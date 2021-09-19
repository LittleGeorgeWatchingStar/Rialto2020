<?php

namespace Rialto\Util\Lock;

/**
 * A semaphore implementation that makes multiple attempts to acquire a lock,
 * sleeping between failed attempts.
 */
class BlockingSemaphore implements Semaphore
{
    /** @var FileSemaphore */
    private $lock;

    private $maxAttempts;

    /**
     * Minimum time to sleep between attempts, in milliseconds.
     * @var int
     */
    private $minSleepTime = 100;

    /**
     * Maximum time to sleep between attempts, in milliseconds.
     * @var int
     */
    private $maxSleepTime = 500;

    /**
     * @param Semaphore $lock The semaphore to use internally
     * @param int $maxAttempts Fail after this many attempts to acquire the lock
     */
    public function __construct(Semaphore $lock, $maxAttempts = 5)
    {
        if ( $maxAttempts < 1 ) throw new \InvalidArgumentException(
            "maxAttempts must be at least 1");
        $this->lock = $lock;
        $this->maxAttempts = $maxAttempts;
    }

    public function setMinSleepTime($minSleepTime)
    {
        if ( $minSleepTime < 1 ) {
            throw new \InvalidArgumentException("sleep time must be at least 1 millisecond");
        }
        if ( $minSleepTime > $this->maxSleepTime ) {
            throw new \InvalidArgumentException("Min sleep cannot be greater than max");
        }
        $this->minSleepTime = $minSleepTime;
    }

    public function setMaxSleepTime($maxSleepTime)
    {
        if ( $maxSleepTime < 1 ) {
            throw new \InvalidArgumentException("sleep time must be at least 1 millisecond");
        }
        if ( $maxSleepTime < $this->minSleepTime ) {
            throw new \InvalidArgumentException("Max sleep cannot be less than min");
        }
        $this->maxSleepTime = $maxSleepTime;
    }


    public function acquire()
    {
        foreach ( range(0, $this->maxAttempts) as $i) {
            if ( $this->lock->acquire() ) return true;
            $this->sleep();
        }
        return false;
    }

    private function sleep()
    {
        $min = $this->minSleepTime * 1000;
        $max = $this->maxSleepTime * 1000;
        usleep(rand($min, $max));
    }

    public function release()
    {
        $this->lock->release();
    }

}
