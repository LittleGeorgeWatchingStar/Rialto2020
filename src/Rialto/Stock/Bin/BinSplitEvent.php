<?php

namespace Rialto\Stock\Bin;

use Rialto\Stock\Item\StockItemEvent;

class BinSplitEvent extends StockItemEvent implements HasStockBins
{
    /** @var StockBin */
    private $original;

    /** @var StockBin */
    private $new;

    public function __construct(StockBin $original, StockBin $new)
    {
        $item = $original->getStockItem();
        assertion($new->containsItem($item));
        parent::__construct($item);
        $this->original = $original;
        $this->new = $new;
    }

    /**
     * @return StockBin
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @return StockBin
     */
    public function getNew()
    {
        return $this->new;
    }

    /** @return StockBin[] */
    public function getBins()
    {
        return [$this->original, $this->new];
    }

}
