<?php

namespace Rialto\Sales\Shipping;


use Rialto\Sales\Order\SalesOrderInterface;

interface ShippableOrder extends SalesOrderInterface
{
    /**
     * @return ShippableOrderItem[]
     */
    public function getLineItems();

    /**
     * The subtotal value of this order for shipping/export purposes.
     * Does not include shipping charges or taxes.
     *
     * @return float
     */
    public function getSubtotalValue();

    /**
     * True if the shipper (we) will pay the duties. Otherwise the receiver
     * pays duties.
     *
     * @return bool
     */
    public function shipperPaysDuties();
}
