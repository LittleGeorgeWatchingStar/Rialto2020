<?php

namespace Rialto\Accounting\PaymentTransaction\Cli;


use Doctrine\ORM\EntityManager;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\PaymentTransaction\PaymentTransaction;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Recalculate the amount allocated for supplier and debtor transactions.
 */
class RecalculateSettled extends Command
{
    const NAME = 'accounting:recalc-settled';

    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        parent::__construct(self::NAME);
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setAliases(['rialto:accounting:recalc-settled'])
            ->setDescription('Recalculate the amount allocated for supplier and debtor transactions.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $year = date('Y');
        $repos = [
            SupplierTransaction::class,
            DebtorTransaction::class,
        ];
        foreach ($repos as $repo) {
            $qb = $this->em->getRepository($repo)
                ->createQueryBuilder('trans')
                ->where('trans.date >= :yearStart')
                ->setParameter('yearStart', "$year-01-01");

            /* @var $transactions PaymentTransaction[] */
            $transactions = $qb->getQuery()->getResult();
            foreach ($transactions as $trans) {
                $output->writeln(trim($trans));
                $trans->updateAmountAllocated();
            }
            $this->em->flush();
            $output->writeln(sprintf("Checked %s transactions.",
                number_format(count($transactions))
            ));
        }
    }

}
