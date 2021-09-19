<?php

namespace Rialto\Geppetto\Design;

use Rialto\Stock\Item\StockItem;

class DesignCreateResult
{
    /** @var StockItem */
    private $board;

    /** @var StockItem[] */
    private $derivativeStockItems;

    /**
     * @param StockItem[] $derivativeStockItems
     */
    public function __construct(StockItem $board,
                                array $derivativeStockItems = [])
    {
        $this->board = $board;
        $this->derivativeStockItems = $derivativeStockItems;
    }

    public function getBoard(): StockItem
    {
        return $this->board;
    }

    /**
     * @return StockItem[]
     */
    public function getDerivativeStockItems(): array
    {
        return $this->derivativeStockItems;
    }
}