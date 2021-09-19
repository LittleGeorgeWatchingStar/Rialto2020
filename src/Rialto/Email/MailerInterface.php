<?php

namespace Rialto\Email;

use Rialto\Email\Subscription\SubscriptionEmail;

interface MailerInterface
{
    public function send(Email $mail);

    public function loadSubscribers(SubscriptionEmail $email);
}
