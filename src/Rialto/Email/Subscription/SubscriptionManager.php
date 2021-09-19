<?php

namespace Rialto\Email\Subscription;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Security\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class SubscriptionManager
{
    /** @var ObjectManager */
    private $om;

    /** @var User */
    private $user;

    /**
     * @var UserSubscription[]
     *
     * @Assert\Valid(traverse=true)
     */
    private $subscriptions;

    public function __construct(User $user, ObjectManager $om)
    {
        $this->user = $user;
        $this->om = $om;
        $this->subscriptions = UserSubscription::indexByTopic($this->loadSubscriptions());
    }

    private function loadSubscriptions()
    {
        $repo = $this->om->getRepository(UserSubscription::class);
        return $repo->findBy(['user' => $this->user]);
    }

    /**
     * @return string[]
     */
    public function getTopics()
    {
        return array_keys($this->subscriptions);
    }

    public function addTopic($topic)
    {
        if (! isset($this->subscriptions[$topic])) {
            $sub = new UserSubscription($this->user, $topic);
            $this->subscriptions[$topic] = $sub;
            $this->om->persist($sub);
        }
    }

    public function removeTopic($topic)
    {
        if (isset($this->subscriptions[$topic])) {
            $sub = $this->subscriptions[$topic];
            unset($this->subscriptions[$topic]);
            $this->om->remove($sub);
        }
    }
}
