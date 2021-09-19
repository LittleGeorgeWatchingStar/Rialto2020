<?php

namespace Rialto\Sales\Order;

use Rialto\Accounting\Card\CapturableInvoice;
use Rialto\Accounting\Card\CardTransaction;

/**
 * Used to notify various storefronts that a pre-authorized sales order
 * needs to have the payment captured.
 *
 * Each storefront has a listener that examines this event. If the order
 * belongs to that storefront, the listener will capture the payment and
 * populate the $chargeTransaction field.
 */
class CapturePaymentEvent extends SalesOrderEvent
{
    /** @var CardTransaction */
    private $chargeTransaction = null;

    /** @var CapturableInvoice */
    private $invoice;

    public function __construct(CapturableInvoice $invoice)
    {
        parent::__construct($invoice->getSalesOrder());
        $this->invoice = $invoice;
    }

    /**
     * @return CapturableInvoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Once a listener is found that can capture the payment, it records
     * the charge using this method.
     *
     * @param CardTransaction $transaction
     */
    public function setChargeTransaction(CardTransaction $transaction)
    {
        $this->chargeTransaction = $transaction;
        $this->getOrder()->addCardTransaction($transaction);
    }

    /**
     * @return CardTransaction
     */
    public function getChargeTransaction()
    {
        return $this->chargeTransaction;
    }
}
