<?php

namespace Rialto\Payment;

use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Sales\Order\SalesOrder;

interface PaymentGateway
{
    /**
     * Authorizes (but does not charge) the credit card whose info is given
     * for the given sales order.
     *
     * @param CardAuth $cardInfo
     * @param SalesOrder $order
     * @return CardTransaction
     */
    public function authorize(CardAuth $cardInfo, SalesOrder $order);

    /**
     * Charges a previously-authorized credit card.
     *
     * @param CardTransaction $authorization
     *  The authorization transaction
     * @param float $amount
     *  The amount to charge
     * @param string $invoiceNumber
     *  The order number or invoice number that the charge is for
     * @return CardTransaction|null
     *  Returns a new CardTransaction object on success, or null if there
     *  was an error.
     */
    public function chargeCard(CardTransaction $authorization, $amount, $invoiceNumber);

    /**
     * Voids the given card transaction.
     */
    public function void(CardTransaction $transaction, $invoiceNumber = null);

    /**
     * Creates a credit (refund) for the given payment.
     *
     * @param CardTransaction $payment
     * @param string $cardNumber
     * @param float $amount the amount to refund (a positive number)
     * @return CardTransaction
     */
    public function credit(CardTransaction $transaction, $cardNumber, $amount = null);

    /**
     * @return GLAccount
     */
    public function getDepositAccount();

    /**
     * @return GLAccount
     */
    public function getFeeAccount();

    /**
     * Returns the name of this payment gateway.
     *
     * @return string
     */
    public function getName();
}
