<?php

namespace Rialto\Allocation\Allocation;

use Rialto\Allocation\AllocationInterface;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;


/**
 * A group of allocations for the same item all from the same source.
 */
class ConsolidatedAllocation implements AllocationInterface
{
    /** @var BasicStockSource */
    private $source;

    /** @var StockAllocation[] */
    private $allocations = [];

    public function __construct(BasicStockSource $source)
    {
        $this->source = $source;
    }

    public function addAllocation(StockAllocation $alloc)
    {
        $this->allocations[] = $alloc;
    }

    public function getQtyAllocated()
    {
        $total = 0;
        foreach ($this->allocations as $alloc) {
            $total += $alloc->getQtyAllocated();
        }
        return $total;
    }

    public function getAllocations()
    {
        return $this->allocations;
    }

    public function getSource(): BasicStockSource
    {
        return $this->source;
    }

    public function isWhereNeeded(): bool
    {
        return ($this->source instanceof StockBin)
            && $this->source->isAtLocation($this->getLocationWhereNeeded());
    }

    public function getLocationWhereNeeded(): Facility
    {
        foreach ($this->allocations as $alloc) {
            return $alloc->getLocationWhereNeeded();
        }
        throw new \LogicException("ConsolidatedAllocation has no allocs");
    }
}
