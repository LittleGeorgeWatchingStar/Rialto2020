<?php

namespace Rialto\Manufacturing\Allocation;

use Rialto\Manufacturing\WorkOrder\WorkOrderAllocatorForChooseStock;


/**
 * An index of RequirementAllocators keyed by stock allocator.
 */
class ChooseRequirementAllocation
{
    /** @var WorkOrderAllocatorForChooseStock */
    private $allocator;

    /** @var WorkOrderAllocatorWithoutLocation */
    private $workOrderAllocatorWithoutLocation = null;

    public function __construct(WorkOrderAllocatorForChooseStock $allocator)
    {
        $this->allocator = $allocator;
        $this->workOrderAllocatorWithoutLocation = $this->initAllocator();
        $this->populateAllocator();
    }

    public function getAllocator()
    {
        if (null === $this->workOrderAllocatorWithoutLocation) {
            $this->workOrderAllocatorWithoutLocation = $this->initAllocator();
            $this->populateAllocator();
        }
        return $this->workOrderAllocatorWithoutLocation;
    }

    /** @return WorkOrderAllocatorWithoutLocation */
    private function initAllocator()
    {
        $generalAllocator = new WorkOrderAllocatorWithoutLocation();
        $generalAllocator->setAction('allocate');
        return $generalAllocator;
    }

    private function populateAllocator()
    {
        foreach ($this->allocator->getItems() as $allocator) {
            $this->workOrderAllocatorWithoutLocation->addItem($allocator);
        }
    }

    public function getStockAllocations()
    {
        $stockAllocations = [];
            $requirementAllocators = $this->workOrderAllocatorWithoutLocation->getItems();
            foreach ($requirementAllocators as $allocator) {
                $stockAllocations = array_merge($stockAllocations, $allocator->getAllocations());
            }
        return $stockAllocations;
    }
}
