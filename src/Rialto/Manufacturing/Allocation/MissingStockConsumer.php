<?php

namespace Rialto\Manufacturing\Allocation;

use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Requirement\MissingStockRequirement;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;

class MissingStockConsumer implements StockConsumer
{
    /** @var MissingStockRequirement */
    private $requirement;

    public function __construct(MissingStockRequirement $requirement)
    {
        $this->requirement = $requirement;
    }

    /** @return Customization|null */
    public function getCustomization()
    {
        return $this->requirement->getCustomization();
    }

    public function getSku()
    {
        return $this->requirement->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @return MissingStockRequirement[] */
    public function getRequirements()
    {
        return [$this->requirement];
    }

    /**
     * Returns the location at which this consumer requires its stock
     * item to be.
     *
     * @return Facility
     */
    public function getLocation()
    {
        return $this->requirement->getFacility();
    }

    /**
     * Returns the order number of the parent work or sales order.
     *
     * @return integer|string
     */
    public function getOrderNumber()
    {
        return 'missing';
    }

    public function isForSameOrder(StockConsumer $other)
    {
        return ( $other instanceof MissingStockConsumer ) &&
            ($this->getLocation()->equals($other->getLocation()));
    }

    /**
     * The number of units required.
     *
     * @return integer
     */
    public function getQtyOrdered()
    {
        return INF;
    }

    /** @return Version */
    public function getVersion()
    {
        return $this->requirement->getVersion();
    }

    /** @return StockItem */
    public function getStockItem()
    {
        return $this->requirement->getStockItem();
    }

    public function getFullSku()
    {
        return $this->requirement->getFullSku();
    }

    /** @deprecated */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    public function getDueDate()
    {
        return null;
    }
}
