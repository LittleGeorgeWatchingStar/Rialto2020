<?php

namespace Rialto\Stock\Level;

use DateTime;
use Rialto\Entity\RialtoEntity;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\PhysicalStockItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Keeps track of stock order points and when stock levels were last
 * synchronized with external applications.
 */
class StockLevelStatus implements RialtoEntity, AvailableStockLevel
{
    /** @var PhysicalStockItem */
    private $stockItem;

    /** @var Facility */
    private $location;

    /**
     * The last time this stock level was synchronized with external
     * applications.
     *
     * @var DateTime|null
     */
    private $dateUpdated = null;

    /**
     * The total quantity in stock at the time of the last sync.
     * @var float
     */
    private $qtyInStock = 0.0;

    /**
     * The quantity allocated at the time of the last sync.
     * @var integer
     */
    private $qtyAllocated = 0;

    /**
     * The order point for this item at this location.
     *
     * @var integer
     * @Assert\NotNull(message="Reorder level cannot be null.",
     *   groups={"Default", "purchasing"})
     * @Assert\Type(type="numeric",
     *   message="Reorder level must be a number.",
     *   groups={"Default", "purchasing"})
     * @Assert\Range(min=0,
     *   groups={"Default", "purchasing"})
     */
    private $orderPoint = 0;

    public function __construct(PhysicalStockItem $item, Facility $location)
    {
        $this->stockItem = $item;
        $this->location = $location;
    }

    public function __toString()
    {
        return sprintf('%s at %s',
            $this->stockItem,
            $this->location);
    }

    /** @return PhysicalStockItem */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    /** @return DateTime|null */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /** @return string */
    public function getSku()
    {
        return $this->stockItem->getSku();
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @return Facility */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return float
     */
    public function getQtyInStock()
    {
        return $this->qtyInStock;
    }

    public function getQtyAllocated()
    {
        return $this->qtyAllocated;
    }

    public function getQtyAvailable()
    {
        return $this->qtyInStock - $this->qtyAllocated;
    }

    public function update($qtyInStock, $qtyAllocated)
    {
        $this->qtyInStock = $qtyInStock;
        $this->qtyAllocated = $qtyAllocated;
        $this->dateUpdated = new DateTime();
    }

    public function getOrderPoint()
    {
        return $this->orderPoint;
    }

    public function setOrderPoint($qty)
    {
        $this->orderPoint = $qty;
    }
}
