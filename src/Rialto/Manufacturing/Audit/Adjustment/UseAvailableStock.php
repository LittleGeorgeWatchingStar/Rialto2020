<?php

namespace Rialto\Manufacturing\Audit\Adjustment;


use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Source\SourceCollection;
use Rialto\Manufacturing\Audit\AuditItem;

/**
 * The simplest strategy for positive adjustments -- just try to use
 * available stock that's already at the CM.
 */
class UseAvailableStock implements AdjustmentStrategy
{
    /** @var ObjectManager */
    private $om;

    /** @var AllocationFactory */
    private $factory;

    public function __construct(ObjectManager $om, AllocationFactory $factory)
    {
        $this->om = $om;
        $this->factory = $factory;
    }

    public function releaseFrom(AuditItem $item)
    {
        // does not apply
    }

    public function acquireFor(AuditItem $item)
    {
        $sources = SourceCollection::fromAvailableBins($item, $item->getBuildLocation(), $this->om);
        $qtyAvailable = $sources->getQtyAvailableTo($item);
        $toRelease = $qtyAvailable;
        foreach ($item->getAllocations() as $alloc) {
            if ($toRelease <= 0) {
                break;
            }
            if ($alloc->isWhereNeeded()) {
                continue;
            }
            $toRelease += $alloc->adjustQuantity(-$toRelease);
        }
        $this->factory->allocate($item, $sources->toArray());
    }
}
