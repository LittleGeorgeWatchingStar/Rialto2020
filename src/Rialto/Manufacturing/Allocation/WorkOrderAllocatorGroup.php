<?php

namespace Rialto\Manufacturing\Allocation;

use Rialto\Stock\Facility\Facility;

/**
 * A collection of RequirementAllocators, all of which have the same
 * allocation status (eg, kit complete, in stock, unallocated, etc).
 */
class WorkOrderAllocatorGroup
{
    const ON_ORDER = 'order';

    /** @var Facility[] */
    private $locations = [];

    /** @var RequirementAllocator[] */
    private $items = [];
    private $stillNeeded = 0;
    private $toAllocate = [];
    private $action = null;

    /** @var AllocationConfiguration[]*/
    private $allocationConfigurations = null;

    /**
     * @param Facility[] $locations
     */
    public function __construct(array $locations)
    {
        $this->locations = $locations;
        foreach ($locations as $loc) {
            $this->toAllocate[$loc->getId()] = 0;
        }
        $this->toAllocate[self::ON_ORDER] = 0;
    }

    /** @param AllocationConfiguration[] $allocationConfigurations */
    public function setAllocationConfigurations(array $allocationConfigurations)
    {
        $this->allocationConfigurations = $allocationConfigurations;
    }

    /** @return AllocationConfiguration[]|null */
    public function getAllocationConfigurations()
    {
        return $this->allocationConfigurations;
    }

    /**
     * @return Facility[]
     */
    public function getLocations()
    {
        return $this->locations;
    }

    public function addItem(RequirementAllocator $item)
    {
        $this->items[] = $item;
        $this->stillNeeded += $item->getQtyStillNeeded();
        foreach ($this->locations as $loc) {
            $locId = $loc->getId();
            $this->toAllocate[$locId] += $item->getQtyToAllocateFrom($loc);
        }
        $this->toAllocate[self::ON_ORDER] += $item->getQtyToAllocateFromOrders();
    }

    /** @return RequirementAllocator[] */
    public function getItems()
    {
        if ($this->allocationConfigurations !== null) {
            foreach ($this->items as $requirementAllocator) {
                $requirementAllocator->setAllocationConfigurations($this->allocationConfigurations);
            }
        }
        return $this->items;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getTotalStillNeeded()
    {
        return $this->stillNeeded;
    }

    public function getTotalFrom(Facility $location)
    {
        return $this->toAllocate[$location->getId()];
    }

    public function getTotalFromLocations()
    {
        $resultArray = [];
        foreach ($this->locations as $loc) {
            $locId = $loc->getId();
            $resultArray += $this->toAllocate[$locId];
        }
        return $resultArray;
    }

    public function getTotalFromOrders()
    {
        return $this->toAllocate[self::ON_ORDER];
    }
}
