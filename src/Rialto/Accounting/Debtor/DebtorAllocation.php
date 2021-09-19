<?php

namespace Rialto\Accounting\Debtor;

use Rialto\Accounting\PaymentTransaction\PaymentAllocation;
use Rialto\Accounting\PaymentTransaction\PaymentTransaction;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * Allocates customer payments and credits against our invoices.
 * This allows us to know which invoice a payment was made for.
 *
 * @UniqueEntity(fields={"credit", "invoice"},
 *   message="This credit has already been allocated to this invoice.")
 */
class DebtorAllocation
implements PaymentAllocation
{
    private $id;

    /** @var DebtorCredit */
    private $credit;

    /** @var DebtorInvoice */
    private $invoice;

    /** @var \DateTime */
    private $date;

    /**
     * @var float
     * @Assert\Type(type="numeric")
     * @Assert\GreaterThan(value=0)
     */
    private $amount;

    public function __construct(DebtorInvoice $invoice, DebtorCredit $credit)
    {
        $this->invoice = $invoice;
        $this->credit = $credit;
        $this->date = new \DateTime();
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

    /** @Assert\Callback(groups={"invoice"}) */
    public function validateCreditAmount(ExecutionContextInterface $context)
    {
        $this->credit->validateAmountAllocated($context);
    }

    /** @return DebtorTransaction */
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

    /** @return DebtorTransaction */
    public function getInvoice()
    {
        return $this->invoice;
    }
}
