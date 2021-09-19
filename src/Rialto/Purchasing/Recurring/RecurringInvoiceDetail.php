<?php

namespace Rialto\Purchasing\Recurring;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Database\Orm\Persistable;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Invoice\SupplierInvoiceItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A template for a GLEntry that will be created when the recurring
 * invoice is entered.
 */
class RecurringInvoiceDetail implements RialtoEntity, Persistable
{
    const MONEY_PRECISION = 2;

    /** @var int */
    private $id;

    /**
     * @var RecurringInvoice
     * @Assert\NotNull
     */
    private $invoice;

    /**
     * @var GLAccount
     * @Assert\NotNull
     */
    private $account;

    /**
     * @var float
     * @Assert\NotBlank(message="Amount is required.")
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0.01, minMessage="Amount must at least {{ limit }}.")
     */
    private $amount;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $reference;

    public function getId()
    {
        return $this->id;
    }

    public function getInvoice()
    {
        return $this->invoice;
    }

    public function setInvoice(RecurringInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function setAccount(GLAccount $account)
    {
        $this->account = $account;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = (float) $amount;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference($reference)
    {
        $this->reference = trim($reference);
    }

    public function getEntities()
    {
        return [$this];
    }

    public function createInvoiceItem($lineNumber)
    {
        $item = new SupplierInvoiceItem();
        $item->setLineNumber($lineNumber);
        $item->setQtyInvoiced(1);
        $item->setGLAccount($this->account);
        $item->setExtendedCost($this->amount);
        $item->setDescription($this->reference);
        return $item;
    }

}
