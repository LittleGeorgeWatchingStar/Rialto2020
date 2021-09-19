<?php

namespace Rialto\Accounting\Debtor;

use Rialto\Accounting\Debtor\Credit\CustomerCredit;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\EventDispatcher\Event;

class CustomerCreditEvent extends Event
{
    /** @var CustomerCredit */
    private $credit;

    /**
     * Indicates whether this credit confirmed the quotation into a sales
     * order.
     *
     * @var boolean
     */
    private $confirmation = false;

    public function __construct(CustomerCredit $credit)
    {
        $this->credit = $credit;
    }

    /** @return CustomerCredit */
    public function getCredit()
    {
        return $this->credit;
    }

    /** @return SalesOrder|null */
    public function getSalesOrder()
    {
        return $this->credit->getSalesOrder();
    }

    public function hasSalesOrder()
    {
        return null !== $this->getSalesOrder();
    }

    /**
     * Indicates whether the customer should receive a confirmation email.
     * @return boolean
     */
    public function isSendEmail()
    {
        return $this->credit->isSendEmail();
    }

    public function isConfirmation()
    {
        return $this->confirmation && $this->hasSalesOrder();
    }

    public function setConfirmation($confirmation)
    {
        $this->confirmation = $confirmation;
    }
}
