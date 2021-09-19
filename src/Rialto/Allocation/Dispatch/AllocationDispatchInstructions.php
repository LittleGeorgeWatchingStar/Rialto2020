<?php

namespace Rialto\Allocation\Dispatch;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Rialto\Allocation\Allocation\StockAllocation;


/**
 * Takes a list of allocations that have just been created and generates
 * instructions on how the allocated parts should be dispatched.
 */
class AllocationDispatchInstructions implements IteratorAggregate, Countable
{
    /** @var string[] */
    private $instructions = [];

    /**
     * @param StockAllocation[] $allocations
     * @param int|float $qtyReceived
     */
    public function addAllocations(array $allocations, $qtyReceived)
    {
        $qtyAvailable = $qtyReceived;
        foreach ( $allocations as $alloc ) {
            if ( $qtyAvailable <= 0 ) { break; }
            $setAside = min(
                $qtyAvailable,
                $alloc->getQtyAllocated()
            );
            if ( $setAside <= 0 ) { continue; }
            $qtyAvailable -= $setAside;
            $source = $alloc->getSource();

            $this->instructions[] = sprintf('Set aside %s units of %s from %s for %s.',
                number_format($setAside),
                $alloc->getSku(),
                $source->getSourceDescription(),
                $alloc->getConsumerDescription()
            );
        }
    }

    public function toArray()
    {
        return $this->instructions;
    }

    public function count()
    {
        return count($this->instructions);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->instructions);
    }
}
