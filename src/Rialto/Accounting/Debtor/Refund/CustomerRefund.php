<?php

namespace Rialto\Accounting\Debtor\Refund;

use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Entity\RialtoEntity;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Base class for entering a customer refund.
 */
abstract class CustomerRefund
{
    /** @var Customer */
    protected $customer;

    /** @var \DateTime */
    protected $date;

    /** @var GLAccount */
    protected $fromAccount = null;

    /**
     * The amount to refund. Must be a positive number.
     *
     * @Assert\NotBlank
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0.01, minMessage="Amount must be at least {{ limit }}.")
     * @var double
     */
    protected $amount = null;

    protected $memo = 'Refund';

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
        $this->date = new \DateTime();
    }

    /**
     * @return SalesOrder
     */
    public abstract function getSalesOrder();

    public function setDate(\DateTime $date)
    {
        $this->date = clone $date;
    }

    public function getDate()
    {
        return clone $this->date;
    }

    public function setAccount(GLAccount $account)
    {
        $this->fromAccount = $account;
    }

    public function getAccount()
    {
        return $this->fromAccount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    /** @return DebtorInvoice */
    public function createRefund(RefundDataSource $ds)
    {
        $sysType = $ds->getSystemType();
        $glTrans = new Transaction($sysType);
        $glTrans->setDate($this->getDate());
        $glTrans->setMemo($this->memo);

        $debtorTrans = $this->createDebtorTransaction($glTrans);
        $this->createLedgerEntries($glTrans, $ds);
        $ds->persist($glTrans);
        $ds->persist($debtorTrans);

        $entity = $this->createTransactionForPaymentType($glTrans);
        if ($entity) {
            $ds->persist($entity);
        }

        return $debtorTrans;
    }

    /** @return DebtorInvoice */
    private function createDebtorTransaction(Transaction $glTrans)
    {
        $debtorTrans = new DebtorInvoice($glTrans, $this->getSalesOrder());
        $debtorTrans->setSubtotalAmount($this->amount);
        $this->customizeDebtorTransaction($debtorTrans);
        return $debtorTrans;
    }

    protected abstract function customizeDebtorTransaction(
        DebtorInvoice $debtorTrans);

    private function createLedgerEntries(
        Transaction $glTrans,
        RefundDataSource $ds)
    {
        $this->customizeGLTransaction($glTrans);
        $glTrans->addEntry($this->fromAccount, -$this->amount);

        $amtOwed = $this->getSalesOrder()->getAmountOwedByCustomer();

        $amtToReceivable = $this->round(min($this->amount, $amtOwed));
        $amtToPrepaid = $this->round($this->amount - $amtToReceivable);
        assert($amtToPrepaid >= 0);

        if ($amtToReceivable > 0) {
            $acctsRec = $ds->getDebtorAccount();
            $glTrans->addEntry($acctsRec, $amtToReceivable);
        }
        if ($amtToPrepaid > 0) {
            $prepaidRev = $ds->getPrepaidAccount();
            $glTrans->addEntry($prepaidRev, $amtToPrepaid);
        }
    }

    private function round($amount)
    {
        return GLEntry::round($amount);
    }

    protected abstract function customizeGLTransaction(Transaction $glTrans);

    /** @return RialtoEntity|null */
    protected abstract function createTransactionForPaymentType(Transaction $glTrans);
}
