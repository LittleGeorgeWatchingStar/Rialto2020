<?php

namespace Rialto\Allocation\Source;

use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\VersionedItem;

/**
 * A stock source is a place from which stock can be allocated.
 */
interface StockSource extends VersionedItem
{
    /**
     * @return StockAllocation[]
     */
    public function getAllocations();

    /**
     * The physical quantity remaining at the source.
     *
     * @return int
     */
    public function getQtyRemaining();

    /**
     * The quantity that is available to the given StockConsumers to allocate.
     */
    public function getQtyAvailableTo(RequirementCollection $requirements);

    /**
     * The quantity remaining that have not already been allocated to
     * something else.
     *
     * @see getQtyRemaining()
     * @return int
     */
    public function getQtyUnallocated();

    /** @return StockItem */
    public function getStockItem();
}
