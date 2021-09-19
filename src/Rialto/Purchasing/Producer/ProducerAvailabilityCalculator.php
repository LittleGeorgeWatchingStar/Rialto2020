<?php

namespace Rialto\Purchasing\Producer;

use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Allocation\Requirement\ConflictDetector;
use Rialto\Allocation\Requirement\ConsolidatedRequirement;
use Rialto\Allocation\Requirement\RequirementCollection;

/**
 * Decides how many units of a PO item are available to a
 * RequrementCollection.
 *
 * This is trickier than you might think, hence an entire class dedicated
 * to doing it.
 */
class ProducerAvailabilityCalculator
{
    /** @var StockProducer */
    private $producer;

    public function __construct(StockProducer $producer)
    {
        $this->producer = $producer;
    }

    public function getQtyAvailableTo(RequirementCollection $requirements)
    {
        if ($this->hasReflexiveConsumer($requirements)) {
            return 0;
        }
        $detector = new ConflictDetector();
        $totalAllocated = 0;
        foreach ($this->createGroups() as $group) {
            $netAllocated = $this->getQtyAllocated($group);
            if ($netAllocated <= 0) {
                continue;
            }
            $alloc = $this->getFirstAllocation($group);
            if ($detector->isConflict($alloc, $requirements)) {
                if ($this->producer->getBinSize()) {
                    /* If we know the bin size, we round up the qty allocated
                     * to use up the rest of the bin. */
                    $totalAllocated += $this->roundUpQtyAllocated($netAllocated);
                } else {
                    /* If not, we conservatively assume that we cannot share. */
                    return 0;
                }
            } else {
                $totalAllocated += $netAllocated;
            }
        }
        return max($this->producer->getQtyRemaining() - $totalAllocated, 0);
    }

    /**
     * Prevents consumers (eg, rework orders) from allocating from themselves.
     */
    private function hasReflexiveConsumer(RequirementCollection $requirements)
    {
        if (!$this->producer instanceof StockConsumer) {
            return false;
        }
        foreach ($requirements->getRequirements() as $requirement) {
            if ($requirement->getConsumer() === $this->producer) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param StockAllocation[] $allocations
     * @return ConsolidatedRequirement[]
     */
    private function createGroups()
    {
        $cd = new ConflictDetector();
        $groups = [];
        $group = new ConsolidatedRequirement();
        foreach ($this->producer->getAllocations() as $alloc) {
            if ($cd->isConflict($alloc, $group)) {
                $groups[] = $group;
                $group = new ConsolidatedRequirement();
            }
            $group->addRequirement($alloc->getRequirement());
        }
        $groups[] = $group;
        return $groups;
    }

    private function getQtyAllocated(ConsolidatedRequirement $req)
    {
        $total = 0;
        foreach ($req->getAllocations() as $alloc) {
            if ($alloc->getSource() === $this->producer) {
                $total += $alloc->getQtyAllocated();
            }
        }
        return $total;
    }

    private function getFirstAllocation(ConsolidatedRequirement $req)
    {
        foreach ($req->getAllocations() as $alloc) {
            return $alloc;
        }
        throw new \LogicException("Requirement has no allocations");
    }

    private function roundUpQtyAllocated($qty)
    {
        $binSize = $this->producer->getBinSize();
        $units = $qty / $binSize;
        $units = (int) ceil($units);
        return min($units * $binSize, $this->producer->getQtyRemaining());
    }

}
