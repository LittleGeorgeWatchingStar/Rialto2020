<?php

namespace Rialto\Allocation\Allocation;

use Rialto\Stock\Bin\StockCreationEvent;
use Rialto\Stock\StockEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for StockCreationEvents and transfers allocations
 * from the producer to the new bin.
 */
class AllocationTransferListener implements EventSubscriberInterface
{
    /** @var AllocationTransfer */
    private $transfer;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     */
    public static function getSubscribedEvents()
    {
        return [
            StockEvents::STOCK_CREATION => 'onStockCreation',
        ];
    }


    public function __construct(AllocationTransfer $transfer)
    {
        $this->transfer = $transfer;
    }

    public function onStockCreation(StockCreationEvent $event)
    {
        $allocs = $this->transfer->transfer($event->getCreator(), $event->getBin());
        $event->setAllocations($allocs);
    }
}
