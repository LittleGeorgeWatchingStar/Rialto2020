<?php

namespace Rialto\PcbNg;


use Rialto\PcbNg\Event\Cancelled;
use Rialto\PcbNg\Event\Error;
use Rialto\PcbNg\Event\InFabrication;
use Rialto\PcbNg\Event\InManufacturing;
use Rialto\PcbNg\Event\OnHold;
use Rialto\PcbNg\Event\PendingReview;
use Rialto\PcbNg\Event\QueuedForFabrication;
use Rialto\PcbNg\Event\QueuedForManufacturing;
use Rialto\PcbNg\Event\Refunded;
use Rialto\PcbNg\Event\Shipped;
use Symfony\Component\EventDispatcher\Event;

/**
 * Transform a notification payload from PCB:NG into an appropriate event
 */
final class PcbNgOrderNotificationAdapter
{
    const PENDING_REVIEW = 'pending-review';
    const ON_HOLD = 'on-hold';
    const CANCELLED = 'cancelled';
    const QUEUED_FOR_FABRICATION = 'queued-for-fabrication';
    const QUEUED_FOR_MANUFACTURING = 'queued-for-manufacturing';
    const IN_FABRICATION = 'in-fabrication';
    const IN_MANUFACTURING = 'in-manufacturing';
    const SHIPPED = 'shipped';
    const REFUNDED = 'refunded';
    const ERROR = 'error';

    public static function toEvent(array $payload): Event
    {
        // Relevant ID is `userboard_id` which corresponds to the `quotation no` for purchasing data.
        $actionType = $payload['action_type'] ?? null;
        switch ($actionType) {
            case self::PENDING_REVIEW:
                return PendingReview::fromPayload($payload);
            case self::ON_HOLD:
                return OnHold::fromPayload($payload);
            case self::CANCELLED:
                return Cancelled::fromPayload($payload);
            case self::QUEUED_FOR_FABRICATION:
                return QueuedForFabrication::fromPayload($payload);
            case self::QUEUED_FOR_MANUFACTURING:
                return QueuedForManufacturing::fromPayload($payload);
            case self::IN_FABRICATION:
                return InFabrication::fromPayload($payload);
            case self::IN_MANUFACTURING:
                return InManufacturing::fromPayload($payload);
            case self::SHIPPED:
                return Shipped::fromPayload($payload);
            case self::REFUNDED:
                return Refunded::fromPayload($payload);
            case self::ERROR:
                return Error::fromPayload($payload);
            default:
                throw new \InvalidArgumentException("Invalid action type \"$actionType\"");
        }
    }
}
