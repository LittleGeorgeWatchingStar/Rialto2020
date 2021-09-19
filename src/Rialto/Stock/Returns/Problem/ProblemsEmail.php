<?php

namespace Rialto\Stock\Returns\Problem;

use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Returns\ReturnedItem;

/**
 * Sent when the warehouse receives returned bins that have some kind of
 * problem.
 *
 * @see ReturnedItem
 */
class ProblemsEmail
extends SubscriptionEmail
{
    const TOPIC = 'receiving.problems';

    /**
     * @param ReturnedItem[] $params
     */
    public function __construct(User $user, Facility $location, array $items)
    {
        $this->template = "stock/returns/problemsEmail.html.twig";
        $this->params = [
            'user' => $user,
            'location' => $location,
            'items' => $items,
        ];
        $this->setFrom(EmailPersonality::BobErbauer());
        $this->subject = "Problems with items returned from $location";
    }

    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }
}
