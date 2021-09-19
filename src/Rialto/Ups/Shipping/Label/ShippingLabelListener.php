<?php

namespace Rialto\Ups\Shipping\Label;

use Psr\Log\LoggerInterface;
use Rialto\Printing\Job\PrintJob;
use Rialto\Printing\Job\PrintQueue;
use Rialto\Sales\Invoice\SalesInvoiceEvent;
use Rialto\Sales\SalesEvents;
use Rialto\Ups\Shipping\UpsShipment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Listens for events that require a UPS shipping label to be printed.
 */
class ShippingLabelListener implements EventSubscriberInterface
{
    /**
     * The service ID of the printer that should print bin labels.
     */
    const PRINTER_ID = 'ups';

    /** @var PrintQueue */
    private $printQueue;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(PrintQueue $queue, LoggerInterface $logger)
    {
        $this->printQueue = $queue;
        $this->logger = $logger;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     */
    public static function getSubscribedEvents()
    {
        return [
            SalesEvents::ORDER_INVOICE => 'onOrderInvoice',
        ];
    }


    public function onOrderInvoice(SalesInvoiceEvent $event)
    {
        $shipment = $event->getShipment();
        if (!$shipment) {
            return;
        }
        if (!$shipment instanceof UpsShipment) {
            return;
        }

        $this->logger->notice('Printing shipping labels...');
        foreach ($shipment->getShippingLabels() as $label) {
            $job = PrintJob::raw($label);
            $job->setDescription($event->getOrder());
            $this->printQueue->add($job, self::PRINTER_ID);
        }
    }
}
