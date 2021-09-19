<?php

namespace Rialto\Email\Subscription;

use Rialto\Entity\RialtoEntity;
use Rialto\Security\User\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Records a user's subscription to an email topic.
 *
 * @UniqueEntity(fields={"user", "topic"},
 *     message="The user is already subscribed to that topic.")
 */
class UserSubscription implements RialtoEntity
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $topic;

    /**
     * @param self[] $subscriptions
     * @return self[]
     */
    public static function indexByTopic(array $subscriptions)
    {
        $index = [];
        foreach ($subscriptions as $s) {
            $index[$s->getTopic()] = $s;
        }
        return $index;
    }

    public function __construct(User $user, $topic)
    {
        $this->user = $user;
        $this->topic = trim($topic);
    }

    public function getTopic()
    {
        return $this->topic;
    }
}
