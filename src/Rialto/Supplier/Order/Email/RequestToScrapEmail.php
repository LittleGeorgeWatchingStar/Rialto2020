<?php

namespace Rialto\Supplier\Order\Email;

use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Security\User\User;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * An email message requesting to scrap a work order and discard the
 * unfinished quantity.
 */
class RequestToScrapEmail extends SubscriptionEmail
{
    const TOPIC = 'manufacturing.scrap_request';

    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }

    public function __construct(User $requestedBy, PurchaseOrder $po)
    {
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->addCc($requestedBy);
        $this->addTo($po->getOwner());
        $this->setSubject(sprintf("Request to scrap %s from %s",
            $po,
            $requestedBy->getName()
        ));
        $this->params = [
            'user' => $requestedBy,
            'po' => $po,
            'reason' => '',
        ];
        $this->template = 'supplier/email/requestToScrap.html.twig';
        $this->body = 'placeholder'; // TODO: see prepare() below
    }

    /**
     * @return string
     * @Assert\NotBlank(message="Reason cannot be blank.")
     */
    public function getReason()
    {
        return $this->params['reason'];
    }

    public function setReason($reason)
    {
        $this->params['reason'] = trim($reason);
    }

    /**
     * @todo generated vs hand-entered emails
     */
    public function prepare()
    {
        $this->body = null; // regenerate the body
    }
}
