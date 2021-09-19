<?php

namespace Rialto\Supplier\Order\Email;

use Rialto\Email\EmailListener;
use Rialto\Supplier\Order\AdditionalPart;
use Rialto\Supplier\SupplierEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Subscribes to events which require an email to be sent.
 */
class EmailSubscriber extends EmailListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            SupplierEvents::ADDITIONAL_PART => 'requestAdditionalPart',
        ];
    }

    public function requestAdditionalPart(AdditionalPart $part)
    {
        $email = new AdditionalPartEmail($this->getCurrentUser(), $part);
        $this->send($email);
    }

}
