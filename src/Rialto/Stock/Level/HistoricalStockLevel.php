<?php

namespace Rialto\Stock\Level;

use Rialto\Stock\Bin\HistorialStockBin;

/**
 * The stock level for an item at a particular location and date.
 */
class HistoricalStockLevel
{
    /** @var string */
    private $fullSku;

    /** @var HistorialStockBin[] */
    private $bins = [];

    /** @var int|float */
    private $reportedQty = null;

    private function __construct($fullSku)
    {
        $this->fullSku = $fullSku;
    }

    /**
     * Factory method.
     *
     * @param HistorialStockBin[] $bins
     * @return self[]
     */
    public static function fromBins(array $bins)
    {
        $index = [];
        foreach ($bins as $bin) {
            $sku = $bin->getFullSku();
            if (! isset($index[$sku])) {
                $index[$sku] = new self($sku);
            }
            $index[$sku]->addBin($bin);
        }
        return $index;
    }

    /**
     * @return string eg, "PF3503"
     */
    public function getSku()
    {
        $first = reset($this->bins);
        return $first->getSku();
    }

    /**
     * @return string eg, "PF3503-R1234"
     */
    public function getFullSku()
    {
        return $this->fullSku;
    }

    public function addBin(HistorialStockBin $bin)
    {
        $this->bins[] = $bin;
    }

    /** @return HistorialStockBin[] */
    public function getBins()
    {
        return $this->bins;
    }

    public function getTotalToday()
    {
        $total = 0;
        foreach ($this->bins as $bin) {
            $total += $bin->getQtyToday();
        }
        return $total;
    }

    public function getTotalAsOf()
    {
        $total = 0;
        foreach ($this->bins as $bin) {
            $total += $bin->getQtyAsOf();
        }
        return $total;
    }

    public function hasReportedQty()
    {
        return null !== $this->reportedQty;
    }

    /**
     * The quantity reported on by the person who did the stock count.
     *
     * @return float|int
     */
    public function getReportedQty()
    {
        return $this->reportedQty;
    }

    public function setReportedQty($reported)
    {
        $this->reportedQty = $reported;
        $this->setAdjustment($this->getRequiredAdjustment());
    }

    /**
     * The amount of adjustment needed to get to the reported quantity.
     *
     * @return float|int
     */
    public function getRequiredAdjustment()
    {
        return $this->hasReportedQty() ?
            $this->reportedQty - $this->getTotalAsOf()
            : 0;
    }

    public function setAdjustment($qtyDiff)
    {
        ksort($this->bins, SORT_NUMERIC);
        if ($qtyDiff < 0) {
            $this->adjustNegative($qtyDiff);
        } elseif ($qtyDiff > 0) {
            $this->adjustPositive($qtyDiff);
        }
    }

    /**
     * Negative adjustments are made by zeroing out the oldest bins first.
     */
    private function adjustNegative($qtyDiff)
    {
        assertion($qtyDiff < 0);
        $remaining = -$qtyDiff;
        foreach ($this->bins as $bin) {
            if ($remaining <= 0) {
                break;
            }
            $thisBin = min($bin->getQtyAsOf(), $bin->getQtyToday(), $remaining);
            $bin->setAdjustment(-$thisBin);
            $remaining -= $thisBin;
        }
    }

    /**
     * Positive adjustments are made by creating a new bin that contains
     * the difference.
     */
    private function adjustPositive($qtyDiff)
    {
        assertion($qtyDiff > 0);
        /** @var $mostRecentBin HistorialStockBin */
        $mostRecentBin = end($this->bins);
        $newBin = $mostRecentBin->copy();
        $newBin->setAdjustment($qtyDiff);
        $this->bins[] = $newBin;
    }

    /**
     * The amount of adjustment we are actually able to do, given the
     * bins that we have.
     *
     * Ideally, this should equal @see getRequiredAdjustment()
     *
     * @return float|int
     */
    public function getActualAdjustment()
    {
        $total = 0;
        foreach ($this->bins as $bin) {
            $total += $bin->getAdjustment();
        }
        return $total;
    }

    public function getStandardCostDiff()
    {
        $total = 0;
        foreach ($this->bins as $bin) {
            $total += $bin->getStandardCostDiff();
        }
        return $total;
    }

    public function isLarge()
    {
        return abs($this->getStandardCostDiff()) > 1000;
    }

    public function isHuge()
    {
        return abs($this->getStandardCostDiff()) > 10000;
    }
}
