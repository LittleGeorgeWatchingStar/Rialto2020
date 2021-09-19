<?php

namespace Rialto\Purchasing\Catalog;

use Psr\Container\ContainerInterface;
use Rialto\Database\Orm\DbManager;
use Rialto\Purchasing\Catalog\Remote\Orm\SupplierApiRepository;
use Rialto\Purchasing\Catalog\Remote\SupplierApi;
use Rialto\Purchasing\Catalog\Remote\SupplierCatalogException;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Bin\BinStyleRepo;

/**
 * Creates and updates purchasing data records from the information
 * available via the supplier's API.
 */
class PurchasingDataSynchronizer
{
    /**
     * Purchasing data synced before this time is considered stale and
     * needs to be re-synced.
     */
    const STALE = '-24 hours';

    /** @var SupplierApiRepository */
    private $apiRepo;

    /** @var BinStyleRepo */
    private $binStyleRepo;

    /** @var ContainerInterface */
    private $container;

    /** @var DbManager */
    private $dbm;

    public function __construct(DbManager $dbm, ContainerInterface $container)
    {
        $this->dbm = $dbm;
        $this->apiRepo = $dbm->getRepository(SupplierApi::class);
        $this->binStyleRepo = $dbm->getRepository(BinStyle::class);
        $this->container = $container;
    }

    /**
     * @return string A status message
     */
    public function updateStockLevel(PurchasingData $pd)
    {
        if ($pd->isUpdatedSince(self::STALE)) {
            return "$pd is already up-to-date.";
        }
        return $this->forceUpdateStockLevel($pd);
    }

    /**
     * @return string A status message
     */
    public function forceUpdateStockLevel(PurchasingData $pd)
    {
        $api = $this->getApi($pd->getSupplier());
        if (! $api) {
            return sprintf('No API defined for %s', $pd->getSupplierName());
        }
        try {
            $entry = $api->getEntry($pd);
            $pd->importStockLevel($entry);
        } catch (SupplierCatalogException $ex) {
            return $ex->getMessage();
        }
        return '';
    }

    /**
     * @return string A status message
     */
    public function updateAllFields(PurchasingData $pd)
    {
        $api = $this->getApi($pd->getSupplier());
        if (! $api) {
            return sprintf('No API defined for %s', $pd->getSupplierName());
        }
        try {
            $entry = $api->getEntry($pd);
            $pd->importAllFields($entry);
        } catch (SupplierCatalogException $ex) {
            return $ex->getMessage();
        }
        return '';
    }

    private function getApi(Supplier $supplier = null)
    {
        if (! $supplier) {
            return null;
        }
        return $this->apiRepo->findService($this->container, $supplier);
    }
}
