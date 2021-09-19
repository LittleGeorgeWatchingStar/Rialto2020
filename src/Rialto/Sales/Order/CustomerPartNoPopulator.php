<?php

namespace Rialto\Sales\Order;

use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Order\Orm\SalesOrderRepository;

/**
 * Auto populates SalesOrderDetails CustomerPartNo for SalesOrders using
 * SalesOrderDetails of the customer with the same SKUs
 */
class CustomerPartNoPopulator
{
    /** @var SalesOrderRepository */
    private $orderRepo;

    public function __construct(DbManager $dbm)
    {
        $this->orderRepo = $dbm->getRepository(SalesOrder::class);
    }

    public function autoPopulate(SalesOrder $order)
    {
        $items = $order->getLineItems();
        /** @var string[] $skus */
        $skus = array_map(function (SalesOrderDetail $item) {
            return $item->getSku();
        }, $items);

        $customer = $order->getCustomer();

        $query = $this->orderRepo->createBuilder()
            ->byCustomerMatch($customer)
            ->bySkuArray($skus)
            ->orderByDateOrdered();
        /** @var SalesOrder[] $existingOrders */
        $existingOrders = $query->getResult();

        $customerPartNoIndex = [];
        foreach ($existingOrders as $existingOrder) {
            foreach ($existingOrder->getLineItems() as $item) {
                $sku = $item->getSku();
                $customerPartNo = $item->getCustomerPartNo();
                if ($customerPartNo) {
                    $customerPartNoIndex[$sku] = $customerPartNo;
                }
            }
        }

        foreach ($items as $item) {
            $sku = $item->getSku();
            if (array_key_exists($sku, $customerPartNoIndex)) {
                $item->setCustomerPartNo($customerPartNoIndex[$sku]);
            }
        }
    }
}