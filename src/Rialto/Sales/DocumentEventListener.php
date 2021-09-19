<?php

namespace Rialto\Sales;

use Psr\Log\LoggerInterface;
use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Invoice\SalesInvoice;
use Rialto\Sales\Invoice\SalesInvoiceEvent;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderEvent;
use Rialto\Shipping\Export\Document\ElectronicExportInformation;
use Rialto\Shipping\Shipment\Document\ShipmentInvoice;
use Rialto\Shipping\Shipment\SalesOrderShipment;
use Rialto\Shipping\Shipment\ShipmentFactory;
use Rialto\Stock\Publication\Orm\PublicationRepository;
use Rialto\Stock\Publication\Publication;
use Rialto\Stock\Publication\PublicationPrintManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for sales events that require documents be printed and prints them.
 */
class DocumentEventListener implements EventSubscriberInterface
{
    /**
     * The service ID of the printer that should print the documents.
     */
    const PRINTER_ID = 'standard';

    const NUM_INVOICES_TO_PRINT = 3;

    /**
     * Needs to be lower priority than listeners that, eg, add shipments
     * to the order.
     */
    const INVOICE_PRIORITY = -5;

    /** @var ShipmentFactory */
    private $shipmentFactory;

    /** @var SalesPrintManager */
    private $printManager;

    /** @var PublicationRepository */
    private $pubRepo;

    /** @var PublicationPrintManager */
    private $pubManager;

    /** @var LoggerInterface $logger */
    private $logger;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            SalesEvents::ORDER_INVOICE => ['onOrderInvoice', self::INVOICE_PRIORITY],
            SalesEvents::APPROVED_TO_SHIP => 'onApprovedToShip',
        ];
    }

    public function __construct(
        ShipmentFactory $factory,
        SalesPrintManager $printManager,
        DbManager $dbm,
        PublicationPrintManager $pubManager,
        LoggerInterface $logger)
    {
        $this->shipmentFactory = $factory;
        $this->printManager = $printManager;
        $this->pubRepo = $dbm->getRepository(Publication::class);
        $this->pubManager = $pubManager;
        $this->logger = $logger;
    }

    public function onOrderInvoice(SalesInvoiceEvent $event)
    {
        $invoice = $event->getInvoice();
        $shipment = $event->getShipment();
        if (!$shipment) {
            return;
        }
        $this->printShippingDocuments($invoice, $shipment);
    }

    private function printShippingDocuments(
        SalesInvoice $invoice,
        SalesOrderShipment $shipment)
    {
        if ($this->requiresPaperExportDocuments($invoice)) {
            $this->printInvoiceIfNeeded($invoice);
        }
        $this->printSedIfNeeded($invoice, $shipment);
    }

    private function requiresPaperExportDocuments(SalesInvoice $invoice)
    {
        return !$this->shipmentFactory->canUseEdiDocuments($invoice);
    }

    private function printInvoiceIfNeeded(
        SalesInvoice $invoice,
        $numCopies = self::NUM_INVOICES_TO_PRINT)
    {
        $intlInvoice = new ShipmentInvoice($invoice);
        if (!$intlInvoice->isRequired()) {
            return;
        }
        $this->printManager->queueCommercialInvoice($invoice, $numCopies);
        $this->logger->notice("Sent $numCopies copies of commercial invoice to print queue.");

        $debtorTrans = $invoice->getDebtorTransaction();
        $this->printManager->queueDebtorTransaction($debtorTrans, $numCopies);
        $this->logger->notice("Sent $numCopies copies of debtor transaction to print queue.");
    }

    private function printSedIfNeeded(
        SalesInvoice $invoice,
        SalesOrderShipment $shipment,
        $numCopies = self::NUM_INVOICES_TO_PRINT)
    {
        $eei = new ElectronicExportInformation($invoice);
        if (!$eei->isRequired()) {
            return;
        }
        $eei->setTrackingNumber($shipment->getTrackingNumber());
        $this->printManager->queueEei($eei, $numCopies);
        $this->logger->notice("Sent $numCopies copies of EEI/SED form to print queue.");
    }

    /**
     * Prints the packing slip if the order is fully allocated and approved
     * to ship.
     */
    public function onApprovedToShip(SalesOrderEvent $event)
    {
        $order = $event->getOrder();
        assertion($order->isDueToShip());

        $this->printDocumentsIfReady($order);
    }

    private function printDocumentsIfReady(SalesOrder $order)
    {
        $status = $order->getAllocationStatus();
        if (!$status->isKitComplete()) {
            return;
        }
        $this->printPackingSlip($order);
        $this->printPublications($order);
    }

    private function printPackingSlip(SalesOrder $order)
    {
        $this->printManager->queuePackingSlip($order);
        $this->logger->notice("Sent packing slip for $order to print queue.");
    }

    private function printPublications(SalesOrder $order)
    {
        $pubs = $this->pubRepo->findBySalesOrder($order);
        foreach ($pubs as $pub) {
            $this->pubManager->queue($pub);
            $this->logger->notice("Send publication \"$pub\" to print queue.");
        }
    }
}
