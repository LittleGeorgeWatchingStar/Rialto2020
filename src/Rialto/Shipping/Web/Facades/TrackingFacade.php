<?php

namespace Rialto\Shipping\Web\Facades;

use Rialto\Ups\TrackingRecord\TrackingRecord;

class TrackingFacade
{
    /** @var TrackingRecord */
    private $trackingRecord;

    public function __construct(TrackingRecord $trackingRecord)
    {
        $this->trackingRecord = $trackingRecord;
    }

    public function getTrackingNumber()
    {
        return $this->trackingRecord->getTrackingNumber();
    }

    public function getDateCreated()
    {
        $dateCreated = $this->trackingRecord->getDateCreated();
        return $dateCreated ? $dateCreated->format('Y-m-d') : null;
    }

    public function getDateDelivered()
    {
        $dateDelivered = $this->trackingRecord->getDateDelivered();
        return $dateDelivered ? $dateDelivered->format('Y-m-d') : null;
    }

    public function getDateUpdated()
    {
        $dateUpdate = $this->trackingRecord->getDateUpdated();
        return $dateUpdate ? $dateUpdate->format('Y-m-d') : null;
    }

    public function getTrackingStatus()
    {
        return $this->trackingRecord->getTrackingStatus();
    }
}