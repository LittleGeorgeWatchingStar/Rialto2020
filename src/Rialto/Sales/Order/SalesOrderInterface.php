<?php

namespace Rialto\Sales\Order;

use Rialto\Shipping\Order\RatableOrder;
use Rialto\Shipping\ReasonForShipping;


interface SalesOrderInterface extends RatableOrder
{
    /** @return string */
    public function getComments();

    /** @return string */
    public function getDeliveryName();

    /**
     * @return SalesOrderItem[]
     */
    public function getLineItems();

    /**
     * Only return line items that represent tangible/physical products.
     *
     * @return SalesOrderItem[]
     */
    public function getTangibleLineItems();

    /**
     * Returns the sum of the extended prices of each tangible line item.  This
     * amount does not include charges such as shipping, taxes and service fees.
     *
     * @see getShippingPrice()
     * @return float
     */
    public function getSubtotalPrice();

    /** @return string */
    public function getOrderNumber();

    public function getCustomerReference(): string;

    /** @return string */
    public function getContactPhone();

    /**
     * Returns the price that the customer pays for shipping.
     *
     * @return float
     */
    public function getShippingPrice();

    /**
     * For example, "sale", "replacement", "internal use".
     *
     * @see ReasonForShipping
     *
     * @return string
     */
    public function getReasonForShipping();

    /**
     * Returns the tracking number, if there is one.
     * @return string
     */
    public function getTrackingNumber();
}
