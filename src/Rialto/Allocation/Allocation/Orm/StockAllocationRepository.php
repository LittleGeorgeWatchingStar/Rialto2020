<?php

namespace Rialto\Allocation\Allocation\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Rialto\Allocation\Allocation\BinAllocation;
use Rialto\Allocation\Allocation\ProducerAllocation;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Manufacturing\Requirement\MissingStockRequirement;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;

/**
 * Database mapper for the StockAllocation class.
 */
class StockAllocationRepository extends FilteringRepositoryAbstract
{
    public function findByFilters(array $params)
    {
        $query = $this->queryByFilters($params);
        logDebug($query->getParameters(), $query->getSQL());
        return $query->getResult();
    }

    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('a');

        $builder->add('stockItem', function(QueryBuilder $qb, $stockId) {
            $qb->andWhere('a.stockItem = :stockId')
                ->setParameter('stockId', $stockId);
        });

        $builder->add('workOrder', function(QueryBuilder $qb, $workOrderId) {
            $qb->join(Requirement::class, 'req', Join::WITH,
                    'a.requirement = req')
                ->andWhere('req.workOrder = :workOrderId')
                ->setParameter('workOrderId', $workOrderId);
        });

        $builder->add('location', function(QueryBuilder $qb, $facility) {
            $qb->resetDQLParts(['select', 'from']);
            $qb->select('a')
                ->from(BinAllocation::class, 'a')
                ->join('a.source', 'bin')
                ->andWhere('bin.facility = :facility')
                ->setParameter('facility', $facility);
        });

        $builder->add('missing', function(QueryBuilder $qb, $missing) {
            switch ($missing) {
                case 'yes':
                    $qb->join(MissingStockRequirement::class, 'req',
                        Join::WITH, 'a.requirement = req');
                    break;
                case 'no':
                    $qb->join(Requirement::class, 'req',
                        Join::WITH, 'a.requirement = req');
                    break;
                default:
                    break;
            }
        });

        return $builder->buildQuery($params);
    }

    /**
     * @return int
     */
    public function getQtyUndelivered(
        Facility $facility,
        StockItem $item,
        Version $version = null)
    {
        $qb = $this->queryQtyUndelivered($item, $version);
        $qb->andWhere('bin.facility = :facility')
            ->setParameter('facility', $facility);
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param StockItem $item
     * @param Version $version (optional)
     * @return int
     *  The total number of units allocated but not yet delivered.
     */
    public function getTotalQtyUndelivered(StockItem $item, Version $version = null)
    {
        $qb = $this->queryQtyUndelivered($item, $version);
        $query = $qb->getQuery();
        return $query->getSingleScalarResult();
    }

    /** @return QueryBuilder */
    private function queryQtyUndelivered(StockItem $item, Version $version = null)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('sum(alloc.qtyAllocated)')
            ->from(BinAllocation::class, 'alloc')
            ->join('alloc.source', 'bin')
            ->andWhere('alloc.stockItem = :item')
            ->setParameter('item', $item->getSku());

        if (! $version ) {
            $version = Version::any();
        }
        if ( $version->isSpecified() ) {
            $qb->andWhere('bin.version = :version')
                ->setParameter('version', (string) $version);
        }
        return $qb;
    }

    /** @return int The number of allocations deleted. */
    public function deleteEmptyAllocations()
    {
        $alloc = StockAllocation::class;
        $query = $this->_em->createQuery("delete $alloc a where a.qtyAllocated = 0");
        return $query->execute();
    }

    /**
     * Returns all {@see StockAllocation} which will require a transfer to the
     * build {@see Facility} for manufactured {@see PurchaseOrder}
     * (not at needed facility).
     *
     * {@see StockAllocation} maybe allocated from current {@see StockBin} or
     * stock item {@see PurchaseOrder} which would be come {@see StockBin}
     * upon being received (and require transfer after being received).
     *
     * @return stockAllocation[]
     */
    public function getToBeTransferred()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('alloc')
            ->from(StockAllocation::class, 'alloc')
            ->join(Requirement::class, 'req', Join::WITH, 'alloc.requirement = req')
            ->join('req.workOrder', 'wo')
            ->join('wo.purchaseOrder', 'woPo')
            ->leftJoin('wo.child', 'woChild')

            ->leftJoin(BinAllocation::class, 'binAlloc', Join::WITH, 'binAlloc.requirement = req')
            ->leftJoin('binAlloc.source', 'bin')

            ->leftJoin(ProducerAllocation::class, 'proAlloc', Join::WITH, 'proAlloc.requirement = req')
            ->leftJoin('proAlloc.source', 'producer')
            ->leftJoin('producer.purchaseOrder', 'proPo')

            ->andWhere('woPo.buildLocation IS NOT NULL')
            ->andWhere('woPo.datePrinted IS NOT NULL')
            ->andWhere('woChild.parent IS NULL')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        'alloc = binAlloc',
                        'woPo.buildLocation != bin.facility',
                        'bin.transfer IS NULL'
                    ),
                    $qb->expr()->andX(
                        'alloc = proAlloc',
                        'woPo.buildLocation != proPo.deliveryLocation'
                    )
                )
            )

            ->addSelect('CASE WHEN wo.commitmentDate IS NULL THEN 1 ELSE 0 END AS HIDDEN commitmentIsNull')
            ->orderBy('commitmentIsNull')
            ->addOrderBy('wo.commitmentDate', 'ASC');
        return $qb->getQuery()->getResult();
    }
}
