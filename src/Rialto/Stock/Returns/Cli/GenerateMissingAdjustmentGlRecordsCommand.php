<?php

namespace Rialto\Stock\Returns\Cli;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Accounting\Transaction\TransactionRepo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateMissingAdjustmentGlRecordsCommand extends Command
{
    const NAME = 'stock-returns:generate-missing-adjustment-records';

    /** @var EntityManagerInterface */
    private $em;

    /** @var TransactionRepo */
    private $transactionRepo;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct(self::NAME);
        $this->em = $em;
        $this->transactionRepo = $em->getRepository(Transaction::class);
    }


    protected function configure()
    {
        $this->setDescription('Generate missing GL records for stock adjustment transactions')
            ->addArgument('since', InputArgument::REQUIRED, 'The starting date for transactions to search')
            ->addArgument('markDate', InputArgument::REQUIRED, 'The date to mark the new transactions at')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, "Don't save the database", null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $since = $input->getArgument('since');
        $markDate = $input->getArgument('markDate');
        $dryRun = $input->getOption('dry-run');

        $transactions = $this->transactionRepo->findBy([
            'systemType' => SystemType::STOCK_ADJUSTMENT,
        ]);
        /** @var Transaction[] $transactions */
        $transactions = array_values(array_filter($transactions, function (Transaction $t) use ($since) {
            return count($t->getEntries()) == 0 && $t->getDate() > new \DateTime($since) && count($t->getStockMoves()) > 0;
        }));
        foreach ($transactions as $transaction) {
            if (count($transaction->getStockMoves()) > 1) {
                throw new \Exception("{$transaction->getId()} has more than one stock move.");
            }
            $stockItem = $transaction->getStockMoves()[0]->getStockItem();
            $qtyDiff = $transaction->getStockMoves()[0]->getQuantity();
            $stdCost = $transaction->getStockMoves()[0]->getStandardCost();
            $extCost = GLEntry::round($transaction->getStockMoves()[0]->getExtendedStandardCost());
            $stockAccount = $stockItem->getStockAccount();
            $adjustmentAccount = $stockItem->getAdjustmentAccount();

            $transaction->setDate(new \DateTime($markDate));

            $memo = "Resolving transaction {$transaction->getId()}: {$stockItem->getId()} x {$qtyDiff} @ std cost of {$stdCost}";

            $transaction->addEntry($stockAccount, $extCost, $memo);
            $transaction->addEntry($adjustmentAccount, -$extCost, $memo);

            $output->writeln("$stockAccount - $extCost: $memo");
            $output->writeln("$adjustmentAccount - (-)$extCost: $memo");
        }

        if (!$dryRun) {
            $this->em->flush();
        }

        return 0;
    }
}
