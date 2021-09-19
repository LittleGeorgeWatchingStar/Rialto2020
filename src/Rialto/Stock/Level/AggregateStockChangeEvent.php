<?php

namespace Rialto\Stock\Level;

use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\EventDispatcher\Event;

/**
 * Contains all items whose stock levels have changed.
 */
class AggregateStockChangeEvent extends Event
{
    private $changes = [];

    public function registerChange(StockItem $item, Facility $location)
    {
        $this->changes[ $location->getId() ][ $item->getId() ] = $item;
    }

    public function hasChanges()
    {
        return ! empty($this->changes);
    }

    /**
     * @return StockItem[]
     *  An index of stock items whose levels have changed;
     *  indexed by [ locationId ][ stockId ].
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * @return StockItem[]
     *  An index of stock items whose levels have changed; indexed by stockId.
     */
    public function getChangesByLocation(Facility $location)
    {
        $id = $location->getId();
        return isset($this->changes[$id]) ? $this->changes[$id] : [];
    }
}
