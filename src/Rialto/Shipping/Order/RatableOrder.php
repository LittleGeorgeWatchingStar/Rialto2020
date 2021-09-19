<?php

namespace Rialto\Shipping\Order;

use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Shipping\Shipper\Shipper;

/**
 * Any order whose shipping cost can be rated.
 */
interface RatableOrder
{
    /** @return string */
    public function getDeliveryCompany();

    /** @return PostalAddress */
    public function getDeliveryAddress();

    /**
     * Returns the total weight for this order, including packaging,
     * in kilograms.
     *
     * @return double
     */
    public function getTotalWeight();

    /** @return Shipper|null */
    public function getShipper();
}
