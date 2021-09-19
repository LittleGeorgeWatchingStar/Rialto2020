<?php

namespace Rialto\Sales\Order\Dates;

use Rialto\Accounting\Debtor\CustomerCreditEvent;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderEvent;
use Rialto\Sales\SalesEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Attempts to assign a target ship date to an order when required.
 */
class TargetShipDateListener implements EventSubscriberInterface
{
    /**
     * This event should really happen after everything else.
     */
    const PRIORITY = -100;

    /**
     * @var TargetShipDateCalculator
     */
    private $calculator;

    public function __construct(TargetShipDateCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public static function getSubscribedEvents()
    {
        return [
            SalesEvents::ORDER_ALLOCATED => ['onOrderAllocated', self::PRIORITY],
            SalesEvents::CUSTOMER_CREDIT => ['onCustomerCredit', self::PRIORITY],
        ];
    }

    public function onOrderAllocated(SalesOrderEvent $event)
    {
        $this->assignShipDate($event->getOrder());
    }

    public function onCustomerCredit(CustomerCreditEvent $event)
    {
        if ($event->hasSalesOrder()) {
            $this->assignShipDate($event->getSalesOrder());
        }
    }

    private function assignShipDate(SalesOrder $order)
    {
        if ($order->isQuotation()) {
            return;
        }
        if ($order->hasTargetShipDate()) {
            return;
        }
        $this->calculator->setTargetShipDate($order);
    }
}
