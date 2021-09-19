<?php

namespace Rialto\Stock\Cost;

use Rialto\Stock\Category\StockCategory;

/**
 *
 */
class CategoryValuation
{
    /** @var StockCategory */
    private $category;

    /** @var ItemValuation[] */
    private $items = [];

    public function __construct(StockCategory $category)
    {
        $this->category = $category;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getDescription()
    {
        return $this->category->getName();
    }

    public function getStockAccount()
    {
        return $this->category->getStockAccount();
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function getNumItems()
    {
        return count($this->items);
    }

    public function getTotalValue()
    {
        $total = 0;
        foreach ( $this->items as $item ) {
            $total += $item->getTotalValue();
        }
        return $total;
    }
}
