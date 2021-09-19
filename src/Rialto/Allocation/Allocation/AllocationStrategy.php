<?php

namespace Rialto\Allocation\Allocation;

use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Allocation\Source\BasicStockSource;

/**
 * An allocation strategy decides the best way to allocate to a collection
 * of stock consumers from multiple stock sources.
 */
interface AllocationStrategy
{
    /**
     * @param RequirementCollection $consumers
     * @param BasicStockSource[] $sources
     * @return BasicStockSource[]
     *  A subset of $sources that should be used to allocate stock
     *  to the given consumers.
     */
    public function getOptimalSources(RequirementCollection $consumers, array $sources);
}
