<?php

namespace Rialto\Manufacturing\Task\Cli;

use Rialto\Company\Company;
use Rialto\Database\Orm\DbManager;
use Rialto\Email\MailerInterface;
use Rialto\Manufacturing\Log\Logger;
use Rialto\Manufacturing\Task\StaleOrderDefinition;
use Rialto\Manufacturing\Task\Web\ProductionTaskReminderEmail;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Stock\Facility\Facility;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Cron job to notify manufacturers that they need to take action on
 * their purchase orders.
 */
class ProductionTaskReminderCommand extends ContainerAwareCommand
{
    /** @var ContainerInterface */
    private $container;

    /** @var DbManager */
    private $dbm;

    /** @var Logger */
    private $log;

    protected function configure()
    {
        $this->setName('rialto:production:task-reminder')
            ->setDescription('Remind manufacturer of stale orders')
            ->addArgument('locationID',
                InputArgument::REQUIRED,
                'The location of the manufacturer to review.')
            ->addOption('age', null, InputOption::VALUE_OPTIONAL,
                "Number of hours after which an order is considered stale");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container = $this->getContainer();
        $this->dbm = $this->container->get(DbManager::class);
        $this->log = $this->container->get(Logger::class);

        /** @var $location Facility */
        $location = $this->dbm->find(Facility::class, $input->getArgument('locationID'));
        $params = new StaleOrderDefinition($location);
        $age = $input->getOption('age');
        $params->age = (null === $age) ? $params->age : $age;

        if (! $this->isValid($params, $output)) {
            return 1;
        }

        /** @var $repo PurchaseOrderRepository */
        $repo = $this->dbm->getRepository(PurchaseOrder::class);
        $orders = $repo->findOrdersNeedingAttention($params);

        $summary = $this->sendReminderEmail($location, $orders);
        $output->writeln($summary);

        return 0;
    }

    private function isValid(StaleOrderDefinition $params, OutputInterface $output)
    {
        /** @var $validator ValidatorInterface */
        $validator = $this->container->get(ValidatorInterface::class);
        $errors = $validator->validate($params);
        if (count($errors) > 0) {
            $output->writeln("<error>Invalid parameters:</error>");
            foreach ($errors as $error) {
                $output->writeln("  ". $error->getMessage());
            }
            return false;
        }
        return true;
    }

    /**
     * @param Facility $cm
     * @param PurchaseOrder[] $orders
     */
    private function sendReminderEmail(Facility $cm, array $orders)
    {
        $company = Company::findDefault($this->dbm);
        $email = new ProductionTaskReminderEmail($company, $orders);
        $recipients = $this->getRecipients($cm);
        $email->setTo($recipients);

        $mailer = $this->container->get(MailerInterface::class);
        $mailer->send($email);

        return $this->log->productionTaskReminder($cm, $recipients, $orders);
    }

    private function getRecipients(Facility $cm)
    {
        $supplier = $cm->getSupplier();
        return $supplier->getKitContacts();
    }

}
