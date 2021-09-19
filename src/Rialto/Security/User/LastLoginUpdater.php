<?php

namespace Rialto\Security\User;


use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Updates the last login date of the current user.
 */
class LastLoginUpdater implements EventSubscriberInterface
{
    /** @var ObjectManager */
    private $om;

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'updateLoginDate',
        ];
    }

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function updateLoginDate(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $user = $token ? $token->getUser() : null;
        if ($user instanceof User) {
            $user->updateLastLoginDate();
            $this->om->flush();
        }
    }

}
