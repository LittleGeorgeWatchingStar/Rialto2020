<?php

namespace Rialto\Magento2\Api\Rest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Rialto\Magento2\Storefront\Storefront;

abstract class BaseApi
{
    /** @var Storefront */
    protected $store;

    /** @var Client */
    protected $http;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(Storefront $store,
                                Client $http,
                                LoggerInterface $logger)
    {
        $this->store = $store;
        $this->http = $http;
        $this->logger = $logger;
    }

    protected function request(string $method,
                               string $url,
                               array $options = []): ResponseInterface
    {
        $this->logger->debug("$method $url", $options);
        try {
            return $this->http->request($method, $url, $options);
        } catch (RequestException $ex) {
            $this->logException($ex);
            throw $ex;
        }
    }

    protected function logException(RequestException $ex)
    {
        $request = $ex->getRequest()->getBody()->getContents();
        $response = $ex->hasResponse()
            ? $ex->getResponse()->getBody()->getContents() : '';
        $this->logger->error($ex->getMessage(), [
            'request' => $request,
            'response' => $response,
        ]);
    }

    protected function getBody(ResponseInterface $response): string
    {
        return $response->getBody()->getContents();
    }

    protected function decodeBody(ResponseInterface $response): array
    {
        return json_decode($this->getBody($response), true);
    }
}
