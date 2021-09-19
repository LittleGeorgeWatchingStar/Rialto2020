<?php

namespace Rialto\Stock\Bin\Event;


use Rialto\Entity\DomainEvent;
use Rialto\Stock\Bin\StockBin;
use Symfony\Component\EventDispatcher\Event;

final class BinQuantityChanged extends Event implements DomainEvent
{
    /** @var StockBin */
    private $bin;

    /** @var int */
    private $newQty;

    public function __construct(StockBin $bin, int $newQty)
    {
        $this->bin = $bin;
        $this->newQty = $newQty;
    }

    public function getEventName()
    {
        return get_class($this);
    }

    public function getBin(): StockBin
    {
        return $this->bin;
    }

    public function getNewQty(): int
    {
        return $this->newQty;
    }
}
