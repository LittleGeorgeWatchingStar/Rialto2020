<?php

namespace Rialto\Stock\Bin;

/**
 * An event involving a stock bin.
 */
class StockBinEvent extends StockSourceEvent implements HasStockBins
{
    /** @var string */
    private $sku;

    function __construct(StockBin $bin)
    {
        if (! $bin->getId() ) {
            throw new \InvalidArgumentException("Bin must have an ID");
        }
        parent::__construct($bin);
        $this->sku = $bin->getSku();
    }

    /** @return StockBin */
    public function getBin()
    {
        return $this->getSource();
    }

    public function getBins()
    {
        return [$this->getBin()];
    }

    public function getItemSku(): string
    {
        return $this->sku;
    }
}
