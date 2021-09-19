<?php

namespace Rialto\Allocation\Allocation\Strategy;

use Rialto\Allocation\Allocation\AllocationStrategy;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Allocation\Source\StockSource;


/**
 * This allocation strategy uses the Exact Subset Sum algorithm,
 * which finds an optimal set of sources from which to allocate.
 *
 * Note that the subset-sum algorithm run in O(2^n) time -- very slow!
 * The pruning step introduced by Solution.isWorthKeeping() is necessary
 * to keep the performance manageable.
 */
class SubsetSum implements AllocationStrategy
{
    /**
     * Because this algorithm is O(2^n) in the number of sources, we can only
     * run it on a relatively short list.
     */
    const MAX_NUM_SOURCES = 10;

    /**
     * @param BasicStockSource[] $sources
     * @return StockSource[]
     */
    public function getOptimalSources(RequirementCollection $requirements, array $sources)
    {
        $qtyNeeded = $this->getTotalQtyNeeded($requirements);
        if ($qtyNeeded == 0) {
            return [];
        }
        assertion(count($sources) <= self::MAX_NUM_SOURCES, 'Too many sources');

        $this->sortSources($requirements, $sources);

        /* Keep track of the best solution so far. */
        $best = new ClosestFitSolution($qtyNeeded);

        /** @var $solutions Solution[] All possible solutions */
        $solutions = [$best];

        foreach ($sources as $source) {
            $qtyAvailable = $source->getQtyAvailableTo($requirements);
            if ($qtyAvailable <= 0) {
                continue;
            }
            $updatedSolutions = [];
            foreach ($solutions as $solution) {
                $updatedSolutions[] = $solution;

                $newSolution = $solution->derive($source, $qtyAvailable);
                if ($newSolution->isWorthKeeping($best)) {
                    $updatedSolutions[] = $newSolution;
                }

                if ($newSolution->isBetterThan($best)) {
                    $best = $newSolution;
                }
            }
            $solutions = $updatedSolutions;
        }

        return $best->getSources();
    }

    private function getTotalQtyNeeded(RequirementCollection $collection)
    {
        $total = 0;
        foreach ($collection->getRequirements() as $requirement) {
            $total += $requirement->getTotalQtyUnallocated();
        }
        return $total;
    }

    /**
     * Sorting the inputs is a heuristic:
     *  smallest first uses up more small bins and prevents warehouse clutter;
     *  largest first finds a solution *orders of magnitude* faster.
     */
    private function sortSources(RequirementCollection $requirements, array &$sources)
    {
        usort($sources, function (StockSource $a, StockSource $b) use ($requirements) {
            return $a->getQtyAvailableTo($requirements) - $b->getQtyAvailableTo($requirements);
        });
    }
}


/**
 * A possible solution or partial solution.
 */
abstract class Solution
{
    protected $qtyNeeded;
    protected $value = 0;
    protected $sources = [];

    public function __construct($qtyNeeded)
    {
        $this->qtyNeeded = $qtyNeeded;
    }

    /**
     * @return StockSource[]
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * Derive a new solution from this one by adding $source and $dv.
     *
     * @param int $dv the change in value that $source introduces
     * @return Solution
     */
    public function derive(StockSource $source, $dv)
    {
        $new = clone $this;
        $new->sources[] = $source;
        $new->value += $dv;
        return $new;
    }

    /**
     * Whether this solution meets the minimal requirements.
     * @return bool
     */
    public function isSufficient()
    {
        return $this->value >= $this->qtyNeeded;
    }

    /**
     * If this solution is better than $other.
     *
     * @return bool
     */
    public abstract function isBetterThan(Solution $other);

    /**
     * Whether we should keep this solution around as a basis for derived
     * solutions.
     *
     * This performance optimization can *dramatically* reduce the number
     * of solutions considered, in some cases by a factor of 1000.
     *
     * @return bool
     */
    public abstract function isWorthKeeping(Solution $previousBest);
}



/**
 * This solution only cares about getting the closest fit to the goal.
 */
class ClosestFitSolution extends Solution
{
    /**
     * @inheritdoc
     */
    public function isBetterThan(Solution $other)
    {
        if (! $other->isSufficient()) {
            return $this->value > $other->value;
        } elseif ($this->isSufficient()) {
            return $this->value < $other->value;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function isWorthKeeping(Solution $previousBest)
    {
        $discard = $previousBest->isSufficient()
            && $previousBest->isBetterThan($this);
        return ! $discard;
    }
}

/**
 * This solution cares about:
 *  1) getting as close to the goal as possible, and then
 *  2) using up as many small bins as possible.
 *
 * The extra restriction in isWorthKeeping() is necessary to find the optimal
 * solution, but ends up severely compromising the pruning ability. This
 * solution usually runs in worst-case O(2^n) time.
 */
class PreferSmallBinsSolution extends Solution
{
    /**
     * @inheritdoc
     */
    public function isBetterThan(Solution $other)
    {
        if (! $other->isSufficient()) {
            return $this->value > $other->value;
        } elseif ($this->isSufficient()) {
            return ($this->value == $other->value)
                ? $this->usesMoreBinsThan($other)
                : ($this->value < $other->value);
        } else {
            return false;
        }
    }

    private function usesMoreBinsThan(Solution $other)
    {
        return count($this->sources) > count($other->getSources());
    }

    /**
     * @inheritdoc
     *
     * If this solution is not sufficient, it must still be kept around
     * because it may be the basis for a better solution later.
     */
    public function isWorthKeeping(Solution $previousBest)
    {
        $discard = $previousBest->isSufficient()
            && $this->isSufficient()  // this line is important, but slows things down
            && $previousBest->isBetterThan($this);
        return ! $discard;
    }
}
