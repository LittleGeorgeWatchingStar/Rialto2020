<?php

namespace Rialto\Sales\Order\Allocation;

use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderEvent;
use Rialto\Sales\SalesEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for and responds to events that need to trigger stock allocation.
 */
class AllocationEventListener implements EventSubscriberInterface
{
    /**
     * @var AllocationFactory
     */
    private $allocationFactory;

    /**
     * @var DbManager
     */
    private $dbm;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

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
            SalesEvents::ORDER_AUTHORIZED => 'allocate',
        ];
    }

    public function __construct(AllocationFactory $factory,
                                DbManager $dbm,
                                EventDispatcherInterface $dispatcher)
    {
        $this->allocationFactory = $factory;
        $this->dbm = $dbm;
        $this->dispatcher = $dispatcher;
    }

    public function allocate(SalesOrderEvent $event)
    {
        $order = $event->getOrder();
        if ($order->isQuotation()) {
            return;
        }

        $qtyAllocated = $order->allocateForLineItems(
            $this->allocationFactory, $this->dbm);
        if ($qtyAllocated > 0) {
            $this->notifyOfAllocations($order);
        }
    }

    private function notifyOfAllocations(SalesOrder $order)
    {
        $event = new SalesOrderEvent($order);
        $this->dispatcher->dispatch(SalesEvents::ORDER_ALLOCATED, $event);
    }
}
