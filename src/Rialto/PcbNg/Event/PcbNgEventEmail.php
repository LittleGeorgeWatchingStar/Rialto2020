<?php

namespace Rialto\PcbNg\Event;


use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\Subscription\SubscriptionEmail;

final class PcbNgEventEmail extends SubscriptionEmail
{
    const TOPIC = 'pcbng.event';

    public function __construct(array $payload)
    {
        $this->template = "pcbng/event/pcbngEventEmail.html.twig";
        $this->params = [
            'payload' => $payload,
        ];
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->subject = "PCB:NG Notification";
    }

    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }

}
