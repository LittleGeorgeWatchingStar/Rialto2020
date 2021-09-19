<?php

namespace Rialto\Madison\Version;


use Rialto\Stock\Item\StockItem;

/**
 * Remembers which stock items were modified over the course of serving
 * a single request.
 */
class VersionChangeCache
{
    /**
     * @var StockItem[]
     */
    private $changed = [];

    public function addItem(StockItem $stockItem)
    {
        $sku = $stockItem->getSku();
        $this->changed[$sku] = $stockItem;
    }

    /**
     * @return StockItem[]
     */
    public function getItems()
    {
        return array_values($this->changed);
    }

    public function clear()
    {
        $this->changed = [];
    }
}
