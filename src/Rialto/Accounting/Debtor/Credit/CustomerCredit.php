<?php

namespace Rialto\Accounting\Debtor\Credit;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Base class for recording customer credits.
 */
abstract class CustomerCredit
{
    /** @var Customer */
    protected $customer;

    /** @var SalesOrder */
    protected $salesOrder;

    /** @var \DateTime */
    protected $date;

    /**
     * @var float
     * @Assert\Type(type="float")
     * @Assert\Range(min=0.01)
     */
    protected $amount;

    /**
     * @var string
     * @Assert\NotBlank
     */
    protected $memo;

    /** @var boolean */
    private $sendEmail = true;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
        $this->date = new \DateTime();
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /** @return SystemType */
    public abstract function getSystemType(DbManager $dbm);

    protected abstract function getMemoForBranch(CustomerBranch $branch);

    /** @return SalesOrder|null */
    public function getSalesOrder()
    {
        return $this->salesOrder;
    }

    public function setSalesOrder(SalesOrder $salesOrder = null)
    {
        $this->salesOrder = $salesOrder;
        if ( $salesOrder ) {
            assert($this->customer->equals($salesOrder->getCustomer()));
            $this->memo = $this->memo ?: $this->getMemoForOrder($salesOrder);
        }
    }

    protected abstract function getMemoForOrder(SalesOrder $salesOrder);

    public function getMemo()
    {
        return $this->memo;
    }

    public function setMemo($memo)
    {
        $this->memo = $memo;
    }

    public function getDate()
    {
        return clone $this->date;
    }

    public function setDate(\DateTime $date)
    {
        $this->date = clone $date;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getTotalAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function isSendEmail()
    {
        return $this->sendEmail;
    }

    public function setSendEmail($bool)
    {
        $this->sendEmail = $bool;
    }

    public abstract function createAdditionalTransactions(
        Transaction $trans,
        DbManager $dbm);

    /** @return GLAccount */
    public abstract function getToAccount();
}
