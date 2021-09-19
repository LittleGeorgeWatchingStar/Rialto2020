<?php

namespace Rialto\Purchasing\Catalog;

use Rialto\Exception\InvalidDataException;
use Rialto\Stock\Item;
use Rialto\Stock\Item\InvalidItemException;

/**
 * Thrown when there is a problem with the purchasing data for an item.
 */
class PurchasingDataException extends InvalidDataException implements InvalidItemException
{
    private $item;

    public function __construct(Item $item, $message)
    {
        parent::__construct($message);
        $this->item = $item;
    }

    /** @return Item */
    public function getItem()
    {
        return $this->item;
    }

    /** @return string */
    public function getStockCode()
    {
        return $this->item->getSku();
    }
}
