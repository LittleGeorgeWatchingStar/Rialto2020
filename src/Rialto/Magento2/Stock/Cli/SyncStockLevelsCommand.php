<?php

namespace Rialto\Magento2\Stock\Cli;

use GuzzleHttp\Exception\ClientException;
use Rialto\Database\Orm\DbManager;
use Rialto\Magento2\Api\Rest\RestApiFactory;
use Rialto\Magento2\Storefront\Storefront;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Level\Orm\StockLevelStatusRepository;
use Rialto\Stock\Level\StockLevelStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncStockLevelsCommand extends Command
{
    /** @var DbManager */
    private $dbm;

    /** @var StockLevelStatusRepository */
    private $repo;

    /** @var RestApiFactory */
    private $apiFactory;

    public function __construct(DbManager $dbm, RestApiFactory $apiFactory)
    {
        parent::__construct();
        $this->dbm = $dbm;
        $this->repo = $this->dbm->getRepository(StockLevelStatus::class);
        $this->apiFactory = $apiFactory;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('magento2:sync-stock-levels')
            ->setDescription("View differences and optionally sync stock levels with Magento")
            ->addOption('show-missing', null, InputOption::VALUE_NONE, "Show items that are missing from Magento 2")
            ->addOption('show-same', null, InputOption::VALUE_NONE, "Show items whose stock levels match")
            ->addOption('sync', null, InputOption::VALUE_NONE, "Sync stock levels, don't just view");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $storefronts = $this->loadStorefronts();
        if (count($storefronts) == 0) {
            $io->error("No storefronts defined.");
            return 1;
        }
        $hq = Facility::fetchHeadquarters($this->dbm);
        $categories = StockCategory::getSellableIds();
        $products = $this->repo->findAllUpdates($hq, $categories);

        if (count($products) == 0) {
            $io->error("No products found at $hq.");
            return 1;
        }
        foreach ($storefronts as $store) {
            $io->section($store->getStoreUrl());
            $api = $this->apiFactory->createInventoryApi($store);
            $io->writeln(sprintf(
                '%20s %10s %10s %10s %10s',
                'SKU', 'Rialto', 'Magento', 'Enabled', 'Synced'));
            $fmt = '%20s %10d %10s %10s %10s';
            foreach ($products as $product) {
                $sku = $product->getSku();
                $status = $product->getStatus();
                $rialto = (float) $status->getQtyAvailable();
                try {
                    $result = $api->getStockLevel($product);
                } catch (ClientException $ex) {
                    $result = [];
                }
                $store = isset($result['qty']) ? (float) $result['qty'] : '';
                $enabled = $result['is_in_stock'] ?? '';
                $missing = $store === '';
                $same = $rialto === $store;
                $synced = '';
                if ((!$missing) && (!$same) && $input->getOption('sync')) {
                    $api->updateStockLevel($status);
                    $synced = 'YES';
                }

                $show = ($missing && $input->getOption('show-missing'))
                    || ($same && $input->getOption('show-same'))
                    || ((!$missing) && (!$same));
                if ($show) {
                    $io->writeln(sprintf($fmt, $sku, $rialto, $store, $enabled, $synced));
                }
            }
        }

        return 0;
    }

    /** @return Storefront[] */
    private function loadStorefronts()
    {
        return $this->dbm->getRepository(Storefront::class)
            ->findAll();
    }

}
