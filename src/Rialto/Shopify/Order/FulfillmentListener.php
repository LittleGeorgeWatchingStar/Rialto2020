<?php

namespace Rialto\Shopify\Order;


use Rialto\Sales\Invoice\SalesInvoiceEvent;

/**
 * Listens for order invoice events and marks any Shopify orders as fulfilled.
 *
 * @see http://docs.shopify.com/api/fulfillment
 */
class FulfillmentListener extends OrderListener
{
    public function onOrderInvoice(SalesInvoiceEvent $event)
    {
        $order = $event->getOrder();
        $storefront = $this->getStorefront($order);
        if (!$storefront) {
            return;
        }

        $api = $this->getApi($storefront);
        $invoice = $event->getInvoice();
        $result = $api->createFulfillment($invoice);
        $debtorTrans = $invoice->getDebtorTransaction();
        $debtorTrans->setReference(sprintf('%s fulfillment #%s',
            $storefront,
            $result['id']
        ));
        $event->disableEmail();  // Shopify will send the email.

        if ($order->isCompleted()) {
            $api->closeOrder($order);
        }
    }
}
