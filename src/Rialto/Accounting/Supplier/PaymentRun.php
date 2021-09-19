<?php

namespace Rialto\Accounting\Supplier;


use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Accounting\Bank\Account\AvailableChequeNumber;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Account\Cheque;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Currency\Currency;
use Rialto\Company\Company;
use Rialto\Purchasing\Supplier\Supplier;
use SplObjectStorage as Map;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Creates many supplier payments all at once.
 *
 * @AvailableChequeNumber
 */
class PaymentRun implements Cheque
{
    /** @var Company */
    private $company;

    /** @var string */
    public $matching = '';

    /**
     * @var Currency
     * @Assert\NotNull
     */
    public $currency;

    /**
     * @var DateTime
     * @Assert\NotNull
     * @Assert\Date
     */
    public $dueUntil;

    /**
     * @var BankAccount
     * @Assert\NotNull
     */
    public $fromAccount;

    /**
     * @var string
     * @Assert\NotBlank
     */
    public $paymentType;

    /**
     * @var integer
     * @Assert\Range(min=1)
     */
    public $chequeNumber;

    /** @var Map<Supplier, SupplierTransaction[]> */
    private $invoices;

    /** @var Map<Supplier, SupplierPayment> */
    private $payments;

    public function __construct(Company $company)
    {
        $this->company = $company;
        $this->dueUntil = new DateTime('last day of this month');
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /** @return int */
    public function getChequeNumber()
    {
        return $this->chequeNumber;
    }

    /** @return BankAccount */
    public function getBankAccount()
    {
        return $this->fromAccount;
    }

    /** @Assert\Callback */
    public function validateChequeNumber(ExecutionContextInterface $context)
    {
        if ($this->isCheque() && (!$this->chequeNumber)) {
            $context->addViolation('Cheque number is required.');
        }
    }

    /** @return bool */
    public function isCheque()
    {
        return BankTransaction::TYPE_CHEQUE == $this->paymentType;
    }

    /** @return DateTime */
    public function getPaymentDate()
    {
        return new DateTime();
    }

    /**
     * @return Map<Supplier, SupplierTransaction[]>
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    public function loadInvoices(SupplierTransactionRepository $repo)
    {
        $this->invoices = new \SplObjectStorage();
        $invoices = $repo->findForPaymentRun($this);
        foreach ($invoices as $invoice) {
            $supplier = $invoice->getSupplier();
            $bySupplier = isset($this->invoices[$supplier]) ?
                $this->invoices[$supplier] : [];
            $bySupplier[] = $invoice;
            $this->invoices[$supplier] = $bySupplier;
        }
        $this->initPayments();
    }

    private function initPayments()
    {
        $this->payments = new \SplObjectStorage();
        $chequeNo = $this->chequeNumber;
        foreach ($this->invoices as $supplier) {
            /** @var Supplier $supplier */
            assertion($supplier instanceof Supplier);
            $invoices = $this->getInvoicesForSupplier($supplier);
            $payment = new SupplierPayment($this->company, $supplier);
            $paymentTotal = 0;
            foreach ($invoices as $invoice) {
                /* @var $invoice SupplierTransaction */
                $paymentTotal += $invoice->getAmountUnallocated();
            }
            $payment->setPaymentAmount($paymentTotal);
            $payment->setDate($this->getPaymentDate());
            $payment->setAccount($this->fromAccount);
            if ($this->isCheque()) {
                $payment->setChequeNumber($chequeNo);
            }
            $payment->setPaymentType($this->paymentType);
            $payment->setMemo(sprintf('%s - %s payment run on %s - %s',
                $supplier->getId(),
                $supplier,
                $this->getPaymentDate()->format('m/d/Y'),
                $chequeNo));

            $this->payments[$supplier] = $payment;
            $chequeNo++;
        }
    }

    public function hasPayments()
    {
        return count($this->payments) > 0;
    }

    /** @return SupplierTransaction[] */
    private function getInvoicesForSupplier(Supplier $supplier)
    {
        return $this->invoices[$supplier];
    }

    /** @return SupplierPayment */
    public function getPayment(Supplier $supplier)
    {
        return $this->payments[$supplier];
    }

    /**
     * Creates the accounting records and enters the payments into the system.
     */
    public function processPayments(ObjectManager $om)
    {
        foreach ($this->payments as $supplier) {
            /** @var Supplier $supplier */
            $payment = $this->getPayment($supplier);
            $suppTrans = $payment->createPayment($om);
            foreach ($this->getInvoicesForSupplier($supplier) as $invoice) {
                $invoice->allocateFrom($suppTrans);
            }
        }
    }

    /** @return float */
    public function getGrandTotal()
    {
        $total = 0;
        foreach ($this->payments as $supplier) {
            /** @var Supplier $supplier */
            $payment = $this->getPayment($supplier);
            $total += $payment->getTotalAmount();
        }
        return $total;
    }
}
