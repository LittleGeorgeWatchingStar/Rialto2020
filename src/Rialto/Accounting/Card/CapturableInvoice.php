<?php

namespace Rialto\Accounting\Card;

use Rialto\Accounting\InvoiceItem;
use Rialto\Sales\Order\SalesOrder;

/**
 * An invoice for which we can capture a credit card authorization.
 */
interface CapturableInvoice
{
    /**
     * The ID of this invoice on the original storefront.
     *
     * @return string
     */
    public function getSourceId();

    /**
     * @return float
     */
    public function getAmountToCapture();

    /**
     * @return SalesOrder
     */
    public function getSalesOrder();

    /**
     * @return InvoiceItem[]
     */
    public function getLineItems();
}
