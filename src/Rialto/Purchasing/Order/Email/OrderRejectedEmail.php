<?php

namespace Rialto\Purchasing\Order\Email;

use Rialto\Email\Email;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Security\User\User;


/**
 * Notifies the owner of a PO when that PO is rejected by the supplier.
 */
class OrderRejectedEmail extends Email
{
    public function __construct(PurchaseOrder $po, User $rejectedBy)
    {
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->addTo($po->getOwner());
        $this->subject = ucfirst(sprintf('%s was rejected by %s',
            $po, $po->getSupplier()));
        $this->template = 'purchasing/order/rejected-email.html.twig';
        $this->params = [
            'po' => $po,
            'rejectedBy' => $rejectedBy->getName(),
        ];
    }
}
