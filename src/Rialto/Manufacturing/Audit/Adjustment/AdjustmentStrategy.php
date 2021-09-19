<?php

namespace Rialto\Manufacturing\Audit\Adjustment;

use Rialto\Manufacturing\Audit\AuditItem;

/**
 * A strategy for consuming or freeing up stock in response to a shortage
 * report (aka audit).
 */
interface AdjustmentStrategy
{
    /**
     * Remove allocations from $item when it has been adjusted down.
     */
    public function releaseFrom(AuditItem $item);

    /**
     * Create new allocations for $item when its quantity has been adjusted up.
     */
    public function acquireFor(AuditItem $item);
}
