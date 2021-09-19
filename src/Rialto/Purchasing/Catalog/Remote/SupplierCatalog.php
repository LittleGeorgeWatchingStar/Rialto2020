<?php

namespace Rialto\Purchasing\Catalog\Remote;

use Rialto\Purchasing\Catalog\CatalogItem;
use Rialto\Purchasing\Catalog\PurchasingData;

/**
 * Suppliers whose catalogs have APIs should implement this interface.
 */
interface SupplierCatalog
{
    /** @return CatalogItem */
    public function getEntry(PurchasingData $purchData);
}
