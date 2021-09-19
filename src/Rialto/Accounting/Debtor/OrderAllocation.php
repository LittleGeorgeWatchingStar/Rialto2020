<?php

namespace Rialto\Accounting\Debtor;


use DateTime;
use Rialto\Entity\RialtoEntity;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Allocates debtor credits to sales orders.
 *
 * This allows us to track what a customer has paid for before those orders
 * are invoiced.
 */
class OrderAllocation implements RialtoEntity
{
    /** @var  int */
    private $id;

    /** @var DebtorCredit */
    private $credit;

    /**
     * @var  SalesOrder
     * @Assert\Valid
     */
    private $salesOrder;

    /** @var DateTime */
    private $dateUpdated;

    /**
     * @var float
     * @Assert\GreaterThan(value=0,
     *   message="Amount must be positive.",
     *   groups={"Default", "orderAllocation"})
     */
    private $amount;

    public function __construct(DebtorCredit $credit, SalesOrder $order)
    {
        $this->credit = $credit;
        $this->salesOrder = $order;
        $this->dateUpdated = new DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DebtorCredit
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @return SalesOrder
     */
    public function getSalesOrder()
    {
        return $this->salesOrder;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        $this->dateUpdated = new DateTime();
    }

    /**
     * @return DateTime
     */
    public function getCreditDate()
    {
        return $this->credit->getDate();
    }

    /** @Assert\Callback(groups={"Default", "orderAllocation"}) */
    public function validateCustomersMatch(ExecutionContextInterface $context)
    {
        $creditCust = $this->credit->getCustomer();
        $orderCust = $this->salesOrder->getCustomer();
        if (! $creditCust->equals($orderCust) ) {
            $credit = $this->credit;
            $order = $this->salesOrder;
            $msg = "Customer $creditCust for $credit does not match customer $orderCust for $order.";
            $context->addViolation($msg);
        }
    }
}
