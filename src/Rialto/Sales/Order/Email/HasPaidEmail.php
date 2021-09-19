<?php

namespace Rialto\Sales\Order\Email;

use Rialto\Email\Mailable\Mailable;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Sales\Order\SalesOrder;

/**
 * Notifies the relevant parties that a custom has paid
 * but the PCB does not have a build file
 */
class HasPaidEmail extends SubscriptionEmail
{
    const TOPIC = 'notifyWhenCustomerHasPaid';

    /** @var SalesOrder */
    private $salesOrder;

    public function __construct(SalesOrder $salesOrder, Mailable $sender)
    {
        $this->salesOrder = $salesOrder;
        $salesOrderDetails = $salesOrder->getLineItems();
        $this->subject = ucfirst("{$this->salesOrder} is paid and ready to have build files");
        $this->template = 'sales/customer/hasPaidAndNew.html.twig';
        $this->params = [
            'so' => $this->salesOrder,
            'soDetails'=>$salesOrderDetails,
            'sender' => $sender,
        ];
    }

    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }
}
