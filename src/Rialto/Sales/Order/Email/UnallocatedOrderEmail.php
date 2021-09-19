<?php

namespace Rialto\Sales\Order\Email;


use Rialto\Email\Email;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Security\User\User;

class UnallocatedOrderEmail extends Email
{
    public function __construct(SalesOrder $order, User $sendTo)
    {
        $from = EmailPersonality::BobErbauer();
        $this->setFrom($from);
        $this->addTo($sendTo);
        $this->subject = "Unable to allocate to $order";
        $this->template = "sales/order/email/unallocated.html.twig";
        $this->params = [
            'to' => $sendTo,
            'from' => $from,
            'order' => $order,
        ];
    }
}
