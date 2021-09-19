<?php


namespace Rialto\Ups\TrackingRecord\Command;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Ups\Shipping\Webservice\UpsApiService;
use Rialto\Ups\TrackingRecord\TrackingRecord;
use Rialto\Ups\TrackingRecord\TrackingRecordRepository;

final class UpdateTrackingRecordHandler
{
    /** @var TrackingRecordRepository */
    private $trackingRecordRepo;

    /** @var UpsApiService */
    private $upsApi;

    public function __construct(EntityManagerInterface $em, UpsApiService $upsApi)
    {
        $this->trackingRecordRepo = $em->getRepository(TrackingRecord::class);
        $this->upsApi = $upsApi;
    }

    public function handle(UpdateTrackingRecordCommand $command)
    {
        $trackingNumber = $command->getTrackingNumber();

        $record = $this->trackingRecordRepo->getByTrackingNumber($trackingNumber);
        if (!$record) {
            throw new \OutOfBoundsException(
                "Tracking record for '$trackingNumber' not found.");
        }

        $trackingResponse = $this->upsApi->track($trackingNumber);
        $record->setDateDelivered($trackingResponse->getDeliveryDate());
        $record->setDateUpdate();
    }
}
