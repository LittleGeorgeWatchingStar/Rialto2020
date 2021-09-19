<?php

namespace Rialto\Shopify\Order\Api;

use Rialto\Accounting\Card\CapturableInvoice;
use Rialto\Sales\Invoice\SalesInvoice;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Shopify\Api\BaseApi;

/**
 * An HTTP client for interacting with the Shopify order API.
 */
class OrderApi extends BaseApi
{
    /**
     * Captures the value of $invoice.
     *
     * @see http://docs.shopify.com/api/transaction#create
     * @return string[] The transaction data.
     */
    public function capturePayment(CapturableInvoice $invoice)
    {
        $shopifyOrderID = $invoice->getSourceId();
        $url = "/admin/orders/$shopifyOrderID/transactions.json";
        $body = [
            'transaction' => [
                'kind' => 'capture',
                /* Specify the amount because the invoice amount might be
                 * less than the authorized amount. */
                'amount' => $invoice->getAmountToCapture(),
            ],
        ];
        $response = $this->postJson($url, $body);
        $data = $this->decodeBody($response);
        return $data['transaction'];
    }

    /**
     * Creates a fulfillment that corresponds to the invoice.
     *
     * @see http://docs.shopify.com/api/fulfillment
     * @param SalesInvoice $invoice
     */
    public function createFulfillment(SalesInvoice $invoice)
    {
        $lineItems = [];
        foreach ( $invoice->getLineItems() as $invItem ) {
            $lineItems[] = [
                'id' => $invItem->getSourceId(),
                'quantity' => $invItem->getQtyInvoiced(),
            ];
        }

        $shopifyOrderID = $invoice->getSourceId();
        $url = "/admin/orders/$shopifyOrderID/fulfillments.json";
        $body = [
            'fulfillment' => [
                'tracking_company' => (string) $invoice->getShipper(),
                'tracking_number' => $invoice->getTrackingNumber(),
                'line_items' => $lineItems,
            ],
        ];
        $response = $this->postJson($url, $body);
        $data = $this->decodeBody($response);
        return $data['fulfillment'];
    }

    /**
     * Let Shopify know that $order is closed.
     */
    public function closeOrder(SalesOrder $order)
    {
        if (! $order->isCompleted() ) {
            throw new \InvalidArgumentException("$order is not closed yet");
        }

        $shopifyID = $order->getSourceId();
        $url = "/admin/orders/$shopifyID/close.json";
        $this->postJson($url, []);
    }
}
