<?php


namespace Infrastructure\EventDispatcher;

use Rialto\Port\EventDispatcher\EventRecorderInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * An EventRecorder that stores recorded events in an array.
 */
final class SimpleEventRecorder implements EventRecorderInterface
{
    private $events = [];

    public function recordEvent(string $name, Event $event): void
    {
        $this->events[] = [$name, $event];
    }

    public function recordedEvents(): array
    {
        return $this->events;
    }

    public function eraseEvents(): void
    {
        $this->events = [];
    }
}
