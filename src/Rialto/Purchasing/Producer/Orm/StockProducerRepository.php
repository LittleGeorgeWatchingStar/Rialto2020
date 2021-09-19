<?php

namespace Rialto\Purchasing\Producer\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\DependentRecordFinder;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssue;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Receiving\GoodsReceivedItem;
use Rialto\Sales\Returns\SalesReturnItem;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Move\StockMove;

abstract class StockProducerRepository extends RialtoRepositoryAbstract
{
    /** @return StockProducerQueryBuilder */
    public function createBuilder()
    {
        return new StockProducerQueryBuilder($this);
    }

    /**
     * @return int The quantity remaining on order.
     */
    public function getTotalQtyOnOrder(Item $item, Version $version = null)
    {
        return $this->createBuilder()
            ->openForAllocation()
            ->forItem($item)
            ->forVersion($version ?: Version::any())
            ->getQtyOnOrder();
    }

    /** @return StockProducer[] */
    public function findAllOpenProducers(Item $item, Version $version = null)
    {
        return $this->createBuilder()
            ->openForAllocation()
            ->forItem($item)
            ->forVersion($version ?: Version::any())
            ->getResult();
    }

    /** @return QueryBuilder */
    protected function queryOpen()
    {
        return $this->createQueryBuilder('prod')
            ->andWhere('prod.dateClosed is null')
            ->andWhere('prod.qtyReceived < prod.qtyOrdered');
    }

    public function getQtyOnOrder(Facility $location,
                                  Item $item,
                                  Version $version = null)
    {
        return $this->createBuilder()
            ->openForAllocation()
            ->forItem($item)
            ->forVersion($version ?: Version::any())
            ->byDeliveryLocation($location)
            ->getQtyOnOrder();
    }

    /**
     * The stock producer that originally created $bin.
     * @return StockProducer|null
     */
    public function findOriginalProducer(StockBin $bin)
    {
        $qb = $this->createQueryBuilder('sp');
        $qb->join('sp.purchaseOrder', 'po')
            ->join('po.receipts', 'grn')
            ->join('sp.purchasingData', 'pd')
            ->join(StockMove::class, 'move', 'WITH',
                'move.systemType = :grnType and move.systemTypeNumber = grn.id')
            ->setParameter('grnType', SystemType::PURCHASE_ORDER_DELIVERY)
            ->where('move.stockBin = :binID')
            ->andWhere('pd.stockItem = move.stockItem')
            ->setParameter('binID', $bin->getId());
        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

    public function hasDependentRecords(StockProducer $producer)
    {
        $dependents = [
            GoodsReceivedItem::class => ['producer'],
            WorkOrderIssue::class => ['workOrder'],
            SalesReturnItem::class => ['originalWorkOrder', 'reworkOrder'],
        ];

        $finder = new DependentRecordFinder($this->_em);
        return $finder->hasDependentRecords($producer, $dependents);
    }
}
