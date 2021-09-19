<?php

namespace Rialto\Sales\Invoice;

use Psr\Log\LoggerInterface;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Sales\SalesEvents;
use Rialto\Shipping\Shipment\SalesOrderShipment;
use Rialto\Shipping\Shipment\ShipmentFactory;
use Rialto\Ups\TrackingRecord\TrackingRecord;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Does shipping and accounting for a sales order invoice.
 */
class SalesInvoiceProcessor
{
    /** @var DbManager */
    private $dbm;

    /** @var ShipmentFactory */
    private $shipmentFactory;

    /** @var EventDispatcherInterface */
    private $dispatcher = null;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        DbManager $dbm,
        ShipmentFactory $factory,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger)
    {
        $this->dbm = $dbm;
        $this->shipmentFactory = $factory;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }


    /**
     * Processes the invoice, doing any accounting and shipping tasks
     * that need to be done.
     *
     * @return DebtorTransaction
     *  The invoice transaction.
     */
    public function processInvoice(SalesInvoice $invoice)
    {
        $this->logger->notice(sprintf('Invoicing sales order %s for $%s...',
            $invoice->getOrderNumber(),
            number_format($invoice->getTotalPrice(), 2)
        ));

        $debtorTrans = $invoice->process($this->dbm);
        $this->dbm->flush();
        $shipment = $this->processShipment($invoice, $debtorTrans);
        $this->dbm->flush();
        $this->dispatchEvent($invoice, $shipment);
        $this->logger->notice('Invoice processed successfully.');

        return $debtorTrans;
    }

    /**
     * @return SalesOrderShipment|null
     *  Null if the order does not contain shippable items.
     */
    private function processShipment(
        SalesInvoice $invoice,
        DebtorInvoice $debtorTrans)
    {
        if (!$invoice->containsShippableItems()) {
            return null;
        }
        $shipment = $this->shipmentFactory->createShipment($invoice, $invoice->getShippingMethod());
        $shipment->setPackages($invoice->getPackages());
        $this->shipmentFactory->ship($shipment);
        $tracking = $shipment->getTrackingNumber();
        $msg = sprintf('Creating shipment via %s', $shipment->getShippingMethod());
        if ($tracking) {
            $debtorTrans->setConsignment($tracking);
            $invoice->setTrackingNumber($tracking);
            $record = new TrackingRecord($tracking);
            $this->dbm->persist($record);
            $msg .= " with tracking number $tracking";
        }
        $msg .= "...";

        $this->logger->notice($msg);
        return $shipment;
    }

    private function dispatchEvent(
        SalesInvoice $invoice,
        SalesOrderShipment $shipment = null)
    {
        $event = new SalesInvoiceEvent($invoice);
        if ($shipment) {
            $event->setShipment($shipment);
        }
        $this->dispatcher->dispatch(SalesEvents::ORDER_INVOICE, $event);
    }
}
