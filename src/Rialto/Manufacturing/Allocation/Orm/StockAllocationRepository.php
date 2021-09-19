<?php

namespace Rialto\Manufacturing\Allocation\Orm;


use Rialto\Manufacturing\Allocation\WorkOrderAllocator;

/**
 * A repository for retrieving BankAccount objects.
 */
interface StockAllocationRepository
{
    public function getNonFrozenStockAllocation(WorkOrderAllocator $workOrderAllocator);
}
