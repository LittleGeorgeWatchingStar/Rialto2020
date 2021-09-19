<?php

namespace Rialto\Magento2\Api\Rest;

use GuzzleHttp\Psr7\Response;
use Rialto\Magento2\Storefront\Storefront;

class FakeRestApiFactory extends RestApiFactory
{
    /**
     * What response code would we like to test?
     *
     * @var int
     */
    private $responseCode = 200;

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
    }

    /**
     * @param int $statusCode
     */
    public function setResponseCode($statusCode)
    {
        $this->responseCode = $statusCode;
    }

    public function testApiConnection(Storefront $store)
    {
        return new Response($this->responseCode);
    }

    public function createInvoiceApi(Storefront $store)
    {
        return new FakeInvoiceApi();
    }
}
