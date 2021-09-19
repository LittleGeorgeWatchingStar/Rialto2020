<?php

namespace Rialto\Manufacturing\Allocation;

/**
 * A collection of RequirementAllocators, all of which have the same
 * allocation status (eg, kit complete, in stock, unallocated, etc).
 */
class WorkOrderAllocatorWithoutLocation
{
    /** @var RequirementAllocator[] */
    private $items;
    private $stillNeeded;
    private $toAllocate;
    private $action;


    public function __construct()
    {
        $this->items = [];
        $this->stillNeeded = 0;
        $this->toAllocate = [];
        $this->action = null;
    }

    public function addItem(RequirementAllocator $item)
    {
        $this->items[] = $item;
        $this->stillNeeded += $item->getQtyStillNeeded();
    }

    /** @return RequirementAllocator[] */
    public function getItems()
    {
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
}
