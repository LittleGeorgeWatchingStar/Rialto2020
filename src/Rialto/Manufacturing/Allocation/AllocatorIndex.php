<?php

namespace Rialto\Manufacturing\Allocation;

use Rialto\Allocation\Allocation\AllocationFactory;


/**
 * An index of RequirementAllocators keyed by the status of each allocator.
 */
class AllocatorIndex implements \IteratorAggregate
{
    const GROUP_CM = 'at_manufacturer';
    const GROUP_HQ = 'at_headquarters';
    const GROUP_ON_ORDER = 'on_order';
    const GROUP_TO_ALLOCATE = 'to_allocate';
    const GROUP_TO_ORDER = 'to_order';
    const GROUP_ERRORS = 'errors';

    /** @var WorkOrderAllocator */
    private $allocator;

    /** @var WorkOrderAllocatorGroup[] */
    private $groups = null;

    private $canOrder = false;

    /** @var AllocationConfiguration[]|null*/
    private $allocationConfigurations = null;

    public function __construct(WorkOrderAllocator $allocator)
    {
        $this->allocator = $allocator;
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

    public function getIterator()
    {
        return new \ArrayIterator($this->getGroups());
    }

    public function getGroups()
    {
        if (null === $this->groups) {
            $this->groups = $this->initGroups();
            $this->populateGroups();
        }
        return $this->groups;
    }

    /** @return WorkOrderAllocatorGroup[] */
    private function initGroups()
    {
        $locations = $this->allocator->getLocations();
        $hq = new WorkOrderAllocatorGroup($locations);
        $cm = new WorkOrderAllocatorGroup($locations);
        $onOrder = new WorkOrderAllocatorGroup($locations);
        $allocate = new WorkOrderAllocatorGroup($locations);
        $allocate->setAction('allocate');
        $toOrder = new WorkOrderAllocatorGroup($locations);
        if ($this->canOrder) {
            $toOrder->setAction('order');
        }
        $errors = new WorkOrderAllocatorGroup($locations);

        if ($this->allocationConfigurations !== null) {
            $hq->setAllocationConfigurations($this->allocationConfigurations);
            $cm->setAllocationConfigurations($this->allocationConfigurations);
            $onOrder->setAllocationConfigurations($this->allocationConfigurations);
            $toOrder->setAllocationConfigurations($this->allocationConfigurations);
        }
        return [
            self::GROUP_CM => $cm,
            self::GROUP_HQ => $hq,
            self::GROUP_ON_ORDER => $onOrder,
            self::GROUP_TO_ALLOCATE => $allocate,
            self::GROUP_TO_ORDER => $toOrder,
            self::GROUP_ERRORS => $errors,
        ];
    }

    private function populateGroups()
    {
        foreach ($this->allocator->getItems() as $allocator) {
            if ($allocator->hasErrors()) {
                $this->groups[self::GROUP_ERRORS]->addItem($allocator);
            } elseif ($allocator->isAllocatedFromManufacturer()) {
                $this->groups[self::GROUP_CM]->addItem($allocator);
            } elseif ($allocator->isAllocatedFromStock()) {
                $this->groups[self::GROUP_HQ]->addItem($allocator);
            } elseif ($allocator->isFullyAllocated()) {
                $this->groups[self::GROUP_ON_ORDER]->addItem($allocator);
            } elseif ($allocator->requiresOrder())  {
                if ($allocator->hasPurchasingData()) {
                    $this->groups[self::GROUP_TO_ORDER]->addItem($allocator);
                } else {
                    $this->groups[self::GROUP_TO_ALLOCATE]->addItem($allocator);
                }
            }
        }
    }

    /** @return WorkOrderAllocatorGroup */
    private function getGroup($name)
    {
        $groups = $this->getGroups();
        return $groups[$name];
    }

    public function setCanOrder($canOrder)
    {
        $this->canOrder = $canOrder;
    }

    /*
     * deprecated, there was a GROUP_TO_ALLOCATE
     */
    public function getGroupToAllocate(): WorkOrderAllocatorGroup
    {
        return $this->getGroup(self::GROUP_TO_ALLOCATE);
    }

    /** @return WorkOrderAllocatorGroup */
    public function getGroupToOrder()
    {
        return $this->getGroup(self::GROUP_TO_ORDER);
    }

    /**
     * @return WorkOrderAllocatorGroup The group of allocators that have
     *   validation errors.
     */
    public function getErrorGroup()
    {
        return $this->getGroup(self::GROUP_ERRORS);
    }

    /**
     * @return int
     *  The total number of pieces allocated.
     */
    public function allocate(AllocationFactory $factory)
    {
        $group = $this->getGroup(self::GROUP_TO_ORDER);
        $qtyAllocated = 0;
        foreach ($group->getItems() as $allocator) {
            $qtyAllocated += $allocator->allocate($factory);
        }
        return $qtyAllocated;
    }
}
