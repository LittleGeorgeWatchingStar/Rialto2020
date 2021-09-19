<?php

namespace Rialto\Purchasing\Catalog;

/**
 * A cost break from a CatalogItem.
 *
 * @see CatalogItem
 */
interface CostBreakInterface
{
    /** @return int|float */
    public function getMinimumOrderQty();

    /** @return float */
    public function getUnitCost();
}
