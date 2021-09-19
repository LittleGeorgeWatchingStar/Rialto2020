<?php


namespace Rialto\Port\CommandBus;


/**
 * A CommandBus is a service that is capable of taking a Command object and
 * routing it to the appropriate handler that will execute the changes requested
 * by the Command.
 */
interface CommandBus
{
    /**
     * @return mixed
     */
    public function handle(Command $command);
}