<?php

namespace Rialto\Allocation\Consumer;

use DateTime;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\VersionedItem;


/**
 * A "stock consumer" is a work order or sales order detail,
 * something that requires that a certain quantity of a stock item be
 * allocated to it in order for it to be fulfilled.
 */
interface StockConsumer extends VersionedItem
{
    /** @return Requirement[] */
    public function getRequirements();

    /**
     * Returns the location at which this consumer requires its stock
     * item to be.
     *
     * @return Facility
     */
    public function getLocation();

    /**
     * Returns the order number of the parent work or sales order.
     *
     * @return integer|string
     */
    public function getOrderNumber();

    /**
     * @return boolean True if $other belongs to the same order as this.
     */
    public function isForSameOrder(StockConsumer $other);

    /**
     * The number of units required.
     *
     * @return integer
     */
    public function getQtyOrdered();

    /**
     * The date, if any, by which this consumer needs to be fulfilled.
     * @return DateTime|null
     */
    public function getDueDate();
}
