<?php

namespace Rialto\Allocation;

/**
 * Defines events dispatched by the Allocation Bundle
 */
final class AllocationEvents
{
    /**
     * Fires when the amount allocated changes relative to the amount of stock.
     *
     * This event will not fire, for example, if allocations are adjusted to
     * match a stock adjustment, because the total amount available (ie,
     * unallocated) has not changed.
     */
    const ALLOCATION_CHANGE = 'rialto_allocation.change';

    /**
     * Fires when there is a change in a stock consumer
     */
    const STOCK_CONSUMER_CHANGE = 'rialto_allocation.consumer_change';

}
