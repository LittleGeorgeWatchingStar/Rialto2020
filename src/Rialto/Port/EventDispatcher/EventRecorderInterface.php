<?php


namespace Rialto\Port\EventDispatcher;


use Symfony\Component\EventDispatcher\Event;

/**
 * An EventRecorder allows a consumer to record an event, but defer the dispatch
 * of said event to a later time (e.g. we want to create events during the
 * handling of a request, but only dispatch them when database transactions
 * have been committed).
 */
interface EventRecorderInterface
{
    // TODO: Get rid of explicit dependency on Symfony\Component\EventDispatcher\Event.

    public function recordEvent(string $name, Event $event): void;

    public function recordedEvents(): array;

    public function eraseEvents(): void;
}
