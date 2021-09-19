<?php

namespace Rialto\Accounting\Debtor\Credit;

use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * Creates the DebtorTransaction, BankTransaction and related records
 * needed to record a customer payment via wire or cheque receipt.
 */
class WireReceipt
    extends CustomerReceipt
    implements HasBankingFee
{
    /**
     * @Assert\NotNull
     * @var BankAccount
     */
    private $bankAccount;

    /**
     * @Assert\Type(type="numeric", message="Fee amount must be numeric.")
     * @Assert\Range(min=0, minMessage="Fee amount cannot be negative.")
     * @var double
     */
    private $feeAmount = 0;

    private $type = BankTransaction::TYPE_DIRECT;

    /** @var string */
    private $transactionId;

    /**
     * @var integer
     * @Assert\Type(type="integer")
     * @Assert\Range(min=0, max=2147483647)
     */
    private $chequeNo;

    private $bankTrans = null;

    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    public function setBankAccount(BankAccount $bankAccount)
    {
        $this->bankAccount = $bankAccount;
    }

    public function getFeeAmount()
    {
        return $this->feeAmount;
    }

    /**
     * Sets the amount of the wire transfer fee. This value has no
     * effect on the accounting that is done, but will be included in
     * any email to the customer.
     *
     * @param double $feeAmount
     */
    public function setFeeAmount($feeAmount)
    {
        $this->feeAmount = $feeAmount;
    }

    public function createCreditNoteForBankingFee()
    {
        $creditNote = new CreditNote($this->customer);
        $creditNote->setAmount($this->getFeeAmount());
        $order = $this->getSalesOrder();
        if ($order) {
            $creditNote->setSalesOrder($order);
            $creditNote->setMemo("Bank transfer fee for $order");
        } else {
            $creditNote->setMemo("Bank transfer fee for {$this->customer}");
        }
        $creditNote->setDate($this->getDate());
        $creditNote->setToAccount(GLAccount::fetchBankCharges());

        /* The creation of the credit note for any transfers fees should be
         * invisible to the customer. */
        $creditNote->setSendEmail(false);
        return $creditNote;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getDescription()
    {
        switch ($this->type) {
            case BankTransaction::TYPE_CHEQUE:
                return 'check';
            default:
                return 'wire transfer';
        }
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function setTransactionId($transactionId)
    {
        $this->transactionId = trim($transactionId);
    }

    public function getChequeNo()
    {
        return $this->chequeNo;
    }

    public function setChequeNo($chequeNo)
    {
        $this->chequeNo = (int) $chequeNo;
    }

    /** @Assert\Callback */
    public function validateTypeFields(ExecutionContextInterface $context)
    {
        if ($this->type == BankTransaction::TYPE_DIRECT) {
            if (! $this->transactionId) {
                $context->buildViolation(
                    "Transaction ID is required for type {$this->type}.")
                    ->atPath('transactionId')
                    ->addViolation();
            }
        } else if (! $this->chequeNo) {
            $context->buildViolation(
                "Cheque number is required for type {$this->type}.")
                ->atPath('chequeNo')
                ->addViolation();
        }
    }


    public function createAdditionalTransactions(
        Transaction $trans,
        DbManager $dbm)
    {
        $this->bankTrans = $this->createBankTransaction($trans);
        $dbm->persist($this->bankTrans);
    }

    /** @return BankTransaction */
    private function createBankTransaction(Transaction $glTrans)
    {
        $bankTrans = new BankTransaction(
            $glTrans,
            $this->bankAccount,
            $this->type
        );
        $bankTrans->setAmount($this->amount);
        $bankTrans->setReference($this->transactionId);
        if ($this->type == BankTransaction::TYPE_CHEQUE) {
            $bankTrans->setChequeNumber($this->chequeNo);
        }
        return $bankTrans;
    }

    public function getToAccount()
    {
        return $this->bankAccount->getGLAccount();
    }

    protected function getMemoForBranch(CustomerBranch $branch)
    {
        return sprintf('Wire receipt from %s',
            $branch->getBranchName()
        );
    }

    protected function getMemoForOrder(SalesOrder $salesOrder)
    {
        return sprintf('Wire receipt from %s for order %s',
            $salesOrder->getCustomerBranch()->getBranchName(),
            $salesOrder->getId()
        );
    }

    public function getTotalAmount()
    {
        return $this->amount + $this->feeAmount;
    }

    /**
     * @return BankTransaction|null
     *  Null if save() has not been called yet.
     */
    public function getBankTransaction()
    {
        return $this->bankTrans;
    }

}
