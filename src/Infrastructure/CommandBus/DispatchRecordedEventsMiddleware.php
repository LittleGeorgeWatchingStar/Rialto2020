<?php


namespace Infrastructure\CommandBus;


use Exception;
use Rialto\Port\EventDispatcher\EventRecorderInterface;
use League\Tactician\Middleware;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Tactician middleware to dispatch any events that were recorded during the
 * handling of an event, removing the need for the handler to directly dispatch
 * the event.
 *
 * This behaviour is desirable because we typically want events to be dispatched
 * after the database is finished committing any transactions and also want to
 * delegate transaction handling to middleware instead of inside the command
 * handler itself.
 */
final class DispatchRecordedEventsMiddleware implements Middleware
{
    /** @var EventRecorderInterface */
    private $eventRecorder;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(EventRecorderInterface $eventRecorder,
                                EventDispatcherInterface $eventDispatcher)
    {
        $this->eventRecorder = $eventRecorder;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function execute($command, callable $next)
    {
        try {
            $returnValue = $next($command);
        } catch (Exception $exception) {
            throw $exception;
        }

        $events = $this->eventRecorder->recordedEvents();
        $this->eventRecorder->eraseEvents();

        foreach ($events as [$name, $event]) {
            $this->eventDispatcher->dispatch($name, $event);
        }

        return $returnValue;
    }
}