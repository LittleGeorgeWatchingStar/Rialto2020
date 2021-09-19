<?php

namespace Rialto\Database\Orm;


use Doctrine\ORM\OptimisticLockException;
use Rialto\Entity\RialtoEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Templating\EngineInterface;

class LockExceptionHandler implements EventSubscriberInterface
{
    /** @var EngineInterface */
    private $templating;

    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(GetResponseForExceptionEvent $event)
    {
        $ex = $event->getException();
        if ($ex instanceof OptimisticLockException) {
            $this->handleLockException($event, $ex->getEntity());
        }
    }

    private function handleLockException(GetResponseForExceptionEvent $event, $entity)
    {
        $body = "This record was modified by someone else while you were working.";
        $request = $event->getRequest();
        if (!$request->isXmlHttpRequest()) {
            $body = $this->templating->render("TwigBundle:Exception:error409.html.twig", [
                'status_text' => 'Conflict',
                'status_code' => Response::HTTP_CONFLICT,
                'entity' => ($entity instanceof RialtoEntity) ? $entity : null,
            ]);
        }
        $event->setResponse(new Response($body, Response::HTTP_CONFLICT));
    }
}
