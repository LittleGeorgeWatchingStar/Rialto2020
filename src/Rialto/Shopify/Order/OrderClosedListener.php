<?php

namespace Rialto\Shopify\Order;


use Rialto\Sales\Order\SalesOrderEvent;

class OrderClosedListener extends OrderListener
{
    public function onOrderClosed(SalesOrderEvent $event)
    {
        $order = $event->getOrder();
        $store = $this->getStorefront($order);
        if (! $store ) {
            return;
        }

        $api = $this->getApi($store);
        $api->closeOrder($order);
    }
}
