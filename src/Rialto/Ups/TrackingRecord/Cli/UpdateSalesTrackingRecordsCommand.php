<?php


namespace Rialto\Ups\TrackingRecord\Cli;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Sales\Order\Orm\SalesOrderRepository;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Ups\TrackingRecord\TrackingRecord;
use Rialto\Ups\TrackingRecord\TrackingRecordRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateSalesTrackingRecordsCommand extends Command
{
    const NAME = 'ups-tracking-record:update-sales-order-tracking-records';

    /** @var EntityManagerInterface */
    private $em;

    /** @var SalesOrderRepository */
    private $salesOrderRepo;

    /** @var TrackingRecordRepository */
    private $trackingRecordRepo;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct(self::NAME);
        $this->em = $em;
        $this->salesOrderRepo = $this->em->getRepository(SalesOrder::class);
        $this->trackingRecordRepo = $this->em->getRepository(TrackingRecord::class);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $openSO = $this->salesOrderRepo->findOpenSalesOrder();

        $debtorInvoicesForOpenSO = [];
        foreach ($openSO as $so) {
            $debtorInvoicesForOpenSO = array_merge($debtorInvoicesForOpenSO, $so->getInvoices());
        }

        /** @var DebtorInvoice[] $debtorInvoicesForOpenSO */
        $salesOrderTrackingNumbers = array_map(function (DebtorInvoice $debtorInvoice) {
            return $debtorInvoice->getConsignment();
        }, $debtorInvoicesForOpenSO);

        foreach ($salesOrderTrackingNumbers as $trackingNumber) {
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
