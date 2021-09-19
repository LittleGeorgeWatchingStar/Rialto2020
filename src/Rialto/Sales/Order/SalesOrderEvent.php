<?php

namespace Rialto\Sales\Order;

use Rialto\Alert\HasWarnings;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is fired when something happens to a sales order.
 */
class SalesOrderEvent extends Event
{
    use HasWarnings;

    private $order;
    private $sendEmail;

    public function __construct(SalesOrder $order)
    {
        $this->order = $order;
        $this->sendEmail = (bool) $order->getEmail();
    }

    /**
     * @return SalesOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Call to disable email notification to the customer.
     */
    public function disableEmail()
    {
        $this->sendEmail = false;
    }

    /** @return bool */
    public function isEmailEnabled()
    {
        return $this->sendEmail;
    }
}
