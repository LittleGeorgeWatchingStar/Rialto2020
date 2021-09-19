<?php

namespace Rialto\Stock\Item;

class DummyStockItem extends StockItem
{
    const STOCK_TYPE = 'dummy';

    public function getStockType(): string
    {
        return self::STOCK_TYPE;
    }

    public function getWeight()
    {
        return 0;
    }

    public function getVolume()
    {
        return 0;
    }

    public function isEsdSensitive(): bool
    {
        return false;
    }
}
