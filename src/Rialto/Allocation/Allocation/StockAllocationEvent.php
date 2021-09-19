<?php

namespace Rialto\Allocation\Allocation;

use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Entity\DomainEvent;
use Rialto\Stock\Bin\StockSourceEvent;

/**
 * This event is dispatched when a stock allocation is created or updated.
 */
class StockAllocationEvent extends StockSourceEvent implements DomainEvent
{
    /** @var StockAllocation */
    private $alloc;

    /** @var string */
    private $eventName;

    public function __construct(StockAllocation $alloc, $eventName)
    {
        parent::__construct($alloc->getSource());
        $this->alloc = $alloc;
        $this->eventName = $eventName;
    }

    /** @return StockAllocation */
    public function getAllocation()
    {
        return $this->alloc;
    }

    /** @return StockConsumer */
    public function getConsumer()
    {
        return $this->alloc->getConsumer();
    }

    /**
     * @return string The event name to dispatch when this event is handled.
     */
    public function getEventName()
    {
        return $this->eventName;
    }
}
