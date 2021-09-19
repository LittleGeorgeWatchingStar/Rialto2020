<?php

namespace Rialto\Accounting\Supplier;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Company\Company;
use Rialto\Purchasing\Supplier\Supplier;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Creates a refund from a supplier.
 */
class SupplierRefund
{
    /** @var Company */
    private $company;

    /** @var Supplier */
    private $supplier;

    /** @var DateTime */
    private $date;

    /** @var BankAccount */
    private $toAccount = null;

    /**
     * @Assert\Type(type="integer");
     * @var integer
     */
    private $chequeNo = null;

    private $paymentType = BankTransaction::TYPE_CHEQUE;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="numeric")
     * @var double
     */
    private $refundAmount = null;

    private $memo = null;

    private $bankTrans = null;

    public function __construct(Company $company, Supplier $supplier)
    {
        $this->company = $company;
        $this->supplier = $supplier;
        $this->date = new DateTime();
    }

    public function getDate()
    {
        return clone $this->date;
    }

    public function setDate(DateTime $date)
    {
        $this->date = clone $date;
    }

    public function getAccount()
    {
        return $this->toAccount;
    }

    public function setAccount(BankAccount $account)
    {
        $this->toAccount = $account;
    }

    public function getChequeNumber()
    {
        return $this->chequeNo;
    }

    public function setChequeNumber($chequeNo)
    {
        $this->chequeNo = $chequeNo;
    }

    public function getPaymentType()
    {
        return $this->paymentType;
    }

    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    }

    public function getRefundAmount()
    {
        return $this->refundAmount;
    }

    public function setRefundAmount($amount)
    {
        $this->refundAmount = $amount;
    }

    public function getMemo()
    {
        return $this->memo;
    }

    public function setMemo($memo)
    {
        $this->memo = $memo;
    }

    /** @return SupplierTransaction */
    public function createRefund(ObjectManager $om)
    {
        $sysType = SystemType::fetchCreditorRefund($om);
        $glTrans = $this->createCoreTransaction($sysType);
        $om->persist($glTrans);

        $this->bankTrans = $this->createBankTransaction($glTrans);
        $om->persist($this->bankTrans);

        $suppReference = $this->paymentType;
        if ($this->chequeNo) {
            $suppReference .= ' #' . $this->chequeNo;
        }

        $suppTrans = $this->createSupplierTransaction($glTrans, $suppReference);
        $om->persist($suppTrans);

        return $suppTrans;
    }

    private function createCoreTransaction(SystemType $sysType): Transaction
    {
        $glTrans = new Transaction($sysType);
//        $glTrans->setChequeNumber($this->chequeNo);
        $glTrans->setDate($this->date);
        $glTrans->setMemo(sprintf('Refund from %s - %s', $this->supplier->getId(), $this->memo));
        $fromAccount = $this->company->getCreditorsAccount();
        $glTrans->addEntry($fromAccount, -$this->refundAmount);
        $glTrans->addEntry($this->toAccount->getGLAccount(), $this->refundAmount);
        return $glTrans;
    }


    private function createBankTransaction(Transaction $glTrans): BankTransaction
    {
        $bankTrans = new BankTransaction(
            $glTrans,
            $this->toAccount,
            $this->paymentType);
        $bankTrans->setAmount($this->refundAmount);

        return $bankTrans;
    }

    private function createSupplierTransaction(
        Transaction $glTrans,
        $suppReference): SupplierTransaction
    {
        $suppTrans = new SupplierTransaction($glTrans, $this->supplier);
        $suppTrans->setReference($suppReference);
        $suppTrans->setSubtotalAmount($this->refundAmount);
        $suppTrans->setMemo($this->memo);

        return $suppTrans;
    }


    /**
     * @return BankTransaction|null
     *  Null if createPayment() has not been called yet.
     */
    public function getBankTransaction()
    {
        return $this->bankTrans;
    }

}
