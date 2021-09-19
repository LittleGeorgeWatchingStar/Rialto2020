<?php

namespace Rialto\Purchasing\Producer\CommitmentDateEstimator;

use DateTime;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Shipping\Method\ShippingTimeEstimator\ShippingTimeEstimatorInterface;
use Rialto\Stock\Item\PurchasedStockItem;

class StockProducerCommitmentDateEstimator
{
    /** @var ShippingTimeEstimatorInterface */
    private $shippingTimeEstimator;

    public function __construct(ShippingTimeEstimatorInterface $shippingTimeEstimator)
    {
        $this->shippingTimeEstimator = $shippingTimeEstimator;
    }

    /**
     * TODO: Handle processing time.
     *
     * @return DateTime|null
     */
    public function getEstimatedCommitmentDate(StockProducer $stockProducer)
    {
        if ($stockProducer->getCommitmentDate()) {
            return $stockProducer->getCommitmentDate();
        }

        if ($stockProducer->getStockItem() instanceof PurchasedStockItem) {
            $purchaseOrder = $stockProducer->getPurchaseOrder();
            $shippingMethod = $purchaseOrder->getShippingMethod();
            $sentDate = $purchaseOrder->getDateSent();
            if ($shippingMethod && $sentDate) {
                return $this->shippingTimeEstimator->getEstimatedDeliveryDate($shippingMethod, $sentDate);
            }
        }

        return null;
    }
}