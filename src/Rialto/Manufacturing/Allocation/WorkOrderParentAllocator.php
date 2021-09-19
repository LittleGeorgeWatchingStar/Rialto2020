<?php

namespace Rialto\Manufacturing\Allocation;

use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Allocation\Source\CompatibilityChecker;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Stock\Facility\Facility;

/**
 * Allocates the child component of a parent work order from the child work order.
 */
class WorkOrderParentAllocator implements RequirementCollection
{
    /** @var Requirement */
    private $childReq;

    /** @var WorkOrder */
    private $childWo;

    public function __construct(WorkOrder $parent)
    {
        $this->childWo = $parent->getChild();
        if (! $this->childWo ) {
            throw new \InvalidArgumentException('Work order has no child');
        }
        $this->childReq = $this->getChildRequirement($parent);
        $this->checkCompatibility();
    }

    private function getChildRequirement(WorkOrder $parent)
    {
        foreach ( $parent->getRequirements() as $woReq ) {
            $sourceWO = $woReq->getSourceWorkOrder();
            if ( $this->childWo === $sourceWO ) {
                return $woReq;
            }
        }
        throw new \InvalidArgumentException('Unable to find child requirement');
    }

    private function checkCompatibility()
    {
        $checker = new CompatibilityChecker();
        if (! $checker->areCompatible($this->childWo, $this) ) {
            throw new \InvalidArgumentException(
                "{$this->childWo} is not compatible with {$this->childReq}");
        }
    }

    public function getRequirements()
    {
        return [$this->childReq];
    }

    public function isShareBins()
    {
        return false;
    }

    public function allocate(AllocationFactory $factory)
    {
        $qtyBefore = $this->getQtyAllocated();
        $factory->allocate($this, [$this->childWo]);
        return $this->getQtyAllocated() - $qtyBefore;
    }

    private function getQtyAllocated()
    {
        $status = $this->childReq->getAllocationStatus();
        return $status->getQtyAllocated();
    }

    public function getSku()
    {
        return $this->childReq->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getStockItem()
    {
        return $this->childReq->getStockItem();
    }

    /**
     * @deprecated use getFullSku() instead.
     */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    public function getFullSku()
    {
        return $this->childReq->getFullSku();
    }

    public function getCustomization()
    {
        return $this->childReq->getCustomization();
    }

    public function getVersion()
    {
        return $this->childReq->getVersion();
    }

    /**
     * @return Facility The location where stock is required.
     */
    public function getFacility()
    {
        return $this->childReq->getFacility();
    }

    /**
     * @deprecated use getFacility() instead
     */
    public function getLocation()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFacility();
    }
}
