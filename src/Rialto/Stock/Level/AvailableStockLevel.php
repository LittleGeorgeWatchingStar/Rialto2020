<?php

namespace Rialto\Stock\Level;


/**
 * Extends StockLevel to include the unallocated quantity available to new
 * orders.
 */
interface AvailableStockLevel extends StockLevel
{
    /**
     * @return int|float The unallocated quantity available for new orders.
     */
    public function getQtyAvailable();
}
