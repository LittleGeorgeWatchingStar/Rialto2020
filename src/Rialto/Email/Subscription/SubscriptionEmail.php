<?php

namespace Rialto\Email\Subscription;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Email\Email;
use Rialto\Security\User\Orm\UserRepository;
use Rialto\Security\User\User;

/**
 * A subscription email is one that is sent (or CC'd) to a list of users
 * who have subscribed to it.
 *
 * In some cases, the primary recipients are determined by the program logic
 * and the subscribers receive a CC.
 */
abstract class SubscriptionEmail extends Email
{
    public function loadSubscribers(ObjectManager $om)
    {
        /** @var UserRepository $repo */
        $repo = $om->getRepository(User::class);
        $this->loadSubscribersFromRepo($repo);
    }

    public function loadSubscribersFromRepo(UserRepository $repo)
    {
        $users = $repo->findBySubscriptionTopic($this->getSubscriptionTopic());
        $this->setSubscribers($users);
    }

    /**
     * @param User[] $subscribers
     */
    public function setSubscribers(array $subscribers)
    {
        foreach ($subscribers as $user) {
            assertion('' != $user->getEmail());
            $this->addSubscriber($user);
        }
    }

    /**
     * @return string
     */
    protected abstract function getSubscriptionTopic();

    /**
     * Override this method to CC or BCC subscribers instead of adding them
     * to the "To:" field.
     */
    protected function addSubscriber(User $user)
    {
        $this->addTo($user);
    }
}
