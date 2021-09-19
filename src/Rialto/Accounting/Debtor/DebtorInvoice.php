<?php

namespace Rialto\Accounting\Debtor;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Accounting\Card\CapturableInvoice;
use Rialto\Accounting\PaymentTransaction\CreditTransaction;
use Rialto\Accounting\PaymentTransaction\InvoiceTransaction;
use Rialto\Accounting\PaymentTransaction\PaymentAllocation;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Sales\Invoice\SalesInvoiceItem;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Shipping\Shipper\Shipper;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A debtor transaction which increases the customer's debt to us.
 */
class DebtorInvoice extends DebtorTransaction implements
    InvoiceTransaction,
    CapturableInvoice
{
    /**
     * @var DebtorAllocation[]
     * @Assert\Valid(traverse=true)
     */
    private $allocations;

    /** @var SalesInvoiceItem[] */
    private $lineItems;

    /** @var SalesOrder */
    private $salesOrder;

    /** @var Shipper */
    private $shipper;

    /** @var string Typically the tracking number of the shipment */
    private $consignment = '';

    public function __construct(Transaction $glTrans, SalesOrder $order)
    {
        parent::__construct($glTrans);
        $this->setSalesOrder($order);
        $this->allocations = new ArrayCollection();
        $this->lineItems = new ArrayCollection();
    }

    protected function getAllowedTypes(): array
    {
        return [
            SystemType::SALES_INVOICE,
            SystemType::CUSTOMER_REFUND
        ];
    }

    /**
     * @param DebtorCredit $credit
     * @param float $amount (optional) Defaults to the maximum amount that
     *   can be allocated.
     * @return DebtorAllocation
     */
    public function allocateFrom(CreditTransaction $credit, $amount = null)
    {
        $this->updateAmountAllocated();
        $credit->updateAmountAllocated();
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
     * @return DebtorAllocation[]
     * @Assert\Valid(traverse=true)
     */
    public function getAllocations(): array
    {
        return $this->allocations->toArray();
    }

    /** @return DebtorAllocation */
    public  function getOrCreateAllocation(DebtorCredit $credit)
    {
        $payAlloc = $this->getAllocationOrNull($credit);
        if (! $payAlloc ) {
            $payAlloc = new DebtorAllocation($this, $credit);
            $this->addAllocation($payAlloc);
        }
        return $payAlloc;
    }

    private function getAllocationOrNull(DebtorCredit $credit)
    {
        foreach ( $this->allocations as $payAlloc ) {
            if ( $payAlloc->isForCredit($credit)  ) {
                return $payAlloc;
            }
        }
        return null;
    }

    /** @param $alloc DebtorAllocation */
    public function addAllocation(PaymentAllocation $alloc)
    {
        // check to avoid infinite recursion
        if (! $this->allocations->contains($alloc) ) {
            $this->allocations[] = $alloc;
            $this->updateAmountAllocated();

            $credit = $alloc->getCredit();
            $credit->addAllocation($alloc);
        }
    }

    /** @param $alloc DebtorAllocation */
    public function removeAllocation(PaymentAllocation $alloc)
    {
        // check to avoid infinite recursion
        if ( $this->allocations->contains($alloc) ) {
            $this->allocations->removeElement($alloc);
            $this->updateAmountAllocated();

            $credit = $alloc->getCredit();
            $credit->removeAllocation($alloc);
        }
    }

    public function getSourceId()
    {
        return $this->salesOrder->getSourceId();
    }

    public function canBeCaptured()
    {
        return $this->getAmountToCapture() > 0
            && $this->salesOrder->getCardAuthorization();
    }

    public function getAmountToCapture()
    {
        return $this->getAmountUnallocated();
    }

    /**
     * Returns the stock moves of this transaction consolidated by stock item
     * into a list of SalesOrderItem objects.
     *
     * @return SalesInvoiceItem[]
     */
    public function getLineItems()
    {
        return $this->lineItems->toArray();
    }

    public function addLineItem(SalesInvoiceItem $item)
    {
        $this->lineItems[] = $item;
    }

    public function getSalesOrder(): SalesOrder
    {
        return $this->salesOrder;
    }

    private function setSalesOrder(SalesOrder $order)
    {
        $this->salesOrder = $order;
        $this->salesOrder->addInvoice($this);
        $this->setCustomer($order->getCustomer());
    }

    public function getCustomerTaxId()
    {
        return $this->salesOrder ? $this->salesOrder->getCustomerTaxId() :
            parent::getCustomerTaxId();
    }

    /** @return $this */
    public function setShipper(Shipper $shipper=null)
    {
        if (! $shipper ) {
            $order = $this->getSalesOrder();
            $shipper = $order->getShipper();
        }
        $this->shipper = $shipper;
        return $this;
    }

    public function getConsignment()
    {
        return $this->consignment;
    }

    public function setConsignment($cons)
    {
        $this->consignment = trim($cons);
        return $this;
    }
}
