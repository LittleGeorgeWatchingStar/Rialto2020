<?php

namespace Rialto\Stock\Bin;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Stock\Count\StockAdjustment;
use Rialto\Stock\Location;

/**
 * The quantity on a bin at a particular point in time.
 */
class HistorialStockBin
{
    /** @var StockBin */
    private $bin;
    private $qtyAsOf;

    /**
     * @var int|float
     */
    private $adjustment = 0;

    public function __construct(StockBin $bin, $qtyAsOf)
    {
        $this->bin = $bin;
        $this->qtyAsOf = $qtyAsOf;
    }

    /**
     * Creates a copy of this bin with id and quantities zeroed out.
     *
     * @return HistorialStockBin
     */
    public function copy()
    {
        $newBin = clone $this->bin;
        return new self($newBin, 0);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->bin->getId();
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->bin->getSku();
    }

    /**
     * @return string
     */
    public function getFullSku()
    {
        return $this->bin->getFullSku();
    }

    /**
     * Where the bin is today.
     * @return Location
     */
    public function getLocationToday()
    {
        return $this->bin->getLocation();
    }

    /**
     * @return float|int
     */
    public function getQtyToday()
    {
        return $this->bin->getQtyRemaining();
    }

    /**
     * @return float|int
     */
    public function getQtyAsOf()
    {
        return $this->qtyAsOf;
    }

    /**
     * @return float|int
     */
    public function getAdjustment()
    {
        return $this->adjustment;
    }

    /**
     * @param float|int $adjustment
     */
    public function setAdjustment($adjustment)
    {
        $this->adjustment = $adjustment;
    }

    public function hasAdjustment()
    {
        return $this->adjustment != 0;
    }

    public function getStandardCostDiff()
    {
        return $this->bin->getUnitStandardCost() * $this->adjustment;
    }

    public function persistNewBin(ObjectManager $om)
    {
        $om->persist($this->bin); // a no-op for existing bins
    }

    public function addToStockAdjustment(StockAdjustment $adjustment)
    {
        if ($this->adjustment == 0) {
            return;
        }
        $this->bin->setQtyDiff($this->adjustment);
        $adjustment->addBin($this->bin);
    }
}
