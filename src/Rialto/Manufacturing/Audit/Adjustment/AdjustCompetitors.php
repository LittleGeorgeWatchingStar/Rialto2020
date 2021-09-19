<?php

namespace Rialto\Manufacturing\Audit\Adjustment;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Manufacturing\Audit\AuditItem;
use Rialto\Manufacturing\Requirement\Orm\RequirementRepository;
use Rialto\Manufacturing\Requirement\Requirement;

/**
 * Steals allocations from other purchase orders.
 *
 * This strategy only handles upward adjustments.
 */
class AdjustCompetitors implements AdjustmentStrategy
{
    /* @var RequirementRepository */
    private $repo;

    /** @var AllocationFactory */
    private $factory;

    public function __construct(ObjectManager $om, AllocationFactory $factory)
    {
        $this->repo = $om->getRepository(Requirement::class);
        $this->factory = $factory;
    }

    public function releaseFrom(AuditItem $item)
    {
        /* We don't grant allocations back to competitors. If, on a previous
        audit, they told us that a competitor was short parts, we want to leave
        it short. */
    }

    public function acquireFor(AuditItem $item)
    {
        $status = $item->getAllocationStatus();
        if ($status->isKitComplete()) {
            return;
        }
        $qtyShort = $status->getQtyNeeded() - $status->getQtyAtLocation();
        $sources = $this->stealFromCompetitors($item, $qtyShort);
        if (count($sources) > 0) {
            $this->releaseAllocationsFromElsewhere($item, $qtyShort);
            $this->factory->allocate($item, $sources);
        }
    }

    private function stealFromCompetitors(RequirementCollection $item, $toSteal)
    {
        $victims = $this->findCompetitors($item);
        $sources = [];
        foreach ($victims as $victim) {
            if ($toSteal <= 0) {
                break;
            }

            foreach ($victim->getAllocations() as $alloc) {
                if (!$alloc->isAtLocation($item->getFacility())) {
                    continue;
                }
                $toSteal += $alloc->adjustQuantity(-$toSteal);
                $sources[] = $alloc->getSource();
            }
        }
        return $sources;
    }

    private function findCompetitors(RequirementCollection $requirements)
    {
        return $this->repo->findLowerPriorityCompetitors($requirements);
    }

    private function releaseAllocationsFromElsewhere(AuditItem $item, $toRelease)
    {
        foreach ($item->getAllocations() as $alloc) {
            if ($toRelease <= 0) {
                break;
            }
            if ($alloc->isWhereNeeded()) {
                continue;
            }
            $toRelease += $alloc->adjustQuantity(-$toRelease);
        }
    }
}
