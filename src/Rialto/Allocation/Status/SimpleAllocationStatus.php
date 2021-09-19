<?php


namespace Rialto\Allocation\Status;


use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Stock\Facility\Facility;

interface SimpleAllocationStatus
{
    /**
     * All {@see StockAllocation} is at needed {@see Facility}.
     */
    public function isKitComplete();

    /**
     * All {@see StockAllocation} is at needed {@see Facility} OR
     * Is in transit to needed {@see Facility} OR
     * Is in on order direct.
     */
    public function isEnRoute();

    /**
     * All {@see StockAllocation} is at a {@see Facility}.
     * At the CM or our warehouse (Does not need to be at needed {@see Facility}).
     */
    public function isFullyStocked();

    /**
     * All {@see StockAllocation} is at a {@see Facility} OR
     * Have been on order direct (on order to the needed {@see Facility}).
     */
    public function isReadyToKit();

    public function isFullyAllocated();

}
