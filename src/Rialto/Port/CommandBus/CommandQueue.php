<?php

namespace Rialto\Port\CommandBus;


use JMS\JobQueueBundle\Entity\Job;

/**
 * A CommandQueue is a service that is capable of taking a Command object and
 * sending it to a queue to be handled at a later time.
 */
interface CommandQueue
{
    /**
     * @param bool $flush If true will flush the queued command to the transport.
     * Set this to false if you intend to queue a large number of commands at once.
     * @return int|null The ID of the command in the queue, will return null if
     * $flush is set to false.
     */
    public function queue(Command $command, bool $flush = true): ?int;

    // TODO: Wrap the Job into a generic, non-JMS version.
    public function findRecentJobForCommand(Command $command): ?Job;
}

