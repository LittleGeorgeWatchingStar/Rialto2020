<?php

namespace Rialto\Security\Nda;

use Rialto\Security\Role\Role;
use Rialto\Security\User\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;


/**
 * Asks users outside of the company to accept the NDA terms for using
 * this system.
 *
 * @see http://symfony.com/doc/current/book/internals.html#kernel-request-event
 */
class NdaFormListener implements EventSubscriberInterface
{
    /** @var UserManager */
    private $userManager;

    /** @var RouterInterface */
    private $router;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function __construct(
        UserManager $userManager,
        RouterInterface $router)
    {
        $this->userManager = $userManager;
        $this->router = $router;
    }


    public function onKernelRequest(GetResponseEvent $event)
    {
        $response = $this->checkNonDisclosureAgreement($event);
        if ($response) {
            $event->setResponse($response);
        }
    }

    /**
     * Checks to see that the user has accepted the non-disclosure agreement
     * (if required to do so).
     *
     * @param GetResponseEvent $event
     * @return Response|null
     *  Null if the user does not need to sign the NDA.
     */
    private function checkNonDisclosureAgreement(GetResponseEvent $event)
    {
        /* If the user is not logged in yet, they need to log in first. */
        $user = $this->userManager->getUserOrNull();
        if (!$user) {
            return null;
        }

        /* Employees and authorized client applications don't need to sign. */
        if ($this->userManager->isGranted(Role::EMPLOYEE)) {
            return null;
        }
        if ($this->userManager->isGranted(Role::API_CLIENT)) {
            return null;
        }

        $request = $event->getRequest();
        if ($request->attributes->get('_route') == 'nda_form') {
            /* The user is already viewing the NDA form. */
            return null;
        }

        $session = $request->getSession();
        if ($session && $session->get('nda_accepted')) {
            return null;
        }

        return new RedirectResponse($this->router->generate('nda_form', [
            'next' => $request->getUri(),
        ]));
    }
}
