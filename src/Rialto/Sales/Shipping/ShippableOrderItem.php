<?php

namespace Rialto\Sales\Shipping;

use Rialto\Sales\Order\SalesOrderItem;

/**
 * A physical sales order item that can be shipped.
 */
interface ShippableOrderItem extends SalesOrderItem
{
    /** @return string */
    public function getDescription();

    /**
     * @return int|double
     *  The quantity to ship, which might not be the same as the quantity
     *  ordered.
     */
    public function getQtyToShip();

    /**
     * @return float The unit value of this item for shipping/export purposes.
     */
    public function getUnitValue();

    /**
     * @return float The extended value of this item for shipping/export purposes.
     */
    public function getExtendedValue();

    /**
     * @return bool
     *  True if this is a physical item that weighs something; false if
     *  this is a service or other non-physical purchase.
     */
    public function hasWeight();

    /**
     * @return double
     *  The weight per unit of this item, in kilograms.
     */
    public function getUnitWeight();

    /**
     * @return double
     *  The total weight for for all of the units of this item.
     */
    public function getTotalWeight();

    /**
     * @return string
     *  The two-letter ISO code.
     */
    public function getCountryOfOrigin();

    /** @return string */
    public function getHarmonizationCode();

    /** @return string */
    public function getEccnCode();
}
