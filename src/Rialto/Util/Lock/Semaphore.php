<?php

namespace Rialto\Util\Lock;

/**
 * Interface for any type of concurrency locking mechanism.
 */
interface Semaphore
{
    /**
     * Attempts to acquire the lock.
     *
     * @return boolean
     *  True if the lock was acquired, false if someone else already has a lock.
     */
    public function acquire();

    /**
     * Releases the lock.
     */
    public function release();
}
