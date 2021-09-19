<?php

namespace Rialto\Stock\Cost;

use Rialto\Exception\InvalidDataException;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Stock\Item\StockItem;

/**
 * This exception is thrown when the standard cost of a stock item is
 * an illegal value.
 *
 * Allowing invalid standard costs to propagate can really screw up the
 * accounting, so use this exception liberally.
 */
class StandardCostException extends InvalidDataException
{
    /** @return StandardCostException */
    public static function fromStockItem(StockItem $item)
    {
        return new self($item->getSku(), $item->getStandardCost());
    }

    public static function fromPurchaseOrderItem(StockProducer $poItem, $cost)
    {
        $desc = $poItem->isStockItem() ? $poItem->getSku() : $poItem->getDescription();
        return new self($desc, $cost);
    }

    public function __construct($item, $cost, $message = null)
    {
        if (! $message ) {
            $message = "$item has an invalid standard cost: ".
                number_format($cost, 4);
        }
        parent::__construct($message);
    }

}
