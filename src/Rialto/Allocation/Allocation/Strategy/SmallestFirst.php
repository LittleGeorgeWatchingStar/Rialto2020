<?php

namespace Rialto\Allocation\Allocation\Strategy;


use Rialto\Allocation\Allocation\AllocationStrategy;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Allocation\Source\StockSource;

/**
 * This allocation strategy yields results that are far less optimal than
 * SubsetSum, but this runs in O(n log n) in the number of sources rather
 * than O(2^n)!
 *
 * @see SubsetSum
 */
class SmallestFirst implements AllocationStrategy
{
    public function getOptimalSources(RequirementCollection $consumers, array $sources)
    {
        usort($sources, function (StockSource $a, StockSource $b) use ($consumers) {
            return $a->getQtyAvailableTo($consumers) - $b->getQtyAvailableTo($consumers);
        });
        return $sources;
    }
}
