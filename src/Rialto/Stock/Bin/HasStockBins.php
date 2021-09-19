<?php


namespace Rialto\Stock\Bin;


interface HasStockBins
{
    /**
     * @return StockBin[]
     */
    public function getBins();
}
