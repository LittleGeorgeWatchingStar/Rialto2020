<?php

namespace Rialto\Magento2\Order;

use Symfony\Component\EventDispatcher\Event;

/**
 * Dispatched when Magento detects that an order might be fraudulent.
 */
class SuspectedFraudEvent extends Event
{
    /**
     * @var Order
     */
    private $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getCustomerReference()
    {
        return $this->order->getCustomerReference();
    }
}
