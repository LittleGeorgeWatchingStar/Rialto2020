<?php


namespace Rialto\Ups\TrackingRecord\Cli;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Purchasing\Invoice\Orm\SupplierInvoiceRepository;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Ups\TrackingRecord\TrackingRecord;
use Rialto\Ups\TrackingRecord\TrackingRecordRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdatePOTrackingRecordsCommand extends Command
{
    const NAME = 'ups-tracking-record:update-purchase-order-tracking-records';

    /** @var EntityManagerInterface */
    private $em;

    /** @var SupplierInvoiceRepository */
    private $supplierInvoiceRepo;

    /** @var TrackingRecordRepository */
    private $trackingRecordRepo;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct(self::NAME);
        $this->em = $em;
        $this->supplierInvoiceRepo = $this->em->getRepository(SupplierInvoice::class);
        $this->trackingRecordRepo = $this->em->getRepository(TrackingRecord::class);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var SupplierInvoice[] $supplierInvoicesForOpenPO */
        $supplierInvoicesForOpenPO = $this->supplierInvoiceRepo->findSupplierInvoicesForOpenPO();

        $trackingNumbers = array_map(function (SupplierInvoice $supplierInvoice) {
            return $supplierInvoice->getTrackingNumber();
        }, $supplierInvoicesForOpenPO);

        foreach ($trackingNumbers as $trackingNumber) {
            if (!$trackingNumber) {
                continue;
            }

            $trackingRecord = $this->trackingRecordRepo->getByTrackingNumber($trackingNumber);
            if (!$trackingRecord) {
                $newTrackingRecord = new TrackingRecord($trackingNumber);
                $this->em->persist($newTrackingRecord);
            }
        }

        $this->em->flush();

        return 0;
    }
}
