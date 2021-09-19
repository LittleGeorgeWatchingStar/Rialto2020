<?php

namespace Rialto\Purchasing\Invoice;

use Rialto\Purchasing\Producer\StockProducer;

/**
 * A line item in a purchase order.
 */
class SupplierPOItemsSplitSolo
{
    /**
     * @var StockProducer
     */
    private $pOItem;

    /**
     * @var bool
     */
    private $splitToThis = false;

    public function getPOItem()
    {
        return $this->pOItem;
    }

    public function setPOItem($pOItem)
    {
        $this->pOItem = $pOItem;
    }

    public function getSplitToThis()
    {
        return $this->splitToThis;
    }

    public function setSplitToThis($splitToThis)
    {
        $this->splitToThis = $splitToThis;
    }

    public function getDescription()
    {
        return $this->pOItem->getDescription();
    }

    public function getQtyOrdered()
    {
        return $this->pOItem->getQtyOrdered();
    }

    public function getQtyInvoiced()
    {
        return $this->pOItem->getQtyInvoiced();
    }

    public function getQtyReceived()
    {
        return $this->pOItem->getQtyReceived();
    }

    public function getUnitCost()
    {
        return $this->pOItem->getUnitCost();
    }

    public function getExtended()
    {
        return $this->pOItem->getExtendedCost();
    }
}


