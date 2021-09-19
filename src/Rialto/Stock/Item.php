<?php

namespace Rialto\Stock;

/**
 * Any object that is intuitively a stock item, such as a work order requirement
 * or sales order item, can implement this interface.
 */
interface Item
{
    /**
     * @return string The Stock Keeping Unit that identifies the item.
     */
    public function getSku();

    /**
     * @deprecated use getSku() instead.
     */
    public function getStockCode();
}
