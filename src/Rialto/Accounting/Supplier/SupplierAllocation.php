<?php

namespace Rialto\Accounting\Supplier;

use Rialto\Accounting\PaymentTransaction\PaymentAllocation;
use Rialto\Accounting\PaymentTransaction\PaymentTransaction;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Allocates our payments and credits against supplier invoices.
 * This allows us to know which invoice a payment was made for.
 *
 * @UniqueEntity(fields={"credit", "invoice"},
 *   message="This credit has already been allocated to this invoice.")
 */
class SupplierAllocation implements PaymentAllocation
{
    private $id;

    /**
     * @var float
     * @Assert\Type(type="numeric")
     * @Assert\GreaterThan(value=0)
     */
    private $amount;

    /** @var \DateTime */
    private $date;

    /**
     * @var SupplierTransaction
     * @Assert\NotNull
     */
    private $credit;

    /**
     * @var SupplierTransaction
     * @Assert\NotNull
     */
    private $invoice;

    public function __construct(SupplierTransaction $invoice, SupplierTransaction $credit)
    {
        $this->date = new \DateTime();
        $this->invoice = $invoice;
        $this->credit = $credit;
    }

    public function getId()
    {
        return $this->id;
    }

    /** @return float */
    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        $this->credit->updateAmountAllocated();
        $this->invoice->updateAmountAllocated();
    }

    /** @return SupplierTransaction */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @return bool True if $trans is the credit side of this allocation.
     */
    public function isForCredit(PaymentTransaction $trans)
    {
        return $trans === $this->credit;
    }

    /** @return SupplierTransaction */
    public function getInvoice()
    {
        return $this->invoice;
    }
}
