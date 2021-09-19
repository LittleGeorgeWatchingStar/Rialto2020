<?php

namespace Rialto\Magento2\Api\Rest;

use Rialto\Accounting\Card\CapturableInvoice;

/**
 * Invoice Api for magento 2 for testing purposes;
 */
class FakeInvoiceApi extends InvoiceApi
{
    const TEST_ID = 1;

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
    }

    public function createInvoice(CapturableInvoice $invoice)
    {
        return self::TEST_ID;
    }

    public function captureInvoice(int $invoiceID)
    {
        return;
    }
}
