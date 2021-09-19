<?php

namespace Rialto\Ups\TrackingRecord;

use Doctrine\ORM\EntityRepository;

class TrackingRecordRepository extends EntityRepository
{
    /**
     * @param string[] $trackingNums
     * @return TrackingRecord[]
     */
    public function getByTrackingNumbers(array $trackingNums) : array
    {
        return  $this->findBy(['trackingNumber' => $trackingNums]);
    }

    /**
     * @param string $trackingNum
     * @return null|object|TrackingRecord
     */
    public function getByTrackingNumber(string $trackingNum)
    {
        return  $this->findOneBy(['trackingNumber' => $trackingNum]);
    }

    /**
     * Get all tracking numbers that are not delivered, or otherwise incomplete.
     * @return TrackingRecord[]
     */
    public function getUndelivered(): array
    {
        return $this->findBy(['dateDelivered' => null]);
    }
}
