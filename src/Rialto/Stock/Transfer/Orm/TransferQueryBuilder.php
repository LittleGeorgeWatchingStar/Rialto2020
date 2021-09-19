<?php

namespace Rialto\Stock\Transfer\Orm;

use Gumstix\Doctrine\HighLevelQueryBuilder;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;

class TransferQueryBuilder extends HighLevelQueryBuilder
{
    public function __construct(TransferRepository $repo)
    {
        parent::__construct($repo, 'transfer');
        $this->qb->leftJoin('transfer.lineItems', 'item');
    }

    public function notKitted()
    {
        $this->qb->andWhere('transfer.dateKitted is null');
        return $this;
    }

    public function kitted()
    {
        $this->qb->andWhere('transfer.dateKitted is not null');
        return $this;
    }

    public function sent()
    {
        $this->qb->andWhere('transfer.dateShipped is not null');
        return $this;
    }

    public function notSent()
    {
        $this->qb->andWhere('transfer.dateShipped is null');
        return $this;
    }

    public function hasTrackingNumber()
    {
        $this->qb->andWhere('transfer.trackingNumbers != :empty')
            ->setParameter('empty', '[]');
        return $this;
    }

    public function notHasTrackingNumber()
    {
        $this->qb->andWhere('transfer.trackingNumbers = :empty')
            ->setParameter('empty', '[]');
        return $this;
    }

    /**
     * @param string[] $shippingMethods
     */
    public function isTrackingNumberNotRequired(array $shippingMethods): self
    {
        $this->qb->andWhere('transfer.shippingMethod IN (:shippingMethods)')
                ->setParameter('shippingMethods', $shippingMethods);
        return $this;
    }

    /**
     * @param string[] $shippingMethods
     */
    public function isTrackingNumberRequired(array $shippingMethods): self
    {
        $this->qb->andWhere('transfer.shippingMethod NOT IN (:shippingMethods)')
                ->setParameter('shippingMethods', $shippingMethods);
        return $this;
    }

    public function isReadyForPickup(string $shippingMethod)
    {
        $this->qb->andWhere('(transfer.shippingMethod = :shippingMethod) OR (transfer.shippingMethod != :shippingMethod AND transfer.trackingNumbers != :empty)')
            ->setParameter('shippingMethod', $shippingMethod)
            ->setParameter('empty', '[]');
        return $this;
    }

    public function received()
    {
        $this->qb->andWhere('transfer.dateReceived is not null');
        return $this;
    }

    public function notReceived()
    {
        $this->qb->andWhere('transfer.dateReceived is null');
        return $this;
    }

    /**
     * Transfers that have no items yet.
     */
    public function isEmpty()
    {
        $this->qb->andWhere('item.id is null');
        return $this;
    }

    public function notEmpty()
    {
        $this->qb->andWhere('item.id is not null');
        return $this;
    }

    /**
     * @param Facility|string $origin
     */
    public function byOrigin($origin)
    {
        $this->qb->andWhere('transfer.origin = :origin')
            ->setParameter('origin', $origin);
        return $this;
    }

    public function byDestination(Facility $destination)
    {
        $this->qb->andWhere('transfer.destination = :dest')
            ->setParameter('dest', $destination);
        return $this;
    }

    public function byBin(StockBin $bin)
    {
        $this->qb->andWhere('item.stockBin = :bin')
            ->setParameter('bin', $bin);
        return $this;
    }

    public function hasMissingItems()
    {
        $this->received();
        $this->qb->andWhere('item.dateReceived is null');
        return $this;
    }

    public function forPurchaseOrder(PurchaseOrder $order)
    {
        $this->qb->join('item.stockBin', 'bin');

        TransferRepository::selectByPurchaseOrderId($this->qb, $order->getId());

        $this->qb->andWhere('transfer.destination = :dest')
            ->setParameter('dest', $order->getBuildLocation());
        return $this;
    }

    public function orderById()
    {
        $this->qb->orderBy('transfer.id');
        return $this;
    }
}
