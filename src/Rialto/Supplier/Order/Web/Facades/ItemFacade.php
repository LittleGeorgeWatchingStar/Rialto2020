<?php

namespace Rialto\Supplier\Order\Web\Facades;


use Rialto\Purchasing\Producer\StockProducer;
use Twig\Environment;

class ItemFacade
{
    /** @var StockProducer */
    private $item;

    public function __construct(StockProducer $item)
    {
        $this->item = $item;
    }

    public function getId()
    {
        return $this->item->getId();
    }

    public function getFullSku()
    {
        return $this->item->getFullSku();
    }

    public function getQtyOrdered()
    {
        if ($this->item->isWorkOrder()) {
            return intval($this->item->getQtyOrdered());
        } else {
            return 0;
        }
    }

    public function getReceivedQty()
    {
        if ($this->item->isWorkOrder()) {
            return $this->item->getQtyReceived();
        } else {
            return 0;
        }
    }

    public function getRemainingQty()
    {
        if ($this->item->isWorkOrder()) {
            return $this->item->getQtyRemaining();
        } else {
            return 0;
        }
    }
}
