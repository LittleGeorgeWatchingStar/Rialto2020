<?php

namespace Rialto\Sales\Invoice;

use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\AllocationInterface;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;


/**
 * A partial allocation which indicates to the warehouse staff which
 * source to ship from and how much to ship.
 *
 * This is needed because we might ship less than the total amount allocated.
 */
class SalesInvoiceAllocation implements AllocationInterface, Item
{
    /**
     * @var StockAllocation
     */
    private $alloc;

    /**
     * @var int|float
     */
    private $quantity;

    public function __construct(StockAllocation $alloc, $qty)
    {
        $this->alloc = $alloc;
        $this->quantity = $qty;
    }

    public function getAllocation()
    {
        return $this->alloc;
    }

    public function getSku()
    {
        return $this->alloc->getSku();
    }

    /**
     * @deprecated use getSku() instead
     */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getStockItem()
    {
        return $this->alloc->getStockItem();
    }

    public function getDescription()
    {
        return $this->getStockItem()->getName();
    }

    public function getSource(): BasicStockSource
    {
        return $this->alloc->getSource();
    }

    public function getSourceDescription()
    {
        return $this->alloc->getSourceDescription();
    }

    public function getQtyAllocated()
    {
        return $this->quantity;
    }

    /** @deprecated */
    public function getNetQtyAllocated()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getQtyAllocated();
    }

    /**
     * True if this allocation is from stock at the user's default location.
     *
     * Intended for warehouse staff.
     */
    public function isAtUserLocation(User $user = null)
    {
        $userLoc = $user ? $user->getDefaultLocation() : null;
        return $userLoc && $this->alloc->isAtLocation($userLoc);
    }

    public function isWhereNeeded(): bool
    {
        return $this->alloc->isWhereNeeded();
    }

    public function getLocationWhereNeeded(): Facility
    {
        return $this->alloc->getLocationWhereNeeded();
    }

    public function getShelfPosition()
    {
        return $this->alloc->getShelfPosition();
    }
}
