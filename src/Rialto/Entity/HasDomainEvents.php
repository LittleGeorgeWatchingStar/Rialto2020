<?php

namespace Rialto\Entity;

/**
 * Indicates that the entity implements the Domain Events pattern.
 *
 * @see http://www.whitewashing.de/2013/07/24/doctrine_and_domainevents.html
 */
interface HasDomainEvents
{
    /** @return DomainEvent[] */
    public function popEvents();
}
