<?php


namespace Rialto\Magento2\Order;


use DateTime;
use Rialto\Magento2\Storefront\Storefront;
use Rialto\Sales\Order\SalesOrder;

interface OrderSynchronizerInterface
{
    /**
     * @return Order[]
     */
    function getOrderList(Storefront $store, DateTime $since): array;

    function alreadyExists(Order $order, Storefront $store): bool;

    /**
     * @param Storefront $store
     * @return DateTime|null
     */
    function findDateOfMostRecentOrder(Storefront $store);

    function createOrder(Order $order): SalesOrder;
}
