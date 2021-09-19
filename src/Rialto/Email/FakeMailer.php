<?php

namespace Rialto\Email;

use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Security\User\User;

class FakeMailer implements MailerInterface
{
    /**
     * @var Email[]
     */
    public $sent = [];

    /**
     * @var User[]
     */
    private $subscribers = [];

    public function send(Email $mail)
    {
        $this->sent[] = $mail;
    }

    public function addSubscriber(User $user)
    {
        assertion($user->getEmail() != '');
        $this->subscribers[] = $user;
    }

    public function loadSubscribers(SubscriptionEmail $email)
    {
        $email->setSubscribers($this->subscribers);
    }

}
