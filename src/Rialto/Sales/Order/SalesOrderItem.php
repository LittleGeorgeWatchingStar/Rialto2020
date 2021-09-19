<?php

namespace Rialto\Sales\Order;

use Rialto\Stock\Item;

interface SalesOrderItem extends Item
{
    /**
     * Returns the base price of a single unit of the line item,
     * before discounts and customizations have been applied.
     *
     * @return float
     */
    public function getBaseUnitPrice();

    /**
     * Returns the price for a single unit of this item after
     * all discounts and customizations have been applied.
     *
     * @return float
     */
    public function getFinalUnitPrice();

    /**
     * Returns the extended price for this line item, which is the
     * final unit price times the quantity.
     *
     * @return float
     */
    public function getExtendedPrice();
}
