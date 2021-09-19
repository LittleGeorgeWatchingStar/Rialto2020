<?php

namespace Rialto\Security\Firewall;

use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Converts UsernameNotFoundExceptions into 403 responses.
 */
class UsernameNotFoundExceptionHandler implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(GetResponseForExceptionEvent $event)
    {
        $previous = $event->getException();
        if ($this->canHandle($previous)) {
            $exception = new AccessDeniedHttpException("Forbidden", $previous);
            $event->setException($exception);
        }
    }

    private function canHandle(Exception $ex)
    {
        return $ex instanceof UsernameNotFoundException;
    }
}
