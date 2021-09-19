<?php

namespace Rialto\Purchasing\Producer;

use Symfony\Component\EventDispatcher\Event;

class StockProducerEvent extends Event
{
    /** @var  StockProducer */
    private $producer;

    public function __construct(StockProducer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * @return StockProducer
     */
    public function getProducer()
    {
        return $this->producer;
    }
}
