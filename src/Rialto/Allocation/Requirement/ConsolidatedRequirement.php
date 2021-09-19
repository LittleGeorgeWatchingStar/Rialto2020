<?php

namespace Rialto\Allocation\Requirement;


use Rialto\Allocation\Allocation\ConsolidatedAllocation;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\Version\Version;

/**
 * Groups multiple requirements for the same part into a single object.
 */
class ConsolidatedRequirement implements RequirementInterface, RequirementCollection
{
    /** @var Requirement */
    private $first = null;

    /** @var Requirement[] */
    private $requirements = [];

    private $shareBins = false;

    public function addRequirement(Requirement $requirement)
    {
        if ($this->first) {
            assertion($this->first->getFullSku() == $requirement->getFullSku());
            $location = $requirement->getFacility();
            $this->shareBins = $location->isAllocateFromCM();
        } else {
            $this->first = $requirement;
        }
        $key = spl_object_hash($requirement);
        $this->requirements[$key] = $requirement;
    }

    public function isShareBins()
    {
        return $this->shareBins;
    }

    public function setShareBins($share)
    {
        $this->shareBins = $share;
    }

    /** @return Requirement[] */
    public function getRequirements()
    {
        return array_values($this->requirements);
    }

    public function getTotalQtyOrdered()
    {
        return array_sum(array_map(function (Requirement $r) {
            return $r->getTotalQtyOrdered();
        }, $this->requirements));
    }

    public function getTotalQtyDelivered()
    {
        return array_sum(array_map(function (Requirement $r) {
            return $r->getTotalQtyDelivered();
        }, $this->requirements));
    }

    public function getTotalQtyUnallocated()
    {
        return array_sum(array_map(function (Requirement $r) {
            return $r->getTotalQtyUnallocated();
        }, $this->requirements));
    }

    public function getTotalQtyUndelivered()
    {
        return array_sum(array_map(function (Requirement $r) {
            return $r->getTotalQtyUndelivered();
        }, $this->requirements));
    }

    /**
     * The location at which this requirement needs its stock to be.
     *
     * @return Facility
     */
    public function getFacility()
    {
        return $this->first->getFacility();
    }

    /**
     * @deprecated use getFacility() instead
     */
    public function getLocation()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFacility();
    }

    /**
     * Any allocations this requirement has.
     *
     * @return StockAllocation[]
     */
    public function getAllocations()
    {
        $allocs = [];
        foreach ($this->requirements as $r) {
            $allocs = array_merge($allocs, $r->getAllocations());
        }
        return $allocs;
    }


    public function getSku()
    {
        return $this->first->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @return PhysicalStockItem */
    public function getStockItem()
    {
        return $this->first->getStockItem();
    }

    public function isCategory($category): bool
    {
        return $this->first->isCategory($category);
    }

    /**
     * @deprecated use getFullSku() instead.
     */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    /**
     * @return string The full SKU, including revision and customization
     *   codes; eg "GS3503F-R1234-C10085"
     */
    public function getFullSku()
    {
        return $this->first->getFullSku();
    }


    /** @return Version */
    public function getVersion()
    {
        return $this->first->getVersion();
    }

    /** @return Customization|null */
    public function getCustomization()
    {
        return $this->first->getCustomization();
    }

    /** @return ConsolidatedAllocation[] */
    public function getConsolidatedAllocations()
    {
        /** @var $consolidated ConsolidatedAllocation[] */
        $consolidated = [];
        foreach ($this->getAllocations() as $alloc) {
            $source = $alloc->getSource();
            $key = $source->getSourceType() . $source->getSourceNumber();
            if (! isset($consolidated[$key])) {
                $consolidated[$key] = new ConsolidatedAllocation($source);
            }
            $consolidated[$key]->addAllocation($alloc);
        }
        return $consolidated;
    }
}
