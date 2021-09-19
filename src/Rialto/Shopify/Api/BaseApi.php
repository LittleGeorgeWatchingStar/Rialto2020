<?php

namespace Rialto\Shopify\Api;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Rialto\Shopify\Storefront\Storefront;

abstract class BaseApi
{
    /** @var ClientInterface */
    protected $http;

    /** @var Storefront */
    private $store;

    public function __construct(ClientInterface $http, Storefront $store)
    {
        $this->http = $http;
        $this->store = $store;
    }

    protected function getBaseUrl()
    {
        return $this->store->getApiBaseUrl();
    }

    /**
     * @param string $urlpath
     * @param array $data
     * @return ResponseInterface
     */
    protected function postJson($urlpath, array $data)
    {
        return $this->http->request('post', $urlpath, [
            'json' => $data,
            'base_url' => $this->getBaseUrl(),
        ]);
    }

    protected function decodeBody(ResponseInterface $response)
    {
        return json_decode($response->getBody(), true);
    }

    /**
     * @param string $urlpath
     * @return ResponseInterface
     */
    protected function getJson($urlpath)
    {
        return $this->http->request('get', $urlpath, [
            'headers' => ['accept' => 'application/json'],
            'base_url' => $this->getBaseUrl(),
        ]);
    }

    protected function delete($urlpath)
    {
        return $this->http->request('delete', $urlpath, [
            'base_url' => $this->getBaseUrl(),
        ]);
    }
}
