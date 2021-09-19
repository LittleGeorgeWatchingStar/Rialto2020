<?php

namespace Rialto\Magento2\Api\Rest;

use DateTime;
use GuzzleHttp\Client;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Rialto\Magento2\Order\Order;
use Rialto\Magento2\Storefront\Storefront;

class OrderApi extends BaseApi
{
    /** @var SerializerInterface */
    private $serializer;

    public function __construct(
        SerializerInterface $serializer,
        Storefront $store,
        Client $http,
        LoggerInterface $logger)
    {
        $this->serializer = $serializer;
        parent::__construct($store, $http, $logger);
    }

    /**
     * Get a list of sales orders.
     *
     * @param DateTime $since Only orders since this date.
     * @return Order[] The orders.
     */
    public function getOrders(DateTime $since)
    {
        $query = "";
        if ($since) {
            $since = clone $since;
            $since->setTimezone(new \DateTimeZone('UTC'));
            $query = [
                'searchCriteria' => [
                    'filter_groups' => [
                        [
                            'filters' => [
                                [
                                    'field' => 'created_at',
                                    'value' => $since->format('Y-m-d H:i:s'),
                                    'condition_type' => 'from'
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        $response = $this->request('GET', "rest/V1/orders/", [
            'query' => $query,
        ]);
        $content = $this->decodeBody($response)['items'];
        return array_map(function (array $result) {
            return $this->deserializeOrder($result);
        }, $content);
    }

    private function deserializeOrder($order)
    {
        $serializedOrder = $this->serializer->deserialize(
            json_encode($order),
            Order::class,
            'json');
        $serializedOrder->setStore($this->store);
        return $serializedOrder;
    }

    /**
     * Get a list of sales orders with respect to current_page and page_size params.
     *
     * @return string[][] of Orders.
     */
    public function getAllOrders($currentPage, $pageSize)
    {
        $query = [
            'searchCriteria' => [
                "current_page" => $currentPage,
                "page_size" => $pageSize
            ]
        ];
        $response = $this->request('GET', "rest/V1/orders/", [
            'query' => $query,
        ]);
        return $this->decodeBody($response);
    }

    /**
     * Cancel the order whose order ID is given.
     *
     * @param $orderID
     */
    public function cancelOrder($orderID)
    {
        $this->request('POST', "rest/V1/orders/$orderID/cancel");
    }
}
