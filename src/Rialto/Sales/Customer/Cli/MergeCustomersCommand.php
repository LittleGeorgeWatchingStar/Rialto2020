<?php

namespace Rialto\Sales\Customer\Cli;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Shopify\Storefront\StorefrontCustomer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class MergeCustomersCommand extends ContainerAwareCommand
{
    /** @var EntityManager */
    private $dbm;

    protected function configure()
    {
        $this->setName('rialto:sales:merge-customer')
            ->setDescription('Merge one customer into another')
            ->addArgument('from', InputArgument::REQUIRED)
            ->addArgument('into', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dbm = $this->getContainer()->get(EntityManagerInterface::class);
        $fromID = $input->getArgument('from');
        $intoID = $input->getArgument('into');

        $from = $this->getCustomer($fromID);
        $into = $this->getCustomer($intoID);

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Merge $from into $into? ", false);
        if (! $helper->ask($input, $output, $question) ) {
            $output->writeln('Aborted.');
            return;
        }

        $dt = DebtorTransaction::class;
        $ct = CardTransaction::class;
        $cb = CustomerBranch::class;
        $sc = StorefrontCustomer::class;
        $c = Customer::class;
        $queries = [
            "update $dt dt set dt.customer = :into where dt.customer = :from",

            "update $ct dt set dt.customer = :into where dt.customer = :from",

            "update $cb dt set dt.customer = :into where dt.customer = :from",

            "update $sc dt set dt.customer = :into where dt.customer = :from",

            "delete from $c c where c.id = :from and :into = :into",
        ];
        foreach ( $queries as $dql ) {
            $query = $this->dbm->createQuery($dql);
            $query->setParameter('into', $intoID);
            $query->setParameter('from', $fromID);
            $result = $query->execute();
            $output->writeln(sprintf('"%s" result: %s',
                $query->getSQL(), $result));
        }
    }

    /** @return Customer */
    private function getCustomer($id)
    {
        return $this->dbm->find(Customer::class, $id);
    }
}
