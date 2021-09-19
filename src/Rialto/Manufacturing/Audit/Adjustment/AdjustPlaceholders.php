<?php

namespace Rialto\Manufacturing\Audit\Adjustment;


use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Requirement\SingleRequirementCollection;
use Rialto\Manufacturing\Audit\AuditItem;
use Rialto\Manufacturing\Requirement\MissingStockRequirement;
use Rialto\Manufacturing\Requirement\Orm\MissingStockRequirementRepository;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Item;

/**
 * Creates or releases "placeholder" allocations to account for stock
 * discrepancies.
 */
class AdjustPlaceholders implements AdjustmentStrategy
{
    /* @var MissingStockRequirementRepository */
    private $repo;

    /** @var AllocationFactory */
    private $factory;

    public function __construct(ObjectManager $om, AllocationFactory $factory)
    {
        $this->repo = $om->getRepository(MissingStockRequirement::class);
        $this->factory = $factory;
    }

    public function releaseFrom(AuditItem $item)
    {
        $sources = $item->releaseAllocationsFromCM();
        $placeholder = $this->repo->findOrCreate(
            $item->getSupplier(),
            $item->getStockItem());
        $collection = new SingleRequirementCollection($placeholder);
        $collection->setShareBins(true);
        $this->factory->allocate($collection, $sources);
    }

    public function acquireFor(AuditItem $item)
    {
        $status = $item->getAllocationStatus();
        if ($status->isKitComplete()) {
            return;
        }

        $qtyShort = $status->getQtyNeeded() - $status->getQtyAtLocation();
        $sources = $this->releasePlaceholderAllocations($item, $qtyShort);
        if (count($sources) > 0) {
            $this->releaseAllocationsFromElsewhere($item, $qtyShort);
            $this->factory->allocate($item, $sources);
        }
    }

    private function releasePlaceholderAllocations(AuditItem $item, $toRelease)
    {
        $placeholder = $this->findPlaceholder($item->getSupplier(), $item);
        if (!$placeholder) {
            return [];
        }

        $sources = [];
        foreach ($placeholder->getAllocations() as $alloc) {
            if ($toRelease <= 0) {
                break;
            }
            if (!$alloc->isAtLocation($item->getFacility())) {
                continue;
            }
            if (!$alloc->isCompatibleWith($item)) {
                continue;
            }
            $toRelease += $alloc->adjustQuantity(-$toRelease);
            $sources[] = $alloc->getSource();
        }
        return $sources;
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

    private function findPlaceholder(Supplier $supplier, Item $item)
    {
        return $this->repo->findExisting($supplier, $item);
    }

}
