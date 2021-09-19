<?php

namespace Rialto\Accounting\Supplier;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Accounting\Bank\Account\AvailableChequeNumber;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Account\Cheque;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Company\Company;
use Rialto\Purchasing\Supplier\Supplier;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Creates a payment to a supplier.
 *
 * @AvailableChequeNumber
 */
class SupplierPayment implements Cheque
{
    /** @var Company */
    private $company;

    /** @var Supplier */
    private $supplier;

    /** @var DateTime */
    private $date;

    /** @var BankAccount */
    private $fromAccount = null;

    /**
     * @Assert\Type(type="integer");
     * @var integer
     */
    private $chequeNo = 0;

    private $paymentType = BankTransaction::TYPE_CHEQUE;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="numeric")
     * @var double
     */
    private $paymentAmount = null;

    /**
     * @Assert\Type(type="numeric")
     * @var double
     */
    private $discountAmount = 0;

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
        return $this->date;
    }

    public function setDate(DateTime $date)
    {
        $this->date = $date;
    }

    public function getAccount()
    {
        return $this->fromAccount;
    }

    public function setAccount(BankAccount $account)
    {
        $this->fromAccount = $account;
    }

    /** @return BankAccount */
    public function getBankAccount()
    {
        return $this->getAccount();
    }

    public function getChequeNumber()
    {
        return $this->chequeNo;
    }

    public function setChequeNumber($chequeNo)
    {
        $this->chequeNo = (int) $chequeNo;
    }

    public function getPaymentType()
    {
        return $this->paymentType;
    }

    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    }

    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    public function setPaymentAmount($paymentAmount)
    {
        $this->paymentAmount = $paymentAmount;
    }

    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;
    }

    public function getMemo()
    {
        return $this->memo;
    }

    public function setMemo($memo)
    {
        $this->memo = $memo;
    }

    public function getTotalAmount()
    {
        return $this->paymentAmount + $this->discountAmount;
    }

    /** @return SupplierTransaction */
    public function createPayment(ObjectManager $om)
    {
        $sysType = SystemType::fetchCreditorPayment();

        $glTrans = $this->createCoreTransaction($sysType);
        $om->persist($glTrans);

        $this->bankTrans = $this->createBankTransaction($glTrans);
        $om->persist($this->bankTrans);

        $suppReference = $this->bankTrans->isCheque() ?
            $this->bankTrans->getChequeNumber() : $this->paymentType;

        $suppTrans = $this->createSupplierTransaction($glTrans, $suppReference);
        $om->persist($suppTrans);

        $this->supplier->setLastPaid($this->paymentAmount, $this->date);

        return $suppTrans;
    }

    /** @return Transaction */
    private function createCoreTransaction(SystemType $sysType)
    {
        $glTrans = new Transaction($sysType);
        $glTrans->setChequeNumber($this->chequeNo);
        $glTrans->setDate($this->date);
        $glTrans->setMemo(sprintf('%s - %s', $this->supplier->getId(), $this->memo));

        $toAccount = $this->company->getCreditorsAccount();
        $glTrans->addEntry($toAccount, $this->getTotalAmount());
        $glTrans->addEntry($this->fromAccount->getGLAccount(), -$this->paymentAmount);

        if ( $this->discountAmount != 0 ) {
            $discountAccount = $this->company->getPaymentDiscountAccount();
            $glTrans->addEntry($discountAccount, -$this->discountAmount);
        }

        return $glTrans;
    }

    /** @return BankTransaction */
    private function createBankTransaction(Transaction $glTrans)
    {
        $bankTrans = new BankTransaction(
            $glTrans,
            $this->fromAccount,
            $this->paymentType);
        $bankTrans->setAmount(-$this->paymentAmount);
        if ( $bankTrans->isCheque() ) {
            $bankTrans->setChequeNumber($this->chequeNo);
            $this->fromAccount->confirmChequeNumber($this->chequeNo);
        }

        return $bankTrans;
    }

    /** @return SupplierTransaction */
    private function createSupplierTransaction(
        Transaction $glTrans,
        $suppReference)
    {
        $suppTrans = new SupplierTransaction($glTrans, $this->supplier);
        $suppTrans->setReference($suppReference);
        $suppTrans->setSubtotalAmount(-$this->getTotalAmount());
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
