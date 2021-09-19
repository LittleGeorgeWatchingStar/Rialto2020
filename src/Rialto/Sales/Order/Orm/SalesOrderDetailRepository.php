<?php

namespace Rialto\Sales\Order\Orm;

use DateTime;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Item;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\StockItem;

class SalesOrderDetailRepository extends RialtoRepositoryAbstract
{
    /**
     * Fetches the SalesOrderDetail whose sales order and stock item are given.
     *
     * @param SalesOrder $order
     * @param Item $item
     * @return SalesOrderDetail|object|null
     */
    public function findByOrderAndItem(
        SalesOrder $order,
        Item $item )
    {
        return $this->findOneBy([
            'salesOrder' => $order,
            'stockItem' => $item->getSku(),
        ]);
    }

    /**
     * Returns consumption information about stock items.
     *
     * @return array[]
     *  A Doctrine "mixed" result set of arrays like this: array(
     *    0 => StockItem entity
     *    'version' => string
     *    'totalQtyOrdered' => integer
     *  )
     */
    public function findConsumptionStatistics(
        DateTime $startDate,
        DateTime $endDate,
        SalesType $salesType = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('item')
            ->addSelect('detail.version')
            ->addSelect('sum(detail.qtyOrdered) as totalQtyOrdered')
            ->from(StockItem::class, 'item')
            ->join(SalesOrderDetail::class, 'detail', 'WITH',
                'detail.stockItem = item')
            ->join('detail.salesOrder', 'o')
            ->where('o.dateOrdered >= :startDate')
            ->andWhere('o.dateOrdered <= :endDate')
            ->andWhere('o.salesStage = :stage')
            ->groupBy('item.stockCode')
            ->addGroupBy('detail.version')
            ->setParameters([
                'startDate' => $startDate,
                'endDate' => $endDate,
                'stage' => SalesOrder::ORDER
            ]);
        if ( $salesType ) {
            $qb->andWhere('o.salesType = :type')
                ->setParameter('type', $salesType->getId());
        }
        return $qb->getQuery()->getResult();
    }

    public function findByDateAndSalesType(DateTime $startDate, SalesType $salesType = null)
    {
        $qb = $this->createQueryBuilder('detail')
            ->innerJoin('detail.salesOrder', 'o')
            ->where('o.dateOrdered >= :startDate')
            ->andWhere('o.salesStage = :stage')
            ->setParameters([
                'startDate' => $startDate,
                'stage' => SalesOrder::ORDER
            ]);
        if ( $salesType ) {
            $qb->andWhere('o.salesType = :type')
                ->setParameter('type', $salesType->getId());
        }
        return $qb->getQuery()->getResult();
    }

    public function findIncompleteManufacturedItemOrder()
    {
        // example from BomItemRepository
        $manufacturedStockItem = ManufacturedStockItem::class;
        $qb = $this->createQueryBuilder('detail')
            ->join('detail.salesOrder', 'salesOrder')
            ->join('detail.stockItem', 'stockItem')
            ->where('detail.completed = 0')
            ->andWhere('salesOrder.salesStage = :order')
            ->andwhere("stockItem instance of $manufacturedStockItem")
            ->andWhere('stockItem.stockCode like :BRD9')
            ->setParameters([
                'order' => "order",
                'BRD9' => "BRD9%"
            ]);
        return $qb->getQuery()->getResult();
    }
}
