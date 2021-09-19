<?php

namespace Rialto\Supplier\Order\Email;


use Rialto\Email\Email;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Security\User\User;
use Rialto\Supplier\Order\AdditionalPart;

/**
 * Notify the PO owner when the manufacturer requests additional parts.
 */
class AdditionalPartEmail extends Email
{
    public function __construct(User $requester, AdditionalPart $part)
    {
        $workOrder = $part->getWorkOrder();
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->addTo($workOrder->getOwner());
        $this->setSubject(sprintf('%s requested additional %s for %s',
            $requester->getName(), $part, $workOrder));
        $this->template = 'supplier/email/additionalPart.html.twig';
        $this->params = [
            'requester' => $requester,
            'workOrder' => $workOrder,
            'part' => $part,
        ];
    }
}
