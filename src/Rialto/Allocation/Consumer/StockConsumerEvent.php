<?php

namespace Rialto\Allocation\Consumer;

use Symfony\Component\EventDispatcher\Event;

/**
 * This event is fired when stock consumers are changed.
 */
class StockConsumerEvent extends Event
{
    private $consumers;

    public function __construct(array $consumers)
    {
        $this->consumers = $consumers;
    }

    /** @return StockConsumer[] */
    public function getConsumers()
    {
        return $this->consumers;
    }
}
