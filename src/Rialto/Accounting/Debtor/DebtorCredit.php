<?php

namespace Rialto\Accounting\Debtor;


use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Accounting\PaymentTransaction\CreditTransaction;
use Rialto\Accounting\PaymentTransaction\PaymentAllocation;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A debtor transaction which relieves the customer's debt to us.
 */
class DebtorCredit
extends DebtorTransaction
implements CreditTransaction
{
    /**
     * @var DebtorAllocation[]
     * @Assert\Valid(traverse=true)
     */
    private $allocations;

    /**
     * @var OrderAllocation[]
     * @Assert\Valid(traverse=true)
     */
    private $orderAllocations;

    public function __construct(Transaction $glTrans, Customer $customer)
    {
        parent::__construct($glTrans);
        $this->setCustomer($customer);
        $this->allocations = new ArrayCollection();
        $this->orderAllocations = new ArrayCollection();
    }

    protected function getAllowedTypes(): array
    {
        return [
            SystemType::CREDIT_NOTE,
            SystemType::RECEIPT,
        ];
    }

    /**
     * @return DebtorAllocation[]
     */
    public function getAllocations(): array
    {
        return $this->allocations->toArray();
    }

    /** @param $alloc DebtorAllocation */
    public function addAllocation(PaymentAllocation $alloc)
    {
        // check to avoid infinite recursion
        if (! $this->allocations->contains($alloc) ) {
            $this->allocations[] = $alloc;
            $this->updateAmountAllocated();

            $invoice = $alloc->getInvoice();
            $invoice->addAllocation($alloc);
        }
    }

    /** @param $alloc DebtorAllocation */
    public function removeAllocation(PaymentAllocation $alloc)
    {
        // check to avoid infinite recursion
        if ( $this->allocations->contains($alloc) ) {
            $this->allocations->removeElement($alloc);
            $this->updateAmountAllocated();

            $invoice = $alloc->getInvoice();
            $invoice->removeAllocation($alloc);
        }
    }

    /**
     * @return OrderAllocation[]
     */
    public function getOrderAllocations()
    {
        return $this->orderAllocations->toArray();
    }

    public function addOrderAllocation(OrderAllocation $alloc)
    {
        $this->orderAllocations[] = $alloc;
        $order = $alloc->getSalesOrder();
        $order->addCreditAllocation($alloc);
    }

    public function removeOrderAllocation(OrderAllocation $alloc)
    {
        $this->orderAllocations->removeElement($alloc);
        $order = $alloc->getSalesOrder();
        $order->removeCreditAllocation($alloc);
    }

    /** @return OrderAllocation */
    public function allocateToOrder(SalesOrder $order)
    {
        $alloc = new OrderAllocation($this, $order);
        $amount = $this->calculateAmountToAllocateToOrder($order);
        $alloc->setAmount($amount);
        $this->addOrderAllocation($alloc);
        return $alloc;
    }

    private function calculateAmountToAllocateToOrder(SalesOrder $order)
    {
        $myAmount = -$this->getTotalAmount();
        assert($myAmount >= 0);
        $orderAmt = $order->getTotalAmountOutstanding();
        return min($myAmount, $orderAmt);
    }

    /** @Assert\Callback(groups={"orderAllocation"}) */
    public function validateAmountAllocatedToOrders(ExecutionContextInterface $context)
    {
        $allocated = $this->getTotalAmountAllocatedToOrders();
        if ( $allocated > -$this->getTotalAmount() ) {
            $context->addViolation("Cannot allocate more than the total value of $this.");
        }
    }

    private function getTotalAmountAllocatedToOrders()
    {
        $total = 0;
        foreach ( $this->orderAllocations as $orderAlloc ) {
            $total += $orderAlloc->getAmount();
        }
        return $total;
    }
}
