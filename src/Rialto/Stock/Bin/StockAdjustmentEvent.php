<?php

namespace Rialto\Stock\Bin;

use Symfony\Component\EventDispatcher\Event;

class StockAdjustmentEvent extends Event implements HasStockBins
{
    private $bins = [];

    public function addBin(StockBin $bin)
    {
        $this->bins[] = $bin;
    }

    /**
     * @return StockBin[]
     */
    public function getBins()
    {
        return $this->bins;
    }
}
