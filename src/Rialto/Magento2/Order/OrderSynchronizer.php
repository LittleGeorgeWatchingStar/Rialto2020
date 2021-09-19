<?php

namespace Rialto\Magento2\Order;

use Rialto\Accounting\Card\CardTransaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Magento2\Api\Rest\RestApiFactory;
use Rialto\Magento2\Order\Order as MagentoOrder;
use Rialto\Magento2\Storefront\Storefront;
use Rialto\Sales\Order\Import\OrderImporter;
use Rialto\Sales\Order\Orm\SalesOrderRepository;
use Rialto\Sales\Order\SalesOrder;

/**
 * Pulls Magento orders into Rialto.
 */
class OrderSynchronizer implements OrderSynchronizerInterface
{
    /** @var DbManager */
    private $dbm;

    /** @var SalesOrderRepository */
    private $orderRepo;

    /** @var RestApiFactory */
    private $apiFactory;

    /** @var OrderImporter */
    private $importer;

    public function __construct(
        DbManager $dbm,
        RestApiFactory $apiFactory,
        OrderImporter $importer)
    {
        $this->dbm = $dbm;
        $this->orderRepo = $dbm->getRepository(SalesOrder::class);
        $this->apiFactory = $apiFactory;
        $this->importer = $importer;
    }

    public function alreadyExists(MagentoOrder $order, Storefront $store): bool
    {
        return $this->orderRepo->orderAlreadyExists(
            $store->getUser(),
            $order->getSourceId());
    }

    /** @return \DateTime|null */
    public function findDateOfMostRecentOrder(Storefront $store)
    {
        return $this->orderRepo->findDateOfMostRecentOrderCreatedByUser(
            $store->getUser());
    }

    /** @return MagentoOrder[] The orders */
    public function getOrderList(Storefront $store, \DateTime $lastUpdate): array
    {
        $api = $this->apiFactory->createOrderApi($store);
        return $api->getOrders($lastUpdate);
    }

    public function createOrder(MagentoOrder $order): SalesOrder
    {
        assertion(!$order->isSuspectedFraud());
        assertion(!$order->isCanceled());
        assertion(!$order->isMissingCardAuthoriation());
        $rialtoOrder = $this->importer->createSalesOrder($order);
        $this->dbm->persist($rialtoOrder);

        if (!$order->isQuote()) {
            $cardTrans = $this->recordCardAuthorization($order, $rialtoOrder);
            $this->dbm->persist($cardTrans);
        }

        return $rialtoOrder;
    }

    private function recordCardAuthorization(
        MagentoOrder $magOrder,
        SalesOrder $rialtoOrder): CardTransaction
    {
        $cardTrans = $magOrder->getCardAuthorization($this->dbm);
        $rialtoOrder->addCardTransaction($cardTrans);
        return $cardTrans;
    }
}
