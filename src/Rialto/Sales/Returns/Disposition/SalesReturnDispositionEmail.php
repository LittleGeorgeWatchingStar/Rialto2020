<?php

namespace Rialto\Sales\Returns\Disposition;

use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Sales\Returns\SalesReturn;

/**
 * Notify the person who authorized a sales return that it has been tested.
 */
class SalesReturnDispositionEmail extends SubscriptionEmail
{
    const TOPIC = 'sales.return_disposition';

    public function __construct(SalesReturn $rma)
    {
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->addTo($rma->getAuthorizedBy());
        $subject = "$rma tested";
        $this->setSubject($subject);

        $this->template = 'sales/return/disposition-email.html.twig';
        $this->params = [
            'rma' => $rma,
        ];
    }

    /**
     * @return string
     */
    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }
}
