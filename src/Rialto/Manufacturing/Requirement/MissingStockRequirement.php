<?php

namespace Rialto\Manufacturing\Requirement;

use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Requirement\Requirement as RequirementAbstract;
use Rialto\Manufacturing\Allocation\MissingStockConsumer;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\Version\Version;

/**
 * "Holds on" to stock that the manufacturer says they don't have.
 *
 * When we think that a manufacturer has enough, but they say they don't,
 * we allocate the difference to one of these. This holds on to that stock
 * so that either: a) we can re-allocate it back to them when there is a
 * positive discrepancy, or b) we can see how much stock they're losing.
 */
class MissingStockRequirement extends RequirementAbstract
{
    const CONSUMER_TYPE = 'missing';

    /** @var Supplier */
    private $supplier;

    public function __construct(Supplier $supplier, PhysicalStockItem $item)
    {
        $this->supplier = $supplier;
        parent::__construct($item);
        $this->version = Version::ANY;
        $this->setUnitQtyNeeded(1);
    }

    /** @return Facility */
    public function getFacility()
    {
        return $this->supplier->getFacility();
    }

    /**
     * @return StockConsumer
     */
    public function getConsumer()
    {
        return new MissingStockConsumer($this);
    }

    public function getConsumerType()
    {
        return self::CONSUMER_TYPE;
    }

    public function getConsumerDescription()
    {
        return sprintf('missing from %s', $this->getFacility());
    }

    /**
     * Returns the total number of units required to fulfill the parent order's
     * need for this stock item.
     *
     * @return integer
     */
    public function getTotalQtyOrdered()
    {
        return INF;
    }

    /**
     * Returns the total number of units required to fill what is left of
     * the parent order's need for this item.  This quantity is the total
     * amount required minus the total amound delivered.
     *
     * @return integer
     */
    public function getTotalQtyUndelivered()
    {
        return INF;
    }

    public function getTotalQtyDelivered()
    {
        return 0;
    }
}
