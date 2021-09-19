<?php

namespace Rialto\Accounting\Supplier;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\PaymentTransaction\PaymentAllocation;
use Rialto\Accounting\PaymentTransaction\PaymentTransaction;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\IllegalStateException;
use Rialto\Purchasing\Recurring\RecurringInvoice;
use Rialto\Purchasing\Supplier\Supplier;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * A financial transaction between the company and a supplier.
 *
 * @todo Refactor into Invoice and Credit subclasses, as with DebtorTransaction.
 */
class SupplierTransaction implements PaymentTransaction, RialtoEntity
{
    const MONEY_PRECISION = 4;

    private $id;

    /** @var Transaction */
    private $transaction;

    /** @deprecated use $transaction instead */
    private $systemType;
    /** @deprecated use $transaction instead */
    private $systemTypeNumber;

    /** @var DateTime */
    private $date;

    /** @var float */
    private $rate = 1.0;

    /** @var float */
    private $subtotalAmount;

    /** @var float */
    private $taxAmount = 0.0;

    /**
     * @deprecated "Settled" just means fully allocated, and so can be
     *   calculated.
     */
    private $settled = false;
    private $amountAllocated = 0.0;
    private $DiffOnExch;
    private $reference = '';
    private $memo = '';

    /** @var PaymentAllocation[] */
    private $invoiceAllocations;

    /** @var PaymentAllocation[] */
    private $creditAllocations;

    /** @var Supplier */
    private $supplier;

    /** @var DateTime */
    private $dueDate;

    /**
     * Indicates that this invoice should not be automatically paid.
     *
     * @var bool
     * @Assert\Type(type="boolean")
     */
    private $hold = true;

    /** @var RecurringInvoice */
    private $recurringInvoice;

