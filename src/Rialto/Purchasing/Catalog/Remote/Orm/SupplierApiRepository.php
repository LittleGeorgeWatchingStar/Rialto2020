<?php

namespace Rialto\Purchasing\Catalog\Remote\Orm;

use Psr\Container\ContainerInterface;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Catalog\Remote\SupplierApi;
use Rialto\Purchasing\Catalog\Remote\SupplierCatalog;
use Rialto\Purchasing\Supplier\Supplier;

/**
 * Database mapper for SupplierApi class.
 */
class SupplierApiRepository extends RialtoRepositoryAbstract
{
    /**
     * Gets the service from the service container that provides API access
     * to the supplier's online catalog.
     * @param ContainerInterface $container
     * @param Supplier $supplier
     * @return SupplierCatalog|null The service class
     */
    public function findService(ContainerInterface $container, Supplier $supplier)
    {
        $api = $this->findBySupplier($supplier);
        return $api ? $api->getService($container) : null;
    }

    /** @return SupplierApi|object|null */
    private function findBySupplier(Supplier $supplier)
    {
        return $this->findOneBy([
            'supplier' => $supplier->getId(),
        ]);
    }

    /**
     * True if $pd can be updated/synced via the supplier's API.
     *
     * @return bool
     */
    public function canSyncViaApi(PurchasingData $pd)
    {
        return $pd->getId()
            && $pd->hasSupplier()
            && (null !== $this->findBySupplier($pd->getSupplier()));
    }
}
