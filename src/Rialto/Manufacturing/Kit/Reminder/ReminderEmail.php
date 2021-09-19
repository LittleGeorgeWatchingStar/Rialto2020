<?php

namespace Rialto\Manufacturing\Kit\Reminder;


use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Security\User\User;
use Rialto\Stock\Transfer\Transfer;

/**
 * Reminds the CM to check in a transfer that has not been received in a
 * timely manner.
 */
class ReminderEmail extends SubscriptionEmail
{
    const TOPIC = 'transfer.reminder';

    public function __construct(Transfer $transfer)
    {
        $this->setSubject("Please check in $transfer");
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->setTo($transfer->getSupplierContacts());
        $this->setTemplate('manufacturing/kit/reminder-email.html.twig');
        $this->params = [
            'transfer' => $transfer,
            'supplier' => $transfer->getSupplier(),
            'delay' => str_replace('+', '', EmailScheduler::DELAY),
        ];
    }

    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }

    protected function addSubscriber(User $user)
    {
        $this->addCc($user);
    }
}
