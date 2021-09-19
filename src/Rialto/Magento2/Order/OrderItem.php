<?php

namespace Rialto\Magento2\Order;

use JMS\Serializer\Annotation\Type;
use Rialto\Sales\Order\Import\ImportableItem;


/**
 * A deserialized sales order item from the Magento API.
 */
class OrderItem implements ImportableItem
{
    /** @Type("integer") */
    public $item_id;

    /** @Type("string") */
    public $sku;

    /** @Type("double") */
    public $qty_ordered;

    /** @Type("double") */
    public $price;

    /** @Type("double") */
    public $base_price;

    /** @Type("double") */
    public $discount_amount;

    /** @Type("double") */
    public $tax_percent;

    /** @Type("double") */
    public $row_total;

    public function getSku()
    {
        return $this->sku;
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getSourceId()
    {
        return $this->item_id;
    }

    public function getQtyOrdered()
    {
        return $this->qty_ordered;
    }

    /**
     * Returns the base price of a single unit of the line item,
     * before discounts and customizations have been applied.
     *
     * @return float
     */
    public function getBaseUnitPrice()
    {
        return $this->base_price;
    }

    public function getDiscountRate()
    {
        return $this->discount_amount / ($this->base_price * $this->qty_ordered);
    }

    public function getTaxRate()
    {
        return $this->tax_percent / 100.0;
    }

    /**
     * Returns the price for a single unit of this item after
     * all discounts and customizations have been applied.
     *
     * @return float
     */
    public function getFinalUnitPrice()
    {
        return $this->price;
    }

    /**
     * Returns the extended price for this line item, which is the
     * final unit price times the quantity.
     *
     * @return float
     */
    public function getExtendedPrice()
    {
        return $this->row_total;
    }
}
