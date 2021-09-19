<?php

namespace Rialto\Accounting\Debtor\Refund;

use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * Creates a customer credit card refund.
 */
class CardRefund extends CustomerRefund
{
    /**
     * The original payment (ie, receipt) that we are refunding.
     *
     * @var CardTransaction
     */
    private $receipt;

    /**
     * If this is a full refund/credit (rather than just voiding an existing
     * payment) set this to the refund CardTransaction.
     *
     * @var CardTransaction
     */
    private $refund = null;

    public function __construct(CardTransaction $receipt)
    {
        parent::__construct($receipt->getCustomer());
        $this->receipt = $receipt;
        $this->memo = "Refund of $receipt";
        $this->updateAmount();
    }

    public function getReceipt()
    {
        return $this->receipt;
    }

    /**
     * @return SalesOrder
     */
    public function getSalesOrder()
    {
        return $this->receipt->getSalesOrder();
    }

    public function setRefund(CardTransaction $refund)
    {
        $this->refund = $refund;
        $this->updateAmount();
    }

    private function updateAmount()
    {
        $amount = $this->refund ?
            -$this->refund->getAmountCaptured() :
            $this->receipt->getAmountCaptured();
        assertion($amount >= 0);
        $this->setAmount($amount);
    }

    /** @Assert\Callback */
    public function validateAmount(ExecutionContextInterface $context)
    {
        if ( $this->amount > $this->receipt->getAmountCaptured() ) {
            $context->buildViolation(
                "Refund amount cannot be greater than receipt amount.")
                ->atPath('amount')
                ->addViolation();
        }
    }

    protected function customizeDebtorTransaction(DebtorInvoice $debtorTrans)
    {
        /* no action needed */
    }

    protected function customizeGLTransaction(Transaction $glTrans)
    {
        /* no action needed */
    }

    /**
     * If this is a full refund (not just a void), this method returns the
     * refund CardTransaction.
     *
     * @return CardTransaction|null
     */
    protected function createTransactionForPaymentType(Transaction $glTrans)
    {
        if ( $this->refund ) {
            $this->refund->setAccountingTransaction($glTrans);
            $this->refund->setReferenceTransaction($this->receipt);
        }
        return $this->refund;
    }
}
