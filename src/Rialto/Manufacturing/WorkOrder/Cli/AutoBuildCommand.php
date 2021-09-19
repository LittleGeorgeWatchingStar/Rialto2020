<?php

namespace Rialto\Manufacturing\WorkOrder\Cli;

use Exception;
use Rialto\Database\Orm\DbManager;
use Rialto\Email\MailerInterface;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkOrder\WorkOrderCreation;
use Rialto\Manufacturing\WorkOrder\WorkOrderFactory;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\Cli\AutoOrderCommand;
use Rialto\Purchasing\Order\Email\AutoOrderEmail;
use Rialto\Stock\Facility\Orm\StockNeedMapper;
use Rialto\Stock\Facility\StockNeed;
use Rialto\Stock\Item\ManufacturedStockItem;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Automatically create work orders as needed.
 *
 * @see AutoOrderCommand, which auto-orders purchased parts.
 */
class AutoBuildCommand extends ContainerAwareCommand
{
    /** @var DbManager */
    private $dbm;

    /** @var WorkOrderFactory */
    private $factory;

    /** @var ValidatorInterface */
    private $validator;

    /** @var Table */
    private $outputTable;

    /** Stock items indexed by error message. */
    private $errors = [];

    /**
     * The needs that are successfully ordered.
     * @var StockNeed[]
     */
    private $ordered = [];

    /** @var boolean */
    private $dryRun;

    protected function configure()
    {
        $this->setName('rialto:auto-build')
            ->setDescription('Automatically create work orders as needed')
            ->addOption('dry-run', null, InputOption::VALUE_NONE,
                "Do not commit any changes");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dryRun = $input->getOption('dry-run');

        $container = $this->getContainer();
        $this->dbm = $container->get(DbManager::class);
        $this->factory = $container->get(WorkOrderFactory::class);
        $this->validator = $container->get(ValidatorInterface::class);
        $mapper = new StockNeedMapper($this->dbm);
        $needs = $mapper->fetchNeedsToBuild();
        if ( $output->isVerbose() ) {
            $output->writeln(sprintf("Found %s items needed:", count($needs)));
        }

        $this->outputTable = new Table($output);
        $this->outputTable->setHeaders(['Item', 'Qty to order', 'Errors']);

        $this->tryToCreateWorkOrders($needs);

        if ( $output->isVerbose() ) {
            $this->outputTable->render();
            if ( $this->dryRun ) {
                $output->writeln("Dry-run requested: rolling back...");
            }
        }

        $email = new AutoOrderEmail($this->ordered, $this->errors);
        $email->setSubject('Auto-build stock');
        $email->setDryRun($this->dryRun);
        if ($email->shouldBeSent()) {
            $email->loadSubscribers($this->dbm);
            $mailer = $container->get(MailerInterface::class);
            /* @var $mailer MailerInterface */
            $mailer->send($email);
        }
    }

    private function tryToCreateWorkOrders(array $needs)
    {
        /** @var $purchDataRepo PurchasingDataRepository */
        $purchDataRepo = $this->dbm->getRepository(PurchasingData::class);
        $this->dbm->beginTransaction();
        try {
            foreach ( $needs as $need ) {
                /* @var $need StockNeed */
                $need->loadPurchasingData($purchDataRepo);
                $this->tryToCreateWorkOrder($need);
            }
            if ( $this->dryRun ) {
                $this->dbm->rollBack();
            } else {
                $this->dbm->flushAndCommit();
            }
        }
        catch ( Exception $ex ) {
            $this->dbm->rollBack();
            throw $ex;
        }
    }

    private function tryToCreateWorkOrder(StockNeed $need)
    {
        $violations = $this->validate($need);
        if ( count($violations) == 0 ) {
            $template = $this->createTemplate($need->getStockItem());
            $template->setQtyOrdered($need->getQtyToOrder());
            $violations = $this->validate($template);
            if ( count($violations) == 0 ) {
                $wo = $this->createWorkOrderFromTemplate($template);
                $this->dbm->persist($wo);
                $need->setPurchaseOrderItem($wo);
                $this->ordered[] = $need;
            }
        }
        $this->outputTable->addRow([
            $need->getStockCode(),
            $need->getQtyToOrder(),
            (string) $violations,
        ]);
    }

    private function validate($object)
    {
        static $groups = ['Default', 'purchasing'];
        $violations = $this->validator->validate($object, null, $groups);
        foreach ( $violations as $error ) {
            $this->errors[$error->getMessage()][] = $object->getStockItem();
        }
        return $violations;
    }

    private function createTemplate(ManufacturedStockItem $item): WorkOrderCreation
    {
        $template = new WorkOrderCreation($item);
        $template->loadDefaultValues($this->dbm);
        return $template;
    }

    private function createWorkOrderFromTemplate(WorkOrderCreation $template): WorkOrder
    {
        $workOrder = $this->factory->create($template);
        $workOrder->setOpenForAllocation(true);
        return $workOrder;
    }
}
