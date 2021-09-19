<?php

namespace Rialto\Stock\Item;

use Rialto\Stock\Item;

interface InvalidItemException
{
    /** @return string */
    public function getMessage();

    /** @return Item */
    public function getItem();

    /** @return string */
    public function getStockCode();
}
