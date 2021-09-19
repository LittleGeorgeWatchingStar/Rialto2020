<?php

namespace Rialto\Sales\Shipping;

use Rialto\Sales\Order\SalesOrderEvent;
use Rialto\Sales\SalesEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApproveToShipEventListener implements EventSubscriberInterface
{
    /** @var SalesOrderShippingApproval */
    private $approvalService;

    /** @var EventDispatcherInterface */
    private $dispatcher;

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
            SalesEvents::ORDER_ALLOCATED => 'approveToShipIfReady'
        ];
    }

    public function __construct(SalesOrderShippingApproval $service,
                                EventDispatcherInterface $dispatcher)
    {
        $this->approvalService = $service;
        $this->dispatcher = $dispatcher;
    }

    public function approveToShipIfReady(SalesOrderEvent $event)
    {
        $order = $event->getOrder();
        $allocStatus = $order->getAllocationStatus();

        if ($order->isQuotation()) {
            return;
        }
        if (!$allocStatus->isKitComplete()) {
            return;
        }
        if ($order->getComments()) {
            return;
        }
        if ($order->doNotShip()) {
            return;
        }

        $errors = $this->approvalService->validate($order);
        if (count($errors) > 0) {
            return;
        }

        $order->approveToShip($this->dispatcher);
    }
}
