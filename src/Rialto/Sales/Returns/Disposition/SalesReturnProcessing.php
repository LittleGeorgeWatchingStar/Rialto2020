<?php

namespace Rialto\Sales\Returns\Disposition;

use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Returns\SalesReturn;


/**
 * Subclasses store the results of the various stages of processing
 * a sales return, such as receiving and testing.
 */
abstract class SalesReturnProcessing
{
    /** @var SalesReturn */
    protected $salesReturn;

    /** @var SalesReturnInstructions */
    private $instructions;

    public function __construct(SalesReturn $rma)
    {
        $this->salesReturn = $rma;
    }

    public function getSalesReturn()
    {
        return $this->salesReturn;
    }

    public function getRmaNumber()
    {
        return $this->salesReturn->getRmaNumber();
    }

    /** @return DebtorTransaction */
    public function getOriginalInvoice()
    {
        return $this->salesReturn->getOriginalInvoice();
    }

    public function getOriginalInvoiceNumber()
    {
        return $this->getOriginalInvoice()->getSystemTypeNumber();
    }

    public function hasReplacementOrder()
    {
        return $this->salesReturn->hasReplacementOrder();
    }

    /** @return SalesOrder|null */
    public function getReplacementOrder()
    {
        return $this->salesReturn->getReplacementOrder();
    }

    /** @return Customer */
    public function getCustomer()
    {
        return $this->salesReturn->getCustomer();
    }

    public function getTaxAccount()
    {
        return $this->salesReturn->getTaxAccount();
    }

    /** @return string[] */
    public function getInstructions()
    {
        return $this->instructions->toArray();
    }

    /** @return string[] */
    public function getLabels()
    {
        return $this->instructions->getLabels();
    }

    public function mergeInstructions(SalesReturnInstructions $newInst)
    {
        if ( ! $this->instructions ) $this->instructions = $newInst;
        else $this->instructions->merge($newInst);
    }

}
