<?php

namespace Rialto\Stock\Item;

use Symfony\Component\EventDispatcher\Event;

/**
 * An event related to a stock item.
 */
class StockItemEvent extends Event
{
    /** @var StockItem */
    private $item;

    public function __construct(StockItem $item)
    {
        $this->item = $item;
    }

    /** @return StockItem */
    public function getItem()
    {
        return $this->item;
    }
}