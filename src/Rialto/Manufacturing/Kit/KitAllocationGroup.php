<?php

namespace Rialto\Manufacturing\Kit;

use Rialto\Allocation\Allocation\ConsolidatedAllocation;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;


/**
 * Contains all allocations for a single work order kit requirement
 * from a single source.
 */
class KitAllocationGroup extends ConsolidatedAllocation
{
    const SPLIT_RATIO = 1.1;

    /** @var StockItem */
    private $stockItem;

    /**
     * @var Facility
     *  The location to which the kit will be sent.
     */
    private $destination;

    public function __construct(BasicStockSource $source, Facility $dest)
    {
        parent::__construct($source);
        $this->stockItem = $source->getStockItem();
        $this->destination = $dest;
    }

    /**
     * @return Facility
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /** @deprecated */
    public function getNetQtyAllocated()
    {
        return $this->getQtyAllocated();
    }

    /** @return StockItem */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function closeAll()
    {
        foreach ($this->getAllocations() as $alloc) {
            $alloc->close();
        }
    }

    public function shouldSplitBin()
    {
        if (! $this->stockItem->isCloseCount() ) return false;

        $qtyNeeded = $this->getQtyAllocated();
        if ( $qtyNeeded == 0 ) return false;
        $qtyNeeded = max($qtyNeeded, $this->getNetQtyNeededAtDestination());

        $qtyOnBin = $this->getSource()->getQtyRemaining();
        return (($qtyOnBin / $qtyNeeded) > self::SPLIT_RATIO);
    }

    /**
     * Returns all allocations from this source that are needed at the
     * destination, regardless of whether they are allocated to the selected
     * work orders.
     *
     * @return StockAllocation[]
     */
    public function getAllocationsNeededAtDestination()
    {
        $allocs = [];
        foreach ( $this->getSource()->getAllocations() as $alloc ) {
            if ( $alloc->isNeededAtLocation($this->destination) ) {
                $allocs[] = $alloc;
            }
        }
        return $allocs;
    }

    /**
     * Returns the total quantity needed from this stock source at the
     * destination by ALL consumers, regardless of whether they are allocated
     * to the selected work orders.
     *
     * This is the minimum amount that should be sent, because otherwise
     * we'll end up splitting bins and sending multiple kits needlessly.
     *
     * @return int
     */
    public function getNetQtyNeededAtDestination()
    {
        $total = 0;
        foreach ( $this->getAllocationsNeededAtDestination() as $alloc ) {
            $total += $alloc->getQtyAllocated();
        }
        return $total;
    }
}
