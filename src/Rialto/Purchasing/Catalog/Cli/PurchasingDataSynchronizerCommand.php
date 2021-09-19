<?php

namespace Rialto\Purchasing\Catalog\Cli;

use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataQueryBuilder;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Catalog\PurchasingDataSynchronizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PurchasingDataSynchronizerCommand extends Command
{
    /** @var ObjectManager */
    private $om;

    /** @var PurchasingDataRepository */
    private $repo;

    /** @var PurchasingDataSynchronizer */
    private $sync;

    public function __construct(ObjectManager $om, PurchasingDataSynchronizer $sync)
    {
        parent::__construct('purchasing:remote:sync');
        $this->om = $om;
        $this->repo = $om->getRepository(PurchasingData::class);
        $this->sync = $sync;
    }

    protected function configure()
    {
        $this
            ->setDescription('Synchronizing all purchasing data with Octopart')
            ->addOption(
                'throttle',
                null,
                InputOption::VALUE_OPTIONAL,
                'Pause in msec between API calls',
                1000
            )
            ->addOption(
                'sku',
                null,
                InputOption::VALUE_OPTIONAL,
                'Item SKU, catalog number or manufacturer code'
            )
            ->addOption(
                'no-manufacturer',
                null,
                InputOption::VALUE_NONE
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_OPTIONAL,
                'Max number of items to synchronize'
            )
            ->addOption(
                'sync-before',
                null,
                InputOption::VALUE_OPTIONAL,
                'Sync items before date (YYYY-MM-DD)'
            )
            ->addOption(
                'supplier',
                null,
                InputOption::VALUE_OPTIONAL,
                'Item supplier name or web site'
            )
            ->addOption(
                'invalid',
                null,
                InputOption::VALUE_OPTIONAL,
                'Invalid data if true (ex. BinSize = 0)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $purchDataList = $this->loadPurchData($input);
        $purchDataCount = count($purchDataList);
        $success = $error = 0;
        $throttle = max((int) $input->getOption('throttle'), 0) ?: 1000;

        $answer = $io->confirm(sprintf("Found %s items to synchronize. Are you sure you want to continue? ", $purchDataCount));
        if (!$answer) {
            return;
        }

        $io->title("Starting synchronizing....");
        foreach ($purchDataList as $purchData) {
            $io->section(sprintf('%s. Synchronizing item with SKU: %s', $purchDataCount, $purchData->getSku()));
            try {
                $err = $this->sync->updateAllFields($purchData);
                if ($err) {
                    $io->error(sprintf('Error synchronizing item with SKU: %s: %s', $purchData->getSku(), $err));
                    $error++;
                } else {
                    $io->success(sprintf('Synchronizing done for item with SKU: %s', $purchData->getSku()));
                    $this->om->flush();
                    $success++;
                }
            } catch (Exception $ex) {
                $err = $ex->getMessage();
                $io->error(sprintf('Error synchronizing item with SKU: %s: %s', $purchData->getSku(), $err));
                $error++;
            }
            $purchDataCount--;
            usleep($throttle * 1000);
        }
        $io->title("Results");
        $io->success("$success records synchronized");
        if ($error > 0) {
            $io->error("$error errors");
        }
    }

    /** @return PurchasingData[] */
    private function loadPurchData(InputInterface $input)
    {
        $qb = $this->repo->createBuilder()
            ->isActive()
            ->hasApi();
        $this->updateOptionsQuery($input, $qb);

        return $qb->getResult();
    }

    private function updateOptionsQuery(InputInterface $input, PurchasingDataQueryBuilder $qb)
    {
        $sku = $input->getOption('sku');
        if ($sku) {
            $qb->byItemIdentifier($sku);
        }

        $supplier = $input->getOption('supplier');
        if ($supplier) {
            $qb->bySupplierPattern($supplier);
        }

        $syncBefore = $input->getOption('sync-before');
        if ($syncBefore) {
            $qb->lastSyncBefore($syncBefore);
        }

        $limit = $input->getOption('limit');
        if ($limit) {
            $qb->setLimit($limit);
        }

        $invalid = $input->getOption('invalid');
        if ($invalid) {
            $qb->isInvalid();
        }

        if ($input->getOption('no-manufacturer')) {
            $qb->doesNotHaveManufacturer();
        }
    }
}
