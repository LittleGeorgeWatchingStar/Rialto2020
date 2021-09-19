<?php

namespace Rialto\Magento2\Order;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Magento2\Api\Rest\RestApiFactory;
use Rialto\Sales\Order\SalesOrderEvent;
use Rialto\Sales\SalesEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for closed orders and closes the order in Magento, too, to keep
 * the two systems in sync.
 */
class OrderClosedListener extends OrderListener implements EventSubscriberInterface
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
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     */
    public static function getSubscribedEvents()
    {
        return [
            SalesEvents::ORDER_CLOSED => 'closeOrder',
        ];
    }

    public function closeOrder(SalesOrderEvent $event)
    {
        $order = $event->getOrder();
        $store = $this->getStorefront($order);
        if (!$store) {
            return;
        }
        $api = $this->apiFactory->createOrderApi($store);
        $api->cancelOrder($order->getSourceId());
    }
}
