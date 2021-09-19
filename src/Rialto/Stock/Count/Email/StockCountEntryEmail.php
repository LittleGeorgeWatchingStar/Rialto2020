<?php

namespace Rialto\Stock\Count\Email;

use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Security\User\User;
use Rialto\Stock\Count\StockCount;

/**
 * This email is sent to the admin when the location manager has entered
 * stock counts.
 */
class StockCountEntryEmail extends SubscriptionEmail
{
    public function __construct(StockCount $count, User $enteredBy, User $to = null)
    {
        if (! $to) {
            $to = $count->getRequestedBy();
        }
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->addTo($to);
        $this->subject = sprintf('%s has entered stock counts for %s',
            $enteredBy->getName(), $count->getLocation());
        $this->template = 'stock/count/entry-email.html.twig';
        $this->params = [
            'count' => $count,
            'enteredBy' => $enteredBy,
            'recipient' => $to,
        ];
    }

    protected function getSubscriptionTopic()
    {
        return 'stock.stock_count';
    }

    /**
     * Subscribers get CCed; the person who requested the count is
     * the main recipient.
     */
    protected function addSubscriber(User $user)
    {
        $this->addCc($user);
    }

}
