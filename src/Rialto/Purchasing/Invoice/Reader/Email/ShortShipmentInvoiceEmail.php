<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;

use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Security\User\User;


/**
 * An email message requesting to scrap a work order and discard the
 * unfinished quantity.
 */
class ShortShipmentInvoiceEmail extends SubscriptionEmail
{
    const TOPIC = 'purchasing.short_shipment_invoice';

    public function __construct(User $notifier, SupplierInvoice $supplierInvoice)
    {
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->addCc($notifier);
        $this->addTo($notifier);
        $this->setSubject(sprintf("Please have a look new order %s from %s",
            $supplierInvoice,
            $notifier->getName()
        ));
        $this->params = [
            'user' => $notifier,
            'si' => $supplierInvoice
        ];
        $this->template = 'supplier/email/newOrder.html.twig';
    }

    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }
}
