<?php

namespace Rialto\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use SplObjectStorage as Set;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Listens for changes to entities that have domain events and dispatches
 * their events.
 *
 * @see http://www.whitewashing.de/2013/07/24/doctrine_and_domainevents.html
 * @see HasDomainEvents
 */
class DomainEventHandler
{
    /** @var HasDomainEvents[] */
    private $entities;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->entities = new Set();
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $this->keepDomainEvents($event);
    }

    public function postUpdate(LifecycleEventArgs $event)
    {
        $this->keepDomainEvents($event);
    }

    public function postRemove(LifecycleEventArgs $event)
    {
        $this->keepDomainEvents($event);
    }

    private function keepDomainEvents(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof HasDomainEvents) {
            $this->entities->attach($entity);
        }
    }

    public function postFlush(PostFlushEventArgs $event)
    {
        foreach ($this->entities as $entity) {
            foreach ($entity->popEvents() as $event) {
                $this->dispatchEvent($event);
            }
        }
        $this->entities = new Set();
    }

    private function dispatchEvent(DomainEvent $event)
    {
        $eventName = $event->getEventName();
        if (! $eventName ) {
            throw new \LogicException("Domain events must have an event name set");
        }
        $this->dispatcher->dispatch($eventName, $event);
    }
}
