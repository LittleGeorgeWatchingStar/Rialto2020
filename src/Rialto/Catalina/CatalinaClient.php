<?php

namespace Rialto\Catalina;

use Gumstix\SSOBundle\Service\HttpClientFactory;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Rialto\Manufacturing\WorkOrder\WorkOrder;

/**
 * For interacting with the Catalina API.
 */
class CatalinaClient
{
    /** @var Client */
    private $http;

    public function __construct(HttpClientFactory $factory, $catalinaUrl)
    {
        $this->http = $factory->builder()
            ->baseUrl($catalinaUrl)
            ->acceptJson()
            ->userAgent('Rialto')
            ->ssoAuth()
            ->throwExceptions(true)
            ->getClient();
    }

    /** @return string[] */
    public function getJob(WorkOrder $order)
    {
        $response = $this->http->get("/api/v2/job/?name=WO{$order->getId()}");
        $data = $this->parseResponse($response);
        if (is_array($data['objects']) && count($data['objects']) > 0) {
            return $data['objects'][0];
        }
        return null;
    }

    private function parseResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody(), true);
    }

    public function createJob(WorkOrder $order)
    {
        $this->http->post("/api/v2/job/", [
            'json' => [
                'id' => $order->getId(),
                'sku' => $order->getSku(),
                'version' => (string) $order->getVersion(),
                'locationName' => $order->getLocation()->getName(),
                'qtyOrdered' => $order->getQtyOrdered(),
                'rework' => $order->isRework() ? "True" : "False",
            ],
        ]);
    }

    /** @return string[][] */
    public function getResults(WorkOrder $order)
    {
        $response = $this->http->get("/api/v2/jobs/WO{$order->getId()}/status/");
        return $this->parseResponse($response);
    }
}
