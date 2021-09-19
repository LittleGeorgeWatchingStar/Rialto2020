<?php

namespace Rialto\Stock\Level;

use Rialto\Stock\Item;

/**
 * Represents the amount of stock at a given location.
 */
interface StockLevel extends Item
{
    public function getLocation();

    public function getQtyInStock();
}
