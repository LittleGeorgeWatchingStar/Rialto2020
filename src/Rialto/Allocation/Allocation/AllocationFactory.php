<?php

namespace Rialto\Allocation\Allocation;

use InvalidArgumentException;
use Rialto\Allocation\Allocation\Strategy\ExistingSourcesFirst;
use Rialto\Allocation\Allocation\Strategy\SmallestFirst;
use Rialto\Allocation\Allocation\Strategy\SubsetSum;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Allocation\Source\StockSource;

/**
 * Creates allocations for ConsumerCollections.
 *
 * @see RequirementCollection
 */
class AllocationFactory
{
    /** @var AllocationStrategy */
    private $strategy = null;

    /**
     * Allows you to force a particular allocation strategy. Useful for testing.
     */
    public function setAllocationStrategy(AllocationStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * Allocates stock for the requirements in $collection from $sources.
     * @param RequirementCollection $collection
     * @param StockSource[] $sources
     * @param bool $needOptimize
     * @return StockAllocation[]
     *  All allocations that were created or updated in the process.
     */
    public function allocate(RequirementCollection $collection, array $sources, bool $needOptimize = true)
    {
        if ($needOptimize) {
            $strategy = $this->strategy ?: $this->chooseAllocationStrategy($sources);
            $sourcesToUse = $strategy->getOptimalSources($collection, $sources);
        } else {
            $sourcesToUse = $sources;
        }


        $allocations = [];
        foreach ($collection->getRequirements() as $requirement) {
            $newAllocs = $this->allocateForRequirement($collection, $requirement, $sourcesToUse);
            $allocations = array_merge($allocations, $newAllocs);
        }
        return $allocations;
    }

    /** @return AllocationStrategy */
    private function chooseAllocationStrategy(array $sources)
    {
        $strategy = (count($sources) > SubsetSum::MAX_NUM_SOURCES)
            ? new SmallestFirst()
            : new SubsetSum();
        return new ExistingSourcesFirst($strategy);
    }

    /**
     * @param BasicStockSource[] $sources
     * @return StockAllocation[]
     */
    private function allocateForRequirement(
        RequirementCollection $collection,
        Requirement $requirement,
        array $sources)
    {
        $qtyNeeded = $requirement->getTotalQtyUnallocated();
        if ($qtyNeeded < 0) {
            $this->releaseExtraAllocations($requirement);
        }

        $allocations = [];
        foreach ($sources as $source) {
            if ($qtyNeeded <= 0) {
                break;
            }
            $this->validateSource($source, $requirement);

            $available = $source->getQtyAvailableTo($collection);
            assertion($available <= $source->getQtyUnallocated());
            $toAllocate = min($qtyNeeded, $available);
            if ($toAllocate <= 0) {
                continue;
            }

            $allocations[] = $this->createAllocation($requirement, $source, $toAllocate);
            $qtyNeeded -= $toAllocate;
        }
        return $allocations;
    }

    /**
     * If we somehow end up with more allocations than we need, we release
     * them here.
     */
    private function releaseExtraAllocations(Requirement $requirement)
    {
        $toRelease = -$requirement->getTotalQtyUnallocated();
        assertion($toRelease > 0);
        foreach ($requirement->getAllocations() as $alloc) {
            if ($toRelease == 0) {
                return;
            }
            assertion($toRelease > 0);
            $toRelease += $alloc->adjustQuantity(-$toRelease);
        }
    }

    private function validateSource($source, Requirement $requirement)
    {
        $error = $this->checkSourceForErrors($source, $requirement);
        if ($error) {
            throw new InvalidArgumentException("Invalid stock source: $error");
        }
    }

    private function checkSourceForErrors($source, Requirement $requirement)
    {
        if (!$source instanceof BasicStockSource) {
            return sprintf('Wrong class (%s given)', get_class($source));
        }
        if (!$source->getSourceNumber()) {
            return "Source has no source number";
        }
        if (!$requirement->isCompatibleWith($source)) {
            return 'Not compatible';
        }
        return null;
    }

    private function createAllocation(
        Requirement $requirement,
        BasicStockSource $source,
        $toAllocate)
    {
        $alloc = $requirement->createAllocation($source);
        $alloc->addQuantity($toAllocate);
        return $alloc;
    }
}
