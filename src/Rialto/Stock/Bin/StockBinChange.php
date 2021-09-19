<?php

namespace Rialto\Stock\Bin;

use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Base class for creating or updating stock bins.
 */
abstract class StockBinChange
{
    /** @var StockBin */
    protected $bin;

    /**
     * @Assert\Type(type="numeric", message="New quantity must be numeric.")
     * @Assert\Range(min=0, minMessage="New quantity cannot be negative.")
     */
    protected $newQty;

    public function __construct(StockBin $bin)
    {
        $this->bin = $bin;
    }

    /** @return StockBin */
    public function getBin()
    {
        return $this->bin;
    }

    /** @return StockItem */
    public function getStockItem()
    {
        return $this->bin->getStockItem();
    }

    public function getNewQty()
    {
        return $this->newQty;
    }

    public function setNewQty($newQty)
    {
        $this->newQty = $newQty;
    }

    public function getBinStyle()
    {
        return $this->bin->getBinStyle();
    }

    public function setBinStyle(BinStyle $style)
    {
        $this->bin->setBinStyle($style);
    }
}