    public function __construct(Transaction $glTrans,
                                Supplier $supplier)
    {
        $this->transaction = $glTrans;
        $this->invoiceAllocations = new ArrayCollection();
        $this->creditAllocations = new ArrayCollection();
        $this->systemType = $glTrans->getSystemType();
        $this->systemTypeNumber = $glTrans->getGroupNo();
        $this->date = clone $glTrans->getDate();
        $this->supplier = $supplier;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param float $amount
     *  (optional) Defaults to the maximum amount that can be allocated.
     *
     * @return PaymentAllocation
     */
    public function allocateFrom(PaymentTransaction $credit, $amount = null)
    {
        assert($this->isInvoice());
        assert($credit->isCredit());
        if (null === $amount) {
            $amount = min(
                abs($this->getAmountUnallocated()),
                abs($credit->getAmountUnallocated())
            );
        }
        $payAlloc = $this->getOrCreateAllocation($credit);
        $payAlloc->setAmount($amount);
        return $payAlloc;
    }

    /**
     * @return PaymentAllocation[]
     * @Assert\Valid(traverse=true)
     */
    public function getAllocations(): array
    {
        if ($this->isInvoice()) {
            return $this->invoiceAllocations->toArray();
        } else {
            return $this->creditAllocations->toArray();
        }
    }

    private function getOrCreateAllocation(SupplierTransaction $credit)
    {
        assert($this->isInvoice());
        $payAlloc = $this->getAllocationOrNull($credit);
        if (!$payAlloc) {
            $payAlloc = new SupplierAllocation($this, $credit);
            $this->addAllocation($payAlloc);
        }
        return $payAlloc;
    }

    private function getAllocationOrNull(SupplierTransaction $credit)
    {
        foreach ($this->invoiceAllocations as $payAlloc) {
            if ($payAlloc->isForCredit($credit)) {
                return $payAlloc;
            }
        }
        return null;
    }

    public function addAllocation(PaymentAllocation $alloc)
    {
        /* @var $alloc SupplierAllocation */
        if ($this->isInvoice()) {
            $this->invoiceAllocations[] = $alloc;
            $credit = $alloc->getCredit();
            $credit->addAllocation($alloc);
        } else {
            $this->creditAllocations[] = $alloc;
        }
        $this->updateAmountAllocated();
    }

    public function removeAllocation(PaymentAllocation $alloc)
    {
        /* @var $alloc SupplierAllocation */
        if ($this->isInvoice()) {
            $this->invoiceAllocations->removeElement($alloc);
            $credit = $alloc->getCredit();
            $credit->removeAllocation($alloc);
        } else {
            $this->creditAllocations->removeElement($alloc);
        }
        $this->updateAmountAllocated();
    }


    /**
     * Update the amount allocated and the settled flag to match the current
     * allocations.
     */
    public function updateAmountAllocated()
    {
        $this->amountAllocated = $this->recalculateAmountAllocated();
        $this->settled = $this->isFullyAllocated();
    }

    private function recalculateAmountAllocated(): float
    {
        $total = 0;
        foreach ($this->getAllocations() as $alloc) {
            $total += $alloc->getAmount();
        }
        $sign = $this->isInvoice() ? 1 : -1;
        return $this->round($sign * $total);
    }

    public function isSettled(): bool
    {
        return (bool) $this->isFullyAllocated();
    }

    /**
     * @return GLEntry[]
     */
    public function getGLEntries(): array
    {
        $dbm = ErpDbManager::getInstance();
        $mapper = $dbm->getRepository(GLEntry::class);
        return $mapper->findByEvent($this);
    }

    /**
     * @return BankTransaction[]
     */
    public function getBankTransactions(): array
    {
        $dbm = ErpDbManager::getInstance();
        $mapper = $dbm->getRepository(BankTransaction::class);
        return $mapper->findByEvent($this);
    }

    private function round($amount): float
    {
        return round($amount, $this->getMoneyPrecision());
    }

    /** @Assert\Callback */
    public function validateAmountAllocated(ExecutionContextInterface $context)
    {
        $total = abs($this->getTotalAmount());
        $alloc = abs($this->recalculateAmountAllocated());
        if ($alloc > $total) {
            $context->addViolation(sprintf(
                'Cannot allocate more (%s) than the total amount (%s) of %s.',
                number_format($alloc, 2),
                number_format($total, 2),
                $this
            ));
        }
    }

    public function getAmountAllocated(): float
    {
        return $this->amountAllocated;
    }

    public function getAmountUnallocated(): float
    {
        return $this->getTotalAmount() - $this->amountAllocated;
    }

    public function isFullyAllocated(): bool
    {
        return bceq($this->getAmountUnallocated(), 0, $this->getMoneyPrecision());
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date ? clone $this->date : null;
    }

    /**
     * @return SystemType
     */
    public function getSystemType()
    {
        return $this->transaction->getSystemType();
    }

    /**
     * @return int
     */
    public function getSystemTypeNumber()
    {
        return $this->transaction->getGroupNo();
    }

    public function getLabel(): string
    {
        $sysType = $this->getSystemType();
        $typeNo = $this->getSystemTypeNumber();
        return strtolower($sysType->getName() . ' ' . $typeNo);
    }

    public function __toString()
    {
        return $this->getLabel();
    }

    public function getSummary(): string
    {
        return sprintf('%s %s for %s ($%s of %s)',
            $this->date->format('Y-m-d'),
            $this->getLabel(),
            $this->getCompanyName(),
            number_format($this->getAmountUnallocated(), 2),
            number_format($this->getTotalAmount(), 2));
    }

    public function isInvoice(): bool
    {
        return in_array($this->getSystemType()->getId(), self::getInvoiceTypes());
    }

    public static function getInvoiceTypes()
    {
        return [
            SystemType::PURCHASE_INVOICE,
            SystemType::CREDITOR_REFUND,
        ];
    }

    public function isCredit(): bool
    {
        return in_array($this->getSystemType()->getId(), self::getCreditTypes());
    }

    public static function getCreditTypes()
    {
        return [
            SystemType::CREDITOR_PAYMENT,
            SystemType::DEBIT_NOTE,
        ];
    }

    private function getMoneyPrecision()
    {
        return self::MONEY_PRECISION;
    }

    public function getPeriod()
    {
        $dbm = ErpDbManager::getInstance();
        $mapper = $dbm->getRepository(Period::class);
        return $mapper->findForDate($this->getDate());
    }

    public function cancelPayment()
    {
        if (!$this->isCredit()) {
            throw new IllegalStateException("Only credits can be cancelled.");
        }

        $origAmount = abs($this->getTotalAmount());
        $this->deleteAllocations();
        $this->settled = true;
        $this->subtotalAmount = 0;
        $this->taxAmount = 0;
        $this->memo .= sprintf(' canceled $%s on %s',
            number_format($origAmount, self::MONEY_PRECISION),
            date('Y-m-d')
        );
    }

    private function deleteAllocations()
    {
        foreach ($this->creditAllocations as $alloc) {
            $invoice = $alloc->getInvoice();
            $invoice->removeAllocation($alloc);
        }
        $this->creditAllocations->clear();
        $this->updateAmountAllocated();
    }

    public function getSubtotalAmount(): float
    {
        return $this->subtotalAmount;
    }

    public function setSubtotalAmount($amount)
    {
        $this->subtotalAmount = $amount;
    }

    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }

    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = (float) $taxAmount;
    }

    public function getTotalAmount(): float
    {
        return $this->round($this->subtotalAmount + $this->taxAmount);
    }

    public function getMemo(): string
    {
        return (string) $this->memo;
    }

    public function setMemo($memo)
    {
        $this->memo = trim($memo);
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference($supplierRef)
    {
        $this->reference = $supplierRef;
    }

    public function setDate(DateTime $date)
    {
        $this->date = clone $date;
    }

    public function getDueDate()
    {
        return $this->dueDate ? clone $this->dueDate : null;
    }

    public function setDueDate(DateTime $dueDate)
    {
        $this->dueDate = clone $dueDate;
    }

    /**
     * Sets the due date based on the transaction date and supplier's payment terms.
     *
     * @return DateTime The new due date.
     */
    public function calculateDueDate()
    {
        assert($this->supplier !== null);
        $terms = $this->supplier->getPaymentTerms();
        $this->dueDate = $terms->calculateDueDate($this->date);
        return clone $this->dueDate;
    }

    /** @return boolean */
    public function isOverdue()
    {
        $due = $this->getDueDate();
        $now = new DateTime();
        return $due ? ($due < $now) : false;
    }

    /**
     * @param string $payday The day of the week on which the user
     *   wants to make payments.
     * @return DateTime|null The date when this invoice should be paid.
     */
    public function getPaymentDate($payday)
    {
        $payDate = $this->getDueDate();
        if (!$payDate) {
            return null;
        }

        $today = new DateTime();
        $today->setTime(0, 0, 0);

        /* Pay it the day of the week before it's due. */
        $payDate->modify("$payday -1 week");
        $payDate->setTime(0, 0, 0);

        /* Unless it's past, in which case pay it the upcoming payday. */
        if ($payDate < $today) {
            $payDate = new DateTime($payday);
            $payDate->setTime(0, 0, 0);
        }
        return $payDate;
    }

    /** @return Supplier */
    public function getSupplier()
    {
        return $this->supplier;
    }

    public function getSupplierName()
    {
        return $this->supplier->getName();
    }

    private function getCompanyName()
    {
        return $this->getSupplierName();
    }

    public function isHold()
    {
        return $this->hold;
    }

    public function setHold($hold)
    {
        $this->hold = (bool) $hold;
    }

    public function setRecurringInvoice(RecurringInvoice $recurringInvoice)
    {
        $this->recurringInvoice = $recurringInvoice;
    }

}
