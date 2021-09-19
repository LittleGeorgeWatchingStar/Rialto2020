<?php

namespace Rialto\Sales\Order\Import;

use Rialto\Sales\Order\SalesOrderItem;

interface ImportableItem extends SalesOrderItem
{
    public function getSourceId();

    public function getQtyOrdered();

    public function getDiscountRate();

    public function getTaxRate();
}
