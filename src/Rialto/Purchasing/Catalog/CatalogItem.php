<?php


namespace Rialto\Purchasing\Catalog;

use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Stock\Bin\BinStyle;

/**
 * An item from a supplier's online catalog.
 *
 * These can be used to create or update PurchasingData records.
 */
interface CatalogItem
{
    /** @return int|float */
    public function getQtyAvailable();

    /** @return string */
    public function getProductUrl();

    /** @return Manufacturer */
    public function getManufacturer();

    /** @return int|float */
    public function getIncrementQty();

    /** @return string */
    public function getRoHS();

    /** @return int */
    public function getLeadTime();

    /** @return BinStyle */
    public function getBinStyle();

    /** @return int|float */
    public function getBinSize();

    /** @return CostBreakInterface[] */
    public function getCostBreaks();
}
