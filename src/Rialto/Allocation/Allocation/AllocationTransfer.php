<?php

namespace Rialto\Allocation\Allocation;

use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Allocation\Source\StockSource;

/**
 * Transfers allocations from one stock source to another.
 */
class AllocationTransfer
{
    /** @return StockAllocation[] */
    public function transfer(StockSource $from, BasicStockSource $to)
    {
        $newAllocs = [];
        $destQty = $to->getQtyRemaining();

        foreach ($from->getAllocations() as $oldAlloc) {
            $allocQty = $oldAlloc->getQtyAllocated();

            if ($destQty <= 0) break;
            if ($allocQty <= 0) continue;  /* go to next alloc */

            $qtyToMove = min($destQty, $allocQty);

            /* A new allocation is created for the current bin */
            $requirement = $oldAlloc->getRequirement();
            $newAlloc = $requirement->createAllocation($to);
            $newAlloc->addQuantity($qtyToMove);

            $newAllocs[] = $newAlloc;

            $allocQty -= $qtyToMove;
            $destQty -= $qtyToMove;

            /* And the old allocation is decremented */
            $oldAlloc->addQuantity(-$qtyToMove);
        }

        return $newAllocs;
    }

}
