<?php

namespace Rialto\Cms;

use Rialto\Security\Role\Role;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Error\RuntimeError;

/**
 * Handles CmsExceptions.
 *
 * @see CmsException
 */
class ExceptionHandler
{
    /** @var AuthorizationCheckerInterface */
    private $auth;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerInterface $auth,
        RouterInterface $router)
    {
        $this->auth = $auth;
        $this->router = $router;
    }

    public function onException(GetResponseForExceptionEvent $event)
    {
        $ex = $event->getException();
        $this->handleException($event, $ex);
    }

    private function handleException(
        GetResponseForExceptionEvent $event,
        \Exception $ex = null)
    {
        if ($ex instanceof CmsException) {
            $this->handleCmsException($event, $ex);
        } elseif ($ex instanceof RuntimeError) {
            /* Recurse using the previous exception */
            $this->handleException($event, $ex->getPrevious());
        }
    }

    private function handleCmsException(
        GetResponseForExceptionEvent $event,
        CmsException $ex)
    {
        $response = new Response();
        $body = "<p>" . $ex->getMessage() . "</p>";

        if ($this->auth->isGranted(Role::ADMIN)) {
            $uri = $this->router->generate('cms_entry_list');
            $body .= "<a href=\"$uri\">Click here to fix the problem.</a>";
        }

        $response->setContent($body);
        $event->setResponse($response);
    }

}
