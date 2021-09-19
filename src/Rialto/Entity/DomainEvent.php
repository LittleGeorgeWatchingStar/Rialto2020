<?php

namespace Rialto\Entity;

/**
 * An event dispatched as a result of a change to an entity.
 *
 * @see http://www.whitewashing.de/2013/07/24/doctrine_and_domainevents.html
 * @see HasDomainEvents
 */
interface DomainEvent
{
    /**
     * @return string The event name to dispatch when this event is handled.
     */
    public function getEventName();
}
