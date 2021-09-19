<?php

namespace Rialto\Supplier\Order\Web\TrackingFacades;

use Doctrine\ORM\EntityManagerInterface;
use Rialto\Purchasing\Invoice\Orm\SupplierInvoiceRepository;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Ups\TrackingRecord\TrackingRecord;
use Rialto\Ups\TrackingRecord\TrackingRecordRepository;
use Twig\Environment;

class SupplierInvoiceTrackingFacadesFactory
{
    /** @var SupplierInvoiceRepository */
    private $supplierInvoiceRepo;

    /** @var TrackingRecordRepository */
    private $TrackingRecordRepo;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var Environment */
    private $twig;


    public function __construct(EntityManagerInterface $em, Environment $twig)
    {
        $this->em = $em;
        $this->supplierInvoiceRepo = $this->em->getRepository(SupplierInvoice::class);
        $this->TrackingRecordRepo = $this->em->getRepository(TrackingRecord::class);
        $this->twig = $twig;
    }

    /**
     * @param SupplierInvoice[] $supplierInvoices
     * @return TrackingFacade[]
     *
     */
    public function fromSupplierInvoices(array $supplierInvoices): array
    {
        /** @var TrackingFacade[] $trackingFacades */
        $trackingFacades = [];
        $trackingNumbers = [];

        /** @var SupplierInvoice[] $trackingNumberToSupplierInvoicesDictionary */
        $trackingNumberToSupplierInvoicesDictionary = [];

        foreach ($supplierInvoices as $supplierInvoice) {
            $trackingNumbers[] = $supplierInvoice->getTrackingNumber();
            $trackingNumberToSupplierInvoicesDictionary[$supplierInvoice->getTrackingNumber()] = $supplierInvoice;
        }

        /** @var TrackingRecord[] $trackingRecords */
        $trackingRecords = $this->TrackingRecordRepo->getByTrackingNumbers($trackingNumbers);

        foreach ($trackingRecords as $tr) {
            $trackingNumber =  $tr->getTrackingNumber();
            $supplierInvoice = $trackingNumberToSupplierInvoicesDictionary[$trackingNumber];
            $po = $supplierInvoice->getPurchaseOrder();

            $trackingFacade = new TrackingFacade($tr, $po, $this->twig);
            $trackingFacades[] = $trackingFacade;
        }
        return $trackingFacades;
    }

    /**
     * @param PurchaseOrder[] $purchaseOrders
     * @return TrackingFacade[]
     */
    public function fromPurchaseOrders(array $purchaseOrders): array
    {
        $facades = [];
        foreach ($purchaseOrders as $purchaseOrder) {
            $poFacades = $this->fromSupplierInvoices($this->supplierInvoiceRepo->findByPurchaseOrder($purchaseOrder));
            if (count($poFacades) == 0) {
                $poFacades = [new TrackingFacade(null, $purchaseOrder, $this->twig)];
            }
            $facades = array_merge($facades, $poFacades);
        }

        return $facades;
    }
}
