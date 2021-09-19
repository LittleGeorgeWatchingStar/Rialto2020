<?php

namespace Rialto\Shopify\Order;


use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\Client;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Shopify\Order\Api\OrderApi;
use Rialto\Shopify\Storefront\Storefront;
use Rialto\Shopify\Storefront\StorefrontRepository;

/**
 * Base class for listeners that are listening for sales order events.
 */
abstract class OrderListener
{
    /** @var StorefrontRepository */
    private $repo;

    /** @var Client */
    private $http;

    public function __construct(ObjectManager $om, Client $http)
    {
        $this->repo = $om->getRepository(Storefront::class);
        $this->http = $http;
    }

    /** @return Storefront|null */
    protected function getStorefront(SalesOrder $order)
    {
        $creator = $order->getCreatedBy();
        return $this->repo->findByUserIfExists($creator);
    }

    /** @return OrderApi */
    protected function getApi(Storefront $store)
    {
        return new OrderApi($this->http, $store);
    }

}
