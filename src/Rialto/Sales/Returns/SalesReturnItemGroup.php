<?php

namespace Rialto\Sales\Returns;

use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Stock\Item;

class SalesReturnItemGroup implements Item
{
    /** @var SalesReturnItem */
    private $first = null;
    private $items = [];

    public function addItem(SalesReturnItem $item)
    {
        if (! $this->first) {
            $this->first = $item;
        } else {
            assertion($item->getSku() == $this->first->getSku());
        }
        $this->items[] = $item;
    }

    public function getSku()
    {
        return $this->first->getSku();
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @return SalesOrderDetail */
    public function updateReplacementOrder()
    {
        $rma = $this->first->getSalesReturn();
        $origOrder = $rma->getOriginalOrder();
        $replacementOrder = $rma->getReplacementOrder();
        assertion(null != $replacementOrder);

        if ($replacementOrder->containsItem($this)) {
            $newItem = $replacementOrder->getLineItem($this);
        } elseif ($origOrder->containsItem($this)) {
            $origItem = $origOrder->getLineItem($this);
            $newItem = clone $origItem;
            $replacementOrder->addLineItem($newItem);
        } else {
            /* The original item was an assembly, of which the RMA item
             * is just a component. */
            $newItem = new SalesOrderDetail(
                $this->first->getStockItem(),
                $this->first->getDiscountAccount());
            $newItem->setBaseUnitPrice($this->first->getBaseUnitPrice());
            $newItem->setDiscountRate($this->first->getDiscountRate());
            $newItem->setTaxRate($this->first->getTaxRate());
            $replacementOrder->addLineItem($newItem);
        }
        $newItem->setQtyOrdered($this->getQtyAuthorized());

        return $newItem;
    }

    private function getQtyAuthorized()
    {
        return array_reduce($this->items, function ($total, SalesReturnItem $item) {
            return $total + $item->getQtyAuthorized();
        }, 0);
    }
}
