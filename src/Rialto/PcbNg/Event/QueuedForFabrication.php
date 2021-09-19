<?php

namespace Rialto\PcbNg\Event;


use Symfony\Component\EventDispatcher\Event;

final class QueuedForFabrication extends Event
{

    /** @var array */
    private $payload;

    public static function fromPayload(array $payload): self
    {
        $event = new static();
        $event->payload = $payload;
        return $event;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
