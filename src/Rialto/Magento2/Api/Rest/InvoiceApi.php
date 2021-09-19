<?php

namespace Rialto\Magento2\Api\Rest;

use Rialto\Accounting\Card\CapturableInvoice;
use Rialto\Magento2\Order\PaymentException;

class InvoiceApi extends BaseApi
{
    /**
     * Create an invoice in Magento corresponding to the given Rialto invoice.
     *
     * @param CapturableInvoice $invoice The Rialto invoice
     * @return int The invoice id
     */
    public function createInvoice(CapturableInvoice $invoice)
    {
        $itemQuantities = [];
        foreach ($invoice->getLineItems() as $item) {
            $sourceID = $item->getSourceId();
            $qtyInvoiced = $item->getQtyInvoiced();
            if (!$sourceID) {
                return null;
            }
            if ($qtyInvoiced > 0) {
                $itemQuantities[] = [
                    'order_item_id' => $sourceID,
                    'qty' => $qtyInvoiced,
                ];
            }
            /* If the order was modified and new items were added, they won't
            have source IDs. We just won't notify Magento that they've been
            invoiced, since they won't appear on the customer's account anyway.
            */
        }

        $requestBody = [
            'capture' => false,
            'items' => $itemQuantities
        ];
        $orderID = $invoice->getSourceId();
        $response = $this->request('POST', "rest/V1/order/$orderID/invoice", [
            'json' => $requestBody,
        ]);
        $invoiceId = $this->getBody($response);
        /** The invoiceId received  has double double quotes. i.e. ""invoiceId"".
         * Need to remove the extra quotes to convert to an integer.*/
        return trim($invoiceId, '"');
    }

    /**
     * Captures payment for the Magento invoice whose ID is given.
     *
     * @param $invoiceID
     */
    public function captureInvoice(int $invoiceID)
    {
        $response = $this->request('POST', "rest/V1/invoices/$invoiceID/capture");
        $success = $this->decodeBody($response);
        if (!$success) {
            throw new PaymentException("Failed to capture Magento 2 invoice $invoiceID");
        }
    }
}
