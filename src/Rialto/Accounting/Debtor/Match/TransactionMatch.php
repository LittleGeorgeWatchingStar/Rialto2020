<?php

namespace Rialto\Accounting\Debtor\Match;

use Rialto\Accounting\Debtor\Credit\CustomerTransferFee;
use Rialto\Accounting\Debtor\DebtorCredit;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A many-to-many match between debtor credits and invoices.
 */
class TransactionMatch
{
    private $selected = false;

    /**
     * @var DebtorInvoice[]
     */
    protected $invoices = [];

    /**
     * @var DebtorCredit[]
     */
    protected $credits = [];

    private $customer;
    private $order;

    /**
     * @Assert\Type(type="double", message="Fee amount must be numeric.")
     * @Assert\Range(min=0, minMessage="Fee amount must not be negative.")
     * @var double
     */
    private $feeAmount = 0.0;

    public function __construct(Customer $cust, SalesOrder $order = null)
    {
        $this->customer = $cust;
        $this->order = $order;
    }

    public function addTransaction(DebtorTransaction $transaction)
    {
        if ($transaction->isCredit()) {
            $this->credits[] = $transaction;
        } elseif ($transaction->isInvoice()) {
            $this->invoices[] = $transaction;
        } else {
            throw new \UnexpectedValueException(sprintf(
                'Payment transaction %s is of an unknown type',
                $transaction->getId()
            ));
        }

        $this->selected = $this->isBalanced();
    }

    public function getIndexKey()
    {
        $reducer = function ($string, DebtorTransaction $trans) {
            return $string . '_' . $trans->getId();
        };
        $string = array_reduce($this->credits, $reducer, '');
        $string = array_reduce($this->invoices, $reducer, $string);
        return sha1($string);
    }

    public function isSelected()
    {
        return $this->selected;
    }

    public function setSelected($selected)
    {
        $this->selected = $selected;
    }

    public function getInvoices()
    {
        return $this->invoices;
    }

    public function getCredits()
    {
        return $this->credits;
    }

    public function isBalanced()
    {
        return 0 == $this->round($this->getDifference());
    }

    public function getDifference()
    {
        $function = function ($total, DebtorTransaction $trans) {
            return $total + $trans->getAmountUnallocated();
        };
        $total = array_reduce($this->invoices, $function, 0);
        return array_reduce($this->credits, $function, $total);
    }

    public function createAllocations(): float
    {
        $totalAllocated = 0.0;
        foreach ($this->invoices as $invoice) {
            $remaining = $invoice->getAmountUnallocated();
            if ($remaining > 0) {
                foreach ($this->credits as $credit) {
                    $toAllocate = min($remaining, -$credit->getAmountUnallocated());
                    if ($toAllocate > 0) {
                        $invoice->allocateFrom($credit, $toAllocate);
                        $remaining -= $toAllocate;
                        $totalAllocated += $toAllocate;
                    }
                    if ($remaining <= 0) break;
                }
            }
        }
        return $totalAllocated;
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    public function getSalesOrder()
    {
        return $this->order;
    }

    protected function round($amount)
    {
        return round($amount, DebtorTransaction::MONEY_PRECISION);
    }

    public function getFeeAmount()
    {
        return $this->feeAmount;
    }

    public function setFeeAmount($feeAmount)
    {
        $this->feeAmount = $feeAmount;
    }

    public function isSubjectToTransferFee()
    {
        if (!$this->order) return false;

        return $this->order->isDirectSale() &&
            $this->isAllocable();
    }

    public function isAllocable()
    {
        return count($this->invoices) > 0 &&
            count($this->credits) > 0;
    }

    public function createTransferFee(): CustomerTransferFee
    {
        $transferFee = new CustomerTransferFee($this->order);
        $transferFee->setDate($this->getDateOfLastPayment());
        $transferFee->setAmount($this->feeAmount);

        return $transferFee;
    }

    private function getDateOfLastPayment()
    {
        $mostRecent = array_reduce(
            $this->credits,
            function ($newest, DebtorTransaction $current) {
                if (!$newest) return $current;
                elseif ($current->getDate() > $newest->getDate()) {
                    return $current;
                } else return $newest;
            }, null);
        return clone $mostRecent->getDate();
    }
}
