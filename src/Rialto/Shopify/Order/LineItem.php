<?php

namespace Rialto\Shopify\Order;

use JMS\Serializer\Annotation\Type;
use Rialto\Sales\Order\Import\ImportableItem;

/**
 * A line item in a Shopify order.
 */
class LineItem implements ImportableItem
{
    /** @Type("string") */
    public $id;

    /** @Type("double") */
    public $price;

    /** @Type("integer") */
    public $quantity;

    /** @Type("string") */
    public $sku;

    /** @Type("array") */
    public $tax_lines = [];

    public function getSourceId()
    {
        return $this->id;
    }

    public function getQtyOrdered()
    {
        return $this->quantity;
    }

    public function getDiscountRate()
    {
        return 0.0;
    }

    public function getTaxRate()
    {
        $total = 0.0;
        foreach ($this->tax_lines as $line) {
            $total += $line['rate'];
        }
        return $total;
    }

    public function getSku()
    {
        return $this->sku;
    }

    public function getStockCode()
    {
        return $this->getSku();
    }

    public function getBaseUnitPrice()
    {
        return $this->price;
    }

    public function getFinalUnitPrice()
    {
        return $this->price;
    }

    public function getExtendedPrice()
    {
        return $this->price * $this->quantity;
    }
}
