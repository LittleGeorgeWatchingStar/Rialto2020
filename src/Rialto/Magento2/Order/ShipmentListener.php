<?php

namespace Rialto\Magento2\Order;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Magento2\Api\Rest\RestApiFactory;
use Rialto\Sales\Invoice\SalesInvoiceEvent;
use Rialto\Sales\SalesEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Handles order shipment events for Magento sales orders.
 */
class ShipmentListener
extends OrderListener
implements EventSubscriberInterface
{
    /**
     * @var RestApiFactory
     */
    private $apiFactory;

    public function __construct(ObjectManager $om, RestApiFactory $apiFactory)
    {
        parent::__construct($om);
        $this->apiFactory = $apiFactory;
    }
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     */
    public static function getSubscribedEvents()
    {
        return [
            SalesEvents::ORDER_INVOICE => 'createShipment',
        ];
    }

    /**
     * If the invoice is for a Magento order, create a shipment record in
     * Magento.
     */
    public function createShipment(SalesInvoiceEvent $event)
    {
        $store = $this->getStorefront($event->getOrder());
        if (! $store) {
            return;
        }
        $api = $this->apiFactory->createShipmentApi($store);
        $api->createShipment($event->getInvoice());
    }
}
