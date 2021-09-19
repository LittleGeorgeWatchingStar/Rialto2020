<?php

namespace Rialto\Allocation\Consumer;

use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\AllocationEvents;
use Rialto\Allocation\Requirement\Requirement;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for changes in StockConsumers.
 */
class StockConsumerListener implements EventSubscriberInterface
{
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
            AllocationEvents::STOCK_CONSUMER_CHANGE => 'onStockConsumerChange',
        ];
    }

    public function onStockConsumerChange(StockConsumerEvent $event)
    {
        $consumers = $event->getConsumers();
        foreach ( $consumers as $consumer ) {
            foreach ( $consumer->getRequirements() as $req) {
                $this->adjustAllocationsIfNeeded($req);
            }
        }
    }

    private function adjustAllocationsIfNeeded(Requirement $requirement)
    {
        $toAdjust = $requirement->getTotalQtyUnallocated();
        if ( $toAdjust >= 0 ) {
            return;
        }

        $allocs = $requirement->getAllocations();
        /* Sort smallest first */
        // TODO: https://bugs.php.net/bug.php?id=50688 php7
        @usort($allocs, function(StockAllocation $a, StockAllocation $b) {
            return $a->getQtyAllocated() - $b->getQtyAllocated();
        });
        foreach ( $allocs as $alloc ) {
            if ( $toAdjust >= 0 ) {
                return;
            }
            $toAdjust -= $alloc->adjustQuantity($toAdjust);
        }
        assertion($toAdjust >= 0);
    }
}
