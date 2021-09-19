<?php

namespace Rialto\Manufacturing\Web\Facades;


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
        return $this->item->getQtyOrdered();
    }

    public function getQtyReceived()
    {
        return $this->item->getQtyReceived();
    }

}
