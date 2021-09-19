<?php

namespace Rialto\Accounting\Debtor\Refund;

use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Account\Cheque;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Refunds a customer via cheque or bank transfer.
 */
class BankRefund extends CustomerRefund implements Cheque
{
    /**
     * @var SalesOrder
     * @Assert\NotNull(message="Sales order is required.")
     */
    private $salesOrder;

    /**
     * @Assert\Type(type="integer");
     * @var integer
     */
    private $chequeNo = 0;

    /** @var string */
    private $paymentType = BankTransaction::TYPE_CHEQUE;

    /**
     * @Assert\NotNull
     * @var BankAccount
     */
    private $bankAccount;

    /**
     * @return SalesOrder
     */
    public function getSalesOrder()
    {
        return $this->salesOrder;
    }

    public function setSalesOrder(SalesOrder $salesOrder)
    {
        $this->salesOrder = $salesOrder;
    }

    public function setBankAccount(BankAccount $account)
    {
        $this->bankAccount = $account;
        $this->setAccount($account->getGLAccount());
    }

    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    public function setChequeNumber($chequeNo)
    {
        $this->chequeNo = $chequeNo;
    }

    public function getChequeNumber()
    {
        return $this->chequeNo;
    }

    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    }

    public function getPaymentType()
    {
        return $this->paymentType;
    }

    protected function customizeDebtorTransaction(DebtorInvoice $debtorTrans)
    {
        $debtorTrans->setMemo($this->chequeNo);
    }

    protected function customizeGLTransaction(Transaction $glTrans)
    {
        $glTrans->setChequeNumber($this->chequeNo);
    }

    /** @return BankTransaction */
    protected function createTransactionForPaymentType(Transaction $glTrans)
    {
        $bTrans = new BankTransaction(
            $glTrans,
            $this->bankAccount,
            $this->paymentType
        );
        $bTrans->setAmount(-$this->amount);
        $bTrans->setReference(sprintf('%s, %s',
            $this->customer->getId(),
            $this->customer->getName()
        ));
        if ($bTrans->isCheque()) {
            $bTrans->setChequeNumber($this->chequeNo);
            $this->bankAccount->confirmChequeNumber($this->chequeNo);
        }
        return $bTrans;
    }
}
