<?php

namespace Rialto\Sales\Order;

use Rialto\Accounting\Debtor\CustomerCreditEvent;
use Rialto\Sales\EmailEventListener;
use Rialto\Sales\SalesEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Responds to events which update the status of sales orders.
 */
class OrderUpdateListener implements EventSubscriberInterface
{
    /**
     * Needs to be higher priority than EmailEventListener.
     *
     * @see EmailEventListener
     */
    const CONVERT_PRIORITY = 10;

    public static function getSubscribedEvents()
    {
        return [
            SalesEvents::CUSTOMER_CREDIT => ['convertToOrder', self::CONVERT_PRIORITY],
        ];
    }

    public function convertToOrder(CustomerCreditEvent $event)
    {
        $order = $event->getSalesOrder();
        if (!$order) {
            return;
        }

        $amtPaid = $order->getTotalAmountPaid();
        if (($amtPaid > 0) && $order->isQuotation()) {
            $order->convertToOrder();
            if ($amtPaid >= $order->getDepositAmount()) {
                $event->setConfirmation(true);
            }
        }
    }
}
