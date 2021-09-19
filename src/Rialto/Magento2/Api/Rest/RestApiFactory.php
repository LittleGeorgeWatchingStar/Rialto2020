<?php

namespace Rialto\Magento2\Api\Rest;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Rialto\Magento2\Storefront\Storefront;

class RestApiFactory
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(SerializerInterface $serializer,
                                LoggerInterface $logger)
    {
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Test that we can talk to the Magento 2 REST API.
     *
     * @return ResponseInterface The response from the Magento API
     */
    public function testApiConnection(Storefront $store)
    {
        $http = $this->http($store);
        return $http->request('GET', 'rest/V1/products/types', [
            'http_errors' => false, // don't throw exception on HTTP error
        ]);
    }

    public function createInventoryApi(Storefront $store)
    {
        $http = $this->http($store);
        return new InventoryApi($store, $http, $this->logger);
    }

    public function createInvoiceApi(Storefront $store)
    {
        $http = $this->http($store);
        return new InvoiceApi($store, $http, $this->logger);
    }

    public function createOrderApi(Storefront $store)
    {
        $http = $this->http($store);
        return new OrderApi($this->serializer, $store, $http, $this->logger);
    }

    public function createShipmentApi(Storefront $store)
    {
        $http = $this->http($store);
        return new ShipmentApi($store, $http, $this->logger);
    }

    /**
     * Creates an HTTP client for communicating with the given store.
     *
     * @return Client
     */
    private function http(Storefront $store)
    {
        $stack = $this->createOauthStack($store);
        return new Client([
            'base_uri' => $store->getStoreUrl(),
            'handler' => $stack,
            'auth' => 'oauth',
            'http_errors' => true,
            'headers' => [
                'User-Agent' => 'Gumstix Rialto',
            ],
        ]);
    }

    /**
     * @return HandlerStack
     */
    private function createOauthStack(Storefront $store)
    {
        $params = [
            'consumer_key' => $store->getConsumerKey(),
            'consumer_secret' => $store->getConsumerSecret(),
            'token' => $store->getAccessToken(),
            'token_secret' => $store->getAccessTokenSecret(),
            'callback' => $store->getStoreUrl(),
        ];

        $oauth = new Oauth\Oauth1($params);
        $stack = HandlerStack::create();
        $stack->push($oauth);

        return $stack;
    }
}
