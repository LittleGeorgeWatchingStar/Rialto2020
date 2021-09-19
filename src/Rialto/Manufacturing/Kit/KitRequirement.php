<?php

namespace Rialto\Manufacturing\Kit;

use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Allocation\InvalidAllocationException;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Requirement\ConsolidatedRequirement;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Source\StockSource;
use Rialto\Allocation\Status\RequirementStatus;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Stock\Facility\Facility;

class KitRequirement extends ConsolidatedRequirement
{
    /** @var Facility */
    private $origin;

    /** @var Facility */
    private $destination;

    /** @var StockAllocation[] */
    private $allocations = [];

    /** @var KitAllocationGroup[] */
    private $allocationGroups = [];

    /** @var RequirementStatus */
    private $status = null;

    /**
     * Factory method.
     * @return KitRequirement
     */
    public static function fromKit(WorkOrderKit $kit)
    {
        return new self(
            $kit->getOrigin(),
            $kit->getDestination());
    }

    public function __construct(Facility $origin, Facility $destination)
    {
        $this->origin = $origin;
        $this->destination = $destination;
    }

    /**
     * @return Facility
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    public function getFacility()
    {
        return $this->destination;
    }

    public function addRequirement(Requirement $woReq)
    {
        parent::addRequirement($woReq);
        $this->addAllocations($woReq->getAllocations());
        $this->status = null;
    }

    private function addAllocations(array $allocations)
    {
        foreach ($allocations as $alloc) {
            $this->allocations[] = $alloc;
            $this->addAllocationToGroup($alloc);
        }
    }

    private function addAllocationToGroup(StockAllocation $alloc)
    {
        if (! $alloc->isAtSublocationOf($this->origin)) {
            return;
        }
        $source = $alloc->getSource();
        $sourceId = $source->getSourceNumber();
        if (empty($this->allocationGroups[$sourceId])) {
            $this->allocationGroups[$sourceId] = new KitAllocationGroup($source, $this->destination);
        }
        $group = $this->allocationGroups[$sourceId];
        $group->addAllocation($alloc);
    }

    public function isShareBins()
    {
        return true;
    }

    /** @return string */
    public function getManufacturerCode()
    {
        foreach ($this->allocationGroups as $group) {
            $code = $group->getSource()->getManufacturerCode();
            if ($code) {
                return $code;
            }
        }
        return '';
    }

    public function getGrossQtyNeeded()
    {
        return $this->getStatus()->getQtyNeeded();
    }

    private function getStatus()
    {
        if (! $this->status) {
            $this->status = RequirementStatus::forRequirement($this);
        }
        return $this->status;
    }

    public function getQtyStillNeeded()
    {
        return $this->getGrossQtyNeeded() - $this->getStatus()->getQtyAtLocation()
        - $this->getStatus()->getQtyInTransitToLocation();
    }

    public function getQtyUnallocated()
    {
        return $this->getGrossQtyNeeded() - $this->getStatus()->getQtyAllocated();
    }

    public function getQtyAllocatedAtDestination()
    {
        return $this->getStatus()->getQtyAtLocation();
    }

    public function getQtyAllocatedAtOrigin()
    {
        return $this->getQtyAllocatedAt($this->origin);
    }

    private function getQtyAllocatedAt(Facility $loc)
    {
        $allocs = $this->getAllocationsAt($loc);
        $total = 0;
        foreach ($allocs as $alloc) {
            $total += $alloc->getQtyAllocated();
        }
        return $total;
    }

    /** @return StockAllocation[] */
    private function getAllocationsAt(Facility $loc)
    {
        $matching = [];
        foreach ($this->allocations as $alloc) {
            if ($alloc->isAtSublocationOf($loc)) {
                $matching[] = $alloc;
            }
        }
        return $matching;
    }

    public function getAllocationsAtOrigin()
    {
        return $this->getAllocationsAt($this->origin);
    }

    public function getQtyToShip()
    {
        $total = 0;
        foreach ($this->getAllocationsAtOrigin() as $alloc) {
            $total += $alloc->getSource()->getQtyRemaining();
        }
        return $total;
    }

    /** @return StockAllocation[] */
    public function getAllocations()
    {
        return $this->allocations;
    }

    /** @return KitAllocationGroup */
    public function getAllocationGroup(StockSource $source)
    {
        $group = $this->getAllocationGroupOrNull($source);
        if ($group) {
            return $group;
        }
        throw new \InvalidArgumentException(
            "No allocation group for source " . $source->getFullSku());
    }

    private function getAllocationGroupOrNull(StockSource $source)
    {
        $id = $source->getId();
        return isset($this->allocationGroups[$id]) ?
            $this->allocationGroups[$id] : null;
    }

    public function getQtyAllocatedFromSource(StockSource $source)
    {
        $allocGroup = $this->getAllocationGroupOrNull($source);
        return $allocGroup ? $allocGroup->getQtyAllocated() : 0;
    }

    public function getQtyAvailableFromSource(StockSource $source)
    {
        try {
            return $source->getQtyAvailableTo($this);
        } catch (InvalidAllocationException $ex) {
            $alloc = $ex->getAllocation();
            $alloc->close();
            return $this->getQtyAvailableFromSource($source);
        }
    }

    public function getDescription()
    {
        return $this->getStockItem()->getName();
    }

    /** @return KitAllocationGroup[] */
    public function getAllocationGroupsAtOrigin()
    {
        return $this->allocationGroups;
    }

    public function isCloseCount()
    {
        return $this->getStockItem()->isCloseCount();
    }

    public function reallocateFromSources(array $sources, AllocationFactory $factory)
    {
        $this->deleteAllocationsFromOrigin();
        $newAllocs = $factory->allocate($this, $sources);
        $this->addAllocations($newAllocs);
        return $newAllocs;
    }

    private function deleteAllocationsFromOrigin()
    {
        foreach ($this->allocationGroups as $group) {
            $group->closeAll();
        }
        $this->allocationGroups = [];
    }

    public function needsPrepWork()
    {
        return count($this->getPrepWork()) > 0;
    }

    /** @return WorkOrder[] */
    public function getPrepWork()
    {
        $prep = [];
        foreach ($this->allocations as $alloc) {
            $source = $alloc->getSource();
            if ($alloc->isFromWorkOrderAtLocation($this->origin)) {
                $prep[] = $source;
            }
        }
        return $prep;
    }
}

