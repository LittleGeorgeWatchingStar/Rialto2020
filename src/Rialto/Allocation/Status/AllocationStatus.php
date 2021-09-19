<?php

namespace Rialto\Allocation\Status;


use Rialto\Allocation\Requirement\RequirementInterface;

/**
 * Describes the allocation status of an entity.
 */
interface AllocationStatus extends SimpleAllocationStatus
{
    public function getQtyNeeded();

    /**
     * Quantity of allocated {@see StockBin} ({@see BinAllocation}).
     */
    public function getQtyAllocated();

    public function getQtyUnallocated();

    /**
     * Quantity of allocated {@see StockItem} ({@see StockAllocation}) at the
     * needed {@see Facility}.
     */
    public function getQtyAtLocation();

    /**
     * Quantity of allocated {@see StockBin} ({@see BinAllocation}) in transit
     * to needed location.
     */
    public function getQtyInTransitToLocation();

    /**
     * Quantity of allocated {@see StockItem} ({@see StockAllocation}) shipped
     * directly to the needed {@see Facility}.
     */
    public function getQtyOnOrderDirect();

    /**
     * Always seem to be 0.
     * @see RequirementInterface::getTotalQtyDelivered();
     *
     * Is included in:
     *  - @see AllocationStatus::getQtyAllocated()
     *  - @see AllocationStatus::getQtyAtLocation()
     */
    public function getQtyDelivered();

    /**
     * Alias for {@see AllocationStatus::getQtyAtLocation()}
     */
    public function getQtyKitComplete();

    /**
     * Quantity of {@see StockAllocation} at a {@see Facility} OR
     * Have been on order direct (on order to the needed {@see Facility}).
     */
    public function getQtyReadyToKit();

}
