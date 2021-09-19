<?php

namespace Rialto\Shopify\Order;


use Rialto\Accounting\Card\CardTransaction;
use Rialto\Sales\Order\CapturePaymentEvent;

/**
 * Processes payments for Shopify orders.
 */
class PaymentProcessor extends OrderListener
{
    /**
     * If the order is a Shopify order, request that Shopify capture
     * the card transaction that was authorized at checkout time.
     */
    public function capturePayment(CapturePaymentEvent $event)
    {
        $order = $event->getOrder();
        $storefront = $this->getStorefront($order);
        if (! $storefront ) {
            return;
        }

        $api = $this->getApi($storefront);
        $transaction = $api->capturePayment($event->getInvoice());
        $this->checkForFailure($transaction);
        $cardTrans = CardTransaction::captured(
            $storefront->getPaymentMethod(),
            $transaction['id'],
            $transaction['authorization'],
            $transaction['amount']);
        $event->setChargeTransaction($cardTrans);
        $event->stopPropagation();
    }

    private function checkForFailure(array $transaction)
    {
        if ('success' != $transaction['status']) {
            throw new PaymentException($transaction['message']);
        }
    }
}
