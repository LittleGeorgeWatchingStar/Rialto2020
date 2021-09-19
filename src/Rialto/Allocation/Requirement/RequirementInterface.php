<?php


namespace Rialto\Allocation\Requirement;


use Rialto\Allocation\Allocation\BinAllocation;
use Rialto\Allocation\Allocation\ProducerAllocation;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\VersionedItem;

interface RequirementInterface extends VersionedItem
{
    public function getTotalQtyOrdered();

    /**
     * Always seems to be 0.
     *  - 0 for {@see BinAllocation} (allocations of {@see StockBin}).
     *  - 0 for {@see ProducerAllocation} (allocations of
     *    {@see PurchaseOrderItem}). Received (delivered) {@see StockItem} will
     *    became a {@see StockBin} with it's own {@see BinAllocation}.
     */
    public function getTotalQtyDelivered();

    /**
     * Always seems to be same as {@see RequirementInterface::getTotalQtyOrdered()}.
     * @see RequirementInterface::getTotalQtyDelivered()
     *
     * The total quantity "outstanding" that is still needed.
     *
     * @return float|int
     */
    public function getTotalQtyUndelivered();

    /**
     * @deprecated use getFacility() instead
     */
    public function getLocation();

    /**
     * The facility at which this requirement needs its stock to be.
     *
     * @return Facility
     */
    public function getFacility();

    /**
     * Any allocations this requirement has.
     *
     * @return StockAllocation[]
     */
    public function getAllocations();

    /**
     * True if the required item is of the given category.
     *
     * @param StockCategory|int $category The category or category ID
     */
    public function isCategory($category): bool;
}
