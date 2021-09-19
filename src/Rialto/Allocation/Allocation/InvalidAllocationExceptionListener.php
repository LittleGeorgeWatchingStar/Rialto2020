<?php

namespace Rialto\Allocation\Allocation;

use Rialto\Alert\BasicAlertMessage;
use Rialto\Database\Orm\DbManager;
use Rialto\Security\Role\Role;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Error\RuntimeError;

/**
 * Handles InvalidAllocationExceptions, which otherwise tend to crop
 * up unpredictably and crash the system.
 *
 * @see InvalidAllocationException
 */
class InvalidAllocationExceptionListener
{
    /** @var DbManager */
    private $dbm;

    /** @var RouterInterface */
    private $router;

    /** @var AuthorizationCheckerInterface */
    private $auth;

    /** @var Session */
    private $session = null;

    public function __construct(
        DbManager $dbm,
        RouterInterface $router,
        AuthorizationCheckerInterface $auth )
    {
        $this->dbm = $dbm;
        $this->router = $router;
        $this->auth = $auth;
    }

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (! $this->canHandle($event) ) return;

        $response = $this->handleInvalidAllocation($event);
        $event->setResponse($response);
    }

    private function canHandle(GetResponseForExceptionEvent $event)
    {
        return (bool) $this->getInvalidAllocationException($event->getException());
    }

    /** @return InvalidAllocationException|null */
    private function getInvalidAllocationException(\Exception $ex = null)
    {
        if (! $ex ) {
            return null;
        }
        if ( $ex instanceof InvalidAllocationException ) {
            return $ex;
        }
        if ( $ex instanceof RuntimeError ) {
            return $this->getInvalidAllocationException($ex->getPrevious());
        }
        return null;
    }

    private function handleInvalidAllocation(GetResponseForExceptionEvent $event)
    {
        $event->stopPropagation();
        if ( $this->auth->isGranted(Role::STOCK) ) {
            return $this->redirectToAllocationList($event);
        }
        else {
            return $this->autoDeleteAllocation($event);
        }
    }

    private function redirectToAllocationList(GetResponseForExceptionEvent $event)
    {
        $this->logAlert("An invalid allocation was detected.");
        $exception = $this->getInvalidAllocationException($event->getException());
        $alloc = $exception->getAllocation();
        $request = $event->getRequest();
        $uri = $this->router->generate('allocation_list', [
            'stockItem' => $alloc->getSku(),
            'returnTo' => $request->getUri(),
        ]);
        return new RedirectResponse($uri);
    }

    private function logAlert($message)
    {
        if (! $this->session ) {
            return;
        }
        $message = BasicAlertMessage::createError($message);
        $this->session->getFlashBag()->add('error', $message);
    }

    private function autoDeleteAllocation(GetResponseForExceptionEvent $event)
    {
        $exception = $this->getInvalidAllocationException($event->getException());
        $alloc = $exception->getAllocation();
        assert($alloc instanceof StockAllocation);
        $alloc->close();
        $this->dbm->flush();

        $request = $event->getRequest();
        $uri = $request->getUri();
        return new RedirectResponse($uri);
    }
}
