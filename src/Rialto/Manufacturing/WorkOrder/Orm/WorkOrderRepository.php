<?php

namespace Rialto\Manufacturing\WorkOrder\Orm;

use DateTime;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Rialto\Accounting\AccountingEventRepository;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Allocation\Allocation\BinAllocation;
use Rialto\Database\Orm\FilteringRepository;
use Rialto\Database\Orm\FilterQueryBuilder;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Producer\Orm\StockProducerRepository;
use Rialto\Sales\Returns\SalesReturnItem;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Stock\Transfer\TransferItem;

class WorkOrderRepository extends StockProducerRepository implements
    FilteringRepository,
    AccountingEventRepository
{
    public function queryByFilters(array $params)
    {
        if (empty($params['orderBy'])) {
            $params['orderBy'] = 'status';
        }

        $builder = $this->createRestBuilder('wo');
        $builder->join('wo.purchasingData', 'pd')
            ->join('pd.stockItem', 'item')
            ->join('wo.purchaseOrder', 'po')
            ->leftJoin('wo.child', 'child')
            ->leftJoin('child.purchasingData', 'childPd')
            ->leftJoin('childPd.stockItem', 'childItem')
            ->leftJoin('wo.parent', 'parent')
            ->leftJoin('parent.purchasingData', 'parentPd')
            ->leftJoin('parentPd.stockItem', 'parentItem');
        $builder->addSelect([
            'pd',
            'item',
            'child',
            'childPd',
            'childItem',
            'parent',
            'parentPd',
            'parentItem',
            'po',
        ]);

        $builder->add('workOrder', function (QueryBuilder $qb, $woId) {
            $qb->andWhere('wo.id = :id');
            $qb->setParameter('id', $woId);
            return true; /* don't process any more filters. */
        });

        $builder->add('location', function (QueryBuilder $qb, $locId) {
            $qb->andWhere('po.buildLocation = :locationId');
            $qb->setParameter('locationId', $locId);
        });

        $builder->add('stockItem', function (QueryBuilder $qb, $stockCode) {
            $qb->andWhere('item.stockCode = :stockCode');
            $qb->setParameter('stockCode', $stockCode);
        });

        $builder->add('parents', function (QueryBuilder $qb, $value) use ($params) {
            /* Ignore this filter if the user has selected a specific item. */
            if (! empty($params['stockItem'])) return;

            if ($value == 'no') {
                $qb->andWhere('child.id is null');
            } elseif ($value == 'yes') {
                $qb->andWhere('child.id is not null');
            }
        });

        $builder->add('createdStart', function (QueryBuilder $qb, $startDate) {
            $qb->andWhere('wo.dateCreated >= :createdStart')
                ->setParameter('createdStart', $startDate);
        });
        $builder->add('createdEnd', function (QueryBuilder $qb, $endDate) {
            $qb->andWhere('wo.dateCreated <= :createdEnd')
                ->setParameter('createdEnd', $endDate);
        });

        $builder->add('closed', function (QueryBuilder $qb, $value) {
            if ($value == 'yes') {
                $qb->andWhere('(wo.dateClosed is not null or wo.qtyReceived >= wo.qtyOrdered)');
            } elseif ($value == 'no') {
                $qb->andWhere('wo.dateClosed is null')
                    ->andWhere('wo.qtyReceived < wo.qtyOrdered');
            }
        });

        $builder->add('sellable', function (QueryBuilder $qb, $sellable) {
            if ('yes' == $sellable) {
                $qb->andWhere('item.category in (:sellable)')
                    ->setParameter('sellable', StockCategory::getSellableIds());
            } elseif ('no' == $sellable) {
                $qb->andWhere('item.category not in (:sellable)')
                    ->setParameter('sellable', StockCategory::getSellableIds());
            }
        });

        $builder->add('rework', function (QueryBuilder $qb, $value) {
            if ($value == 'no') {
                $qb->andWhere('wo.rework = 0');
            } elseif ($value == 'yes') {
                $qb->andWhere('wo.rework = 1');
            }
        });

        $builder->add('overdue', function (QueryBuilder $qb, $value) {
            if ($value == 'no') {
                $qb->andWhere('wo.requestedDate <= CURRENT_DATE()');
            } elseif ($value == 'yes') {
                $qb->andWhere('wo.requestedDate > CURRENT_DATE()');
            }
        });

        $builder->add('orderBy', function (QueryBuilder $qb, $value) {
            switch ($value) {
                case 'id':
                    $qb->orderBy('wo.id');
                    break;
                case 'stockCode':
                    $qb->orderBy('item.stockCode');
                    break;
                default:
                    $qb->addSelect('wo.qtyIssued / wo.qtyOrdered as HIDDEN percentIssued')
                        ->addSelect('wo.qtyReceived / wo.qtyOrdered as HIDDEN percentReceived')
                        ->addOrderBy('percentIssued', 'asc')
                        ->addOrderBy('percentReceived', 'asc')
                        ->addOrderBy('wo.id', 'asc');
                    break;
            }
        });

        $builder->add('startDate', function (QueryBuilder $qb, $startDate) {
            $qb->andWhere('wo.dateCreated >= :startDate')
                ->setParameter('startDate', $startDate);
        });

        $builder->add('endDate', function (QueryBuilder $qb, $endDate) {
            $date = new DateTime($endDate);
            $date->setTime(23, 59, 59);
            $qb->andWhere('wo.dateCreated <= :endDate')
                ->setParameter('endDate', $date);
        });

        return $builder->buildQuery($params);
    }

    /** @return FilterQueryBuilder */
    private function createRestBuilder($alias)
    {
        return new FilterQueryBuilder($this->createQueryBuilder($alias));
    }

    /**
     * Finds an open rework order (that is, a work order to repair a broken item)
     * that matches the given RMA item.
     *
     * @param SalesReturnItem $rmaItem
     * @return WorkOrder|null
     *  Returns null if there is no matching rework order.
     */
    public function findOpenReworkOrder(SalesReturnItem $rmaItem)
    {
        $qb = $this->createQueryBuilder('wo');
        $qb->join('wo.purchasingData', 'pd')
            ->join('wo.requirements', 'wor')
            ->leftJoin('wo.purchaseOrder', 'po')
            ->where('po.datePrinted is null')
            ->andWhere('pd.stockItem = :rmaItem')
            ->andWhere('wor.stockItem = :rmaItem')
            ->andWhere('wo.rework = 1')
            ->andWhere('wo.dateClosed is null')
            ->andWhere('wo.qtyIssued = 0')
            ->setParameters([
                'rmaItem' => $rmaItem->getId(),
            ]);

        $version = $rmaItem->getVersion();
        if ($version->isSpecified()) {
            $qb->andWhere('wo.version = :version')
                ->setParameter('version', (string) $version);
        }

        $customization = $rmaItem->getCustomization();
        if ($customization) {
            $qb->andWhere('wo.customization = :customization')
                ->setParameter('customization', $customization->getId());
        }
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByType(SystemType $sysType, $typeNo)
    {
        return [$this->find($typeNo)];
    }

    /**
     * Summary of work order POs that need allocation, grouped by
     * supplier.
     *
     * @return string[][]
     */
    public function findOrdersNeedingAllocationSummary()
    {
        $sql = "
            select count(distinct OrderNo) as numOrders, supplierID, supplierName
            from (
                select po.OrderNo,
                supp.SupplierID as supplierID,
                supp.SuppName as supplierName,
                (req.unitQtyNeeded * wo.qtyOrdered) + req.scrapCount as extQtyNeeded,
                ifnull(sum(alloc.Qty), 0) as qtyAllocated,
                req.id,
                req.stockCode
                from PurchOrders po
                join Suppliers supp
                    on po.SupplierNo = supp.SupplierID
                join StockProducer wo
                    on wo.purchaseOrderID = po.OrderNo
                    and wo.type = 'labour'
                join Requirement req
                    on req.consumerType = :reqType
                    and req.consumerID = wo.id
                left join StockAllocation alloc
                    on alloc.requirementID = req.id
                where wo.dateClosed is null
                group by req.id
                having qtyAllocated < extQtyNeeded
            ) as AllocSummary
            group by supplierID
            order by supplierName";

        $conn = $this->_em->getConnection();
        $stmt = $conn->executeQuery($sql, [
            'reqType' => Requirement::CONSUMER_TYPE,
        ]);
        return $stmt->fetchAll();
    }

    /** @return QueryBuilder */
    public function queryPotentialSourceBuilds(StockItem $item)
    {
        $qb = $this->createQueryBuilder('wo');
        $qb->join('wo.purchasingData', 'pd')
            ->where('pd.stockItem = :item')
            ->andWhere('wo.qtyReceived > 0')
            ->setParameters([
                'item' => $item->getSku()
            ]);
        return $qb;
    }

    public function findReceivableOrders(Facility $location)
    {
        $qb = $this->createQueryBuilder('wo')
            ->join('wo.purchasingData', 'pd')
            ->join('pd.stockItem', 'item')
            ->join('wo.purchaseOrder', 'po')
            ->andWhere('po.buildLocation = :location')
            ->andWhere('po.datePrinted is not null')
            ->andWhere('wo.qtyOrdered > 0')
            ->andWhere('wo.qtyReceived < wo.qtyOrdered')
            ->andWhere('wo.dateClosed is null')
            ->setParameter('location', $location->getId())
            ->orderBy('item.stockCode');
        return $qb->getQuery()->getResult();
    }

    /**
     * @param WorkOrder $order
     *  A work order that is about to be purchased.
     * @param PurchasingData $extra
     *  A purch data record for an additional one-time manufacturing service,
     *  such as stencil or programming. A "one-time" service is
     *  one that only needs to be purchased once per item and version.
     * @return boolean
     *  True if the purchase order for the given work order needs
     *  an additional line item for the given purch data record.
     */
    public function needsAdditionalLineItem(WorkOrder $order, PurchasingData $extra)
    {
        $pcb = $this->getPcbComponent($order);
        if (! $pcb) {
            return false;
        }
        assertion($pcb->getVersion()->isSpecified());

        $pattern = '%' . $extra->getSku() . '%';
        $qb = $this->createQueryBuilder('wo');
        $qb->select($qb->expr()->count('wo.id'))
            ->join('wo.requirements', 'wor')
            ->join('wo.purchaseOrder', 'po')
            ->join('po.items', 'pod')
            ->where('po.datePrinted is not null')
            ->andWhere('wor.stockItem = :stockCode')
            ->andWhere('wor.version = :version')
            ->andWhere('pod.description like :pattern')
            ->setParameters([
                'stockCode' => $pcb->getSku(),
                'version' => (string) $pcb->getVersion(),
                'pattern' => $pattern,
            ]);
        $query = $qb->getQuery();
        $numRows = (int) $query->getSingleScalarResult();
        return $numRows == 0;
    }

    /** @return Requirement|null */
    private function getPcbComponent(WorkOrder $order)
    {
        foreach ($order->getRequirements() as $woReq) {
            if ($woReq->isPCB()) return $woReq;
        }
        return null;
    }

    /**
     * Finds the original build order for the given rework order.
     * @param WorkOrder $rework
     * @return WorkOrder|null
     * @throws InvalidArgumentException if $rework is not a rework order.
     */
    public function findOriginalBuild(WorkOrder $rework)
    {
        if (! $rework->isRework()) {
            throw new InvalidArgumentException("$rework is not a rework order");
        }

        $qb = $this->createQueryBuilder('build');
        $qb->join(SalesReturnItem::class, 'rmaItem', Join::WITH,
            'rmaItem.originalWorkOrder = build')
            ->join('rmaItem.reworkOrder', 'rework')
            ->where('rework.id = :reworkID')
            ->setParameter('reworkID', $rework->getId());
        return $qb->getQuery()->getOneOrNullResult();
    }

    /** @return WorkOrder[] */
    public function findByTransfer(Transfer $transfer)
    {
        $qb = $this->queryOpen()
            ->join('prod.requirements', 'req')
            ->join(BinAllocation::class, 'alloc', 'WITH',
                'alloc.requirement = req')
            ->join(TransferItem::class, 'item', 'WITH',
                'alloc.source = item.stockBin')
            ->andWhere('item.transfer = :transfer')
            ->setParameter('transfer', $transfer);
        return $qb->getQuery()->getResult();
    }

    /** @return WorkOrder[] */
    public function findByStockItem(StockItem $stockItem, string $version = null)
    {
        $qb = $this->createQueryBuilder('wo')
            ->join('wo.purchasingData', 'pd')
            ->andWhere('pd.stockItem = :stockItem')
            ->setParameter(':stockItem', $stockItem);
        if ($version !== null) {
            $qb->andWhere('wo.version = :version')
                ->setParameter(':version', $version);
        }
        return $qb->getQuery()->getResult();
    }
}
