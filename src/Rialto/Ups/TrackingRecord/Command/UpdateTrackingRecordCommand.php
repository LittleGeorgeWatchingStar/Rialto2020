<?php


namespace Rialto\Ups\TrackingRecord\Command;


use Rialto\Port\CommandBus\Command;

final class UpdateTrackingRecordCommand implements Command
{
    /** @var string */
    private $trackingNumber;

    public function __construct(string $trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }
}
