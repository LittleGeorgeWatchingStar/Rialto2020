<?php

namespace Rialto\Entity;

/**
 * An entity that uses Doctrine's optimistic locking capability for
 * concurrency-safety.
 */
interface LockingEntity
{
    public function getEditNo();
}
