<?php

namespace Rialto\Purchasing\Catalog;

use Rialto\Entity\RialtoEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Base class for purchasing cost breaks.
 */
abstract class CostBreakAbstract implements RialtoEntity, CostBreakInterface
{
    /**
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    protected $minimumOrderQty = 0;

    /**
     * @Assert\NotBlank(message="Manufacturer lead time is required.")
     * @Assert\Type(type="integer")
     * @Assert\Range(min=0)
     */
    protected $manufacturerLeadTime = 0;

    /**
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    protected $supplierLeadTime;

    /**
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    protected $unitCost = 0.0;

    public function getMinimumOrderQty()
    {
        return (int) $this->minimumOrderQty;
    }

    public function setMinimumOrderQty($orderQty)
    {
        $this->minimumOrderQty = $orderQty;
    }

    /**
     * This method should only be called by PurchasingData.
     * @see PurchasingData#getLeadTime()
     */
    public function getManufacturerLeadTime(): int
    {
        return (int) $this->manufacturerLeadTime;
    }

    public function setManufacturerLeadTime(int $leadTime)
    {
        $this->manufacturerLeadTime = $leadTime;
    }

    /**
     * This method should only be called by PurchasingData.
     * @see PurchasingData#getLeadTime()
     * @return int
     */
    public function getSupplierLeadTime()
    {
        return $this->hasSupplierLeadTime() ?
            (int) $this->supplierLeadTime :
            null;
    }

    public function hasSupplierLeadTime()
    {
        return null !== $this->supplierLeadTime;
    }

    public function setSupplierLeadTime($leadTime)
    {
        $this->supplierLeadTime = $leadTime;
    }

    public function getUnitCost()
    {
        return (float) $this->unitCost;
    }

    public function setUnitCost($cost)
    {
        $this->unitCost = $cost;
    }
}
