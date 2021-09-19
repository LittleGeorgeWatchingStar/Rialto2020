<?php

namespace Rialto\Sales;

use Rialto\Accounting\Debtor\CustomerCreditEvent;
use Rialto\Sales\Invoice\SalesInvoiceEvent;
use Rialto\Sales\Order\CapturePaymentEvent;
use Rialto\Sales\Order\SalesOrderEvent;
use Rialto\Sales\Returns\SalesReturnEvent;

/**
 * Defines the events used by the sales bundle.
 */
final class SalesEvents
{
    /**
     * Fires when the customer's credit card is authorized to pay for
     * an order.
     */
    const ORDER_AUTHORIZED = 'rialto_sales.order_authorized';

    /**
     * Fires when a sales order is closed early.
     */
    const ORDER_CLOSED = 'rialto_sales.order_closed';

    /**
     * Fires when stock is allocated to a sales order.
     * The listener receives a @see SalesOrderEvent.
     */
    const ORDER_ALLOCATED = 'rialto_sales.allocation';

    /**
     * Fires when a sales order is approved to ship.
     */
    const APPROVED_TO_SHIP = 'rialto_sales.approved_to_ship';

    /**
     * Fires when we need to capture the payment for a pre-authorized
     * sales order.
     *
     * @see CapturePaymentEvent
     */
    const CAPTURE_PAYMENT = 'rialto_sales.capture_payment';

    /**
     * Fires when a sales order is charged.
     * The listener receives a @see SalesOrderEvent.
     */
    const ORDER_CHARGED = 'rialto.sales.order_charged';

    /**
     * Fires when a sales order is invoiced.
     * @see SalesInvoiceEvent
     */
    const ORDER_INVOICE = 'rialto_sales.order_invoice';

    /**
     * Fires when a credit or receipt from a customer is processed.
     * The listener receives a @see CustomerCreditEvent.
     */
    const CUSTOMER_CREDIT = 'rialto.sales.credit';

    /**
     * Fires when an RMA product is tested.
     * The listener receives a @see SalesReturnEvent.
     */
    const RETURN_DISPOSITION = 'rialto.sales.sales_return_disposition';
}
