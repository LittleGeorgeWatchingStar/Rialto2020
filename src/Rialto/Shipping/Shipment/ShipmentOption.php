<?php

namespace Rialto\Shipping\Shipment;

use Rialto\Shipping\Method\ShippingMethodInterface;
use Rialto\Shipping\Order\RatableOrder;

/**
 * A shipping method combined with a price, used for rating the shipping
 * cost of a ratable order.
 *
 * @see RatableOrder
 */
interface ShipmentOption extends ShippingMethodInterface
{
    /**
     * Returns the amount that the shipper charges us for this shipment.
     * Contrast this with ISalesOrder->getShippingPrice(), which is what
     * we charge the customer.
     *
     * @return double
     */
    public function getShippingCost();

    /**
     * @return ShippingMethodInterface
     */
    public function getShippingMethod();
}
