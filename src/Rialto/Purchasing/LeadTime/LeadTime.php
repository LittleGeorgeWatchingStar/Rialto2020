<?php

namespace Rialto\Purchasing\LeadTime;

use Rialto\Purchasing\Catalog\PurchasingData;


/**
 * Represents the total lead time needed for an item.
 *
 * Instances of this class should only be created by LeadTimeCalculator.
 * @see LeadTimeCalculator
 */
class LeadTime
{
    /** @var PurchasingData */
    private $purchData;

    /** @var int */
    private $orderQty;

    /** @var LeadTime[] */
    private $components = [];

    public function __construct(PurchasingData $purchData, $orderQty)
    {
        $this->purchData = $purchData;
        $this->orderQty = $orderQty;
    }

    public function getStockItem()
    {
        return $this->purchData->getStockItem();
    }

    public function getSku()
    {
        return $this->purchData->getSku();
    }

    /**
     * @deprecated use getSku() instead
     */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getPurchasingData()
    {
        return $this->purchData;
    }

    public function getSupplier()
    {
        return $this->purchData->getSupplier();
    }

    public function getSupplierName()
    {
        $s = $this->getSupplier();
        $l = $this->purchData->getBuildLocation();
        return $s ? $s->getName() :
            ($l ? $l->getName() : 'N/A');
    }

    public function getOrderQty()
    {
        return $this->orderQty;
    }

    /**
     * The amount that the supplier currently has in stock.
     *
     * @return integer
     */
    public function getQtyInStockAtSupplier()
    {
        return $this->purchData->getQtyAvailable();
    }

    public function addComponent(LeadTime $clt)
    {
        $this->components[$clt->getSku()] = $clt;
    }

    public function hasComponents()
    {
        return count($this->components) > 0;
    }

    /** @return LeadTime[] */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @return int
     *  The total number of days for this item and all subcomponents.
     * @see getNetDays()
     */
    public function getTotalDays()
    {
        return $this->getNetDays() + $this->getMaxComponentLeadTime();
    }

    /**
     * @return int
     *  The number of days for just this item, not including subcomponents.
     * @see getTotalDays()
     */
    public function getNetDays()
    {
        return $this->purchData->getLeadTime($this->orderQty);
    }

    /**
     * @deprecated use getNetDays() instead
     */
    public function getNumDays()
    {
        return $this->getNetDays();
    }

    /** @return string */
    public function __toString()
    {
        return (string) $this->getTotalDays();
    }

    private function getMaxComponentLeadTime()
    {
        $max = 0;
        foreach ($this->components as $clt) {
            $max = max($max, $clt->getTotalDays());
        }
        return $max;
    }
}
