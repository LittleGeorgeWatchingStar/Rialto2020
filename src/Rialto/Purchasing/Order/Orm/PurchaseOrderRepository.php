<?php

namespace Rialto\Purchasing\Order\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use Gumstix\Doctrine\HighLevelFilter;
use Rialto\Database\Orm\FilteringRepository;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Manufacturing\BuildFiles\BuildFiles;
use Rialto\Manufacturing\Task\ProductionTask;
use Rialto\Manufacturing\Task\StaleOrderDefinition;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\Version\Version;

class PurchaseOrderRepository extends RialtoRepositoryAbstract implements FilteringRepository
{
    public function queryByFilters(array $params)
    {
        $builder = new HighLevelFilter($this->createBuilder());

        $builder->add('orderNo', function (PurchaseOrderQueryBuilder $qb, $id) {
            $qb->byId($id);
            return true; // stop any further filters from being processed.
        });

        $builder->add('supplierRef', function (PurchaseOrderQueryBuilder $qb, $ref) {
            $qb->bySupplierReference($ref);
        });

        $builder->add('stockItem', function (PurchaseOrderQueryBuilder $qb, $sku) {
            $qb->bySku($sku);
        });

        $builder->add('supplier', function (PurchaseOrderQueryBuilder $qb, $supplier) {
            $qb->bySupplier($supplier);
        });

        $builder->add('printed', function (PurchaseOrderQueryBuilder $qb, $sent) {
            if ($sent == 'yes') {
                $qb->isSent();
            } elseif ($sent == 'no') {
                $qb->isNotSent();
            }
        });

        $builder->add('completed', function (PurchaseOrderQueryBuilder $qb, $completed) {
            if ($completed == 'yes') {
                $qb->isClosed();
            } elseif ($completed == 'no') {
                $qb->isOpen();
            }
        });

        $builder->add('exclude', function (PurchaseOrderQueryBuilder $qb, $exclude) {
            $exclude = explode(',', $exclude);
            $qb->excludeInitiators($exclude);
        });

        $builder->add('deliveryLocation', function (PurchaseOrderQueryBuilder $qb, $location) {
            $qb->byDeliveryLocation($location);
        });

        $builder->add('orderDate', function (PurchaseOrderQueryBuilder $qb, $dateRange) {
            $qb->byDateOrdered($dateRange);
        });

        return $builder->buildQuery($params);
    }

    public function createBuilder()
    {
        return new PurchaseOrderQueryBuilder($this);
    }

    /**
     * Returns an open, unsent purchase order that is capable
     * of supplying the given stock item (even if that item is not
     * currently on the PO).
     *
     * @param Item $item
     * @param Version $version
     * @param Supplier $supplier (optional)
     * @param Facility $intoLocation
     *  (defaults to headquarters)
     * @return PurchaseOrder
     */
    public function findOpenOrderThatCanSupplyItem(
        Item $item,
        Version $version,
        Supplier $supplier = null,
        Facility $intoLocation = null)
    {
        $parameters = [
            'sku' => $item->getSku(),
            'location' => $intoLocation ? $intoLocation->getId() : Facility::HEADQUARTERS_ID,
        ];

        $supplierMatches = "";
        if ($supplier) {
            $supplierMatches = "AND po.SupplierNo = :supplier";
            $parameters['supplier'] = $supplier->getId();

        }

        $versionMatches = "";
        if ($version->isSpecified()) {
            $versionMatches = "AND pd.Version in (:versions)";
            $parameters['versions'] = [
                (string) $version,
                Version::ANY
            ];
        }

        $sql = "
            SELECT po.*
            FROM PurchData pd
            JOIN PurchasingCost pc ON pc.purchasingDataId = pd.ID
            JOIN PurchOrders po ON pd.SupplierNo = po.SupplierNo
            LEFT JOIN (
                SELECT id, purchaseOrderID
                FROM StockProducer
                WHERE dateClosed IS NOT NULL
                OR qtyReceived >= qtyOrdered
                GROUP BY purchaseOrderID
            ) closedItem ON po.OrderNo = closedItem.purchaseOrderID
            WHERE po.DatePrinted IS NULL
            AND po.autoAddItems = 1
            AND po.IntoStockLocation = :location
            AND pd.StockID = :sku
            AND closedItem.id is null
            $supplierMatches
            $versionMatches

            GROUP BY po.OrderNo

            ORDER BY pd.Preferred DESC, pc.unitCost ASC, po.OrderNo ASC
        ";

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(PurchaseOrder::class, 'po');
        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameters($parameters);

        /** @var PurchaseOrder[] $orders */
        $orders = $query->getResult();
        foreach ($orders as $order) {
            if ($order->canSupplyItem($item, $version)) {
                return $order;
            }
        }
        return null;
    }

    /** @return PurchaseOrder */
    public function findMostRecentOpenOrder(Supplier $supp)
    {
        $qb = $this->createQueryBuilder('po')
            ->innerJoin('po.items', 'pod')
            ->where('po.supplier = :supplier')
            ->andWhere('pod.dateClosed is null')
            ->setParameter('supplier', $supp->getId())
            ->groupBy('po.id')
            ->orderBy('po.orderDate', 'DESC')
            ->setMaxResults(1);
        $query = $qb->getQuery();
        return $query->getSingleResult();
    }

    /** @return PurchaseOrder[] */
    public function findOpenOrdersNeedingBuildFiles(BuildFiles $files)
    {
        $qb = $this->createQueryBuilder('po');
        $qb->join(WorkOrder::class, 'wo',
            'WITH', 'wo.purchaseOrder = po')
            ->join('wo.requirements', 'req')
            ->where('req.stockItem = :stockCode')
            ->setParameter('stockCode', $files->getSku())
            ->andWhere('req.version = :version')
            ->setParameter('version', (string) $files->getVersion())
            ->andWhere('wo.dateClosed is null');
        $query = $qb->getQuery();
        return $query->getResult();
    }

    /** @return PurchaseOrder[] */
    public function findOpenOrdersForProduction($rework)
    {
        return $this->createBuilder()
            ->isOpen()
            ->byRework($rework)
            ->hasSupplier()
            ->prefetchTasks()
            ->orderByRequestedDate()
            ->getResult();
    }

    /** @return PurchaseOrder[] */
    public function findOpenOrders()
    {
        return $this->createBuilder()
            ->isOpen()
            ->orderByRequestedDate()
            ->getResult();
    }

    /** @return PurchaseOrder[] */
    public function findRequiredTasksForWarehouse()
    {
        return $this->createBuilder()
            ->isOpen()
            ->hasSupplier()
            ->prefetchTasks()
            ->byTaskRole(Role::WAREHOUSE)
            ->hasRequiredTasks()
            ->orderByRequestedDate()
            ->getResult();
    }

    /** @return PurchaseOrder[] */
    public function findOpenOrdersForWarehouse()
    {
        return $this->createBuilder()
            ->isOpen()
            ->hasSupplier()
            ->prefetchTasks()
            ->byTaskRole(Role::WAREHOUSE)
            ->requiredTasksFirst()
            ->getResult();
    }

    /**
     * @return PurchaseOrder[]
     */
    public function findBySupplier(Supplier $supplier, $rework = false)
    {
        return $this->createBuilder()
            ->isOpen()
            ->bySupplier($supplier)
            ->byRework($rework)
            ->prefetchTasks()
            ->orderByRequestedDate()
            ->getResult();
    }

    /**
     * @return int The number of open purchase orders
     */
    public function countBySupplier(Supplier $supplier, $rework = false)
    {
        return $this->createBuilder()
            ->isOpen()
            ->bySupplier($supplier)
            ->byRework($rework)
            ->getCount();
    }

    /** @return QueryBuilder */
    private function deepQuery()
    {
        $qb = $this->createQueryBuilder('po');
        $qb->select(
            'po',
            'wo',
            'pd',
            'product',

            'req',
            'component',
            'alloc'
        )
            ->join(WorkOrder::class, 'wo', Join::WITH,
                'wo.purchaseOrder = po')
            ->join('wo.purchasingData', 'pd')
            ->join('pd.stockItem', 'product')
            ->leftJoin('wo.requirements', 'req')
            ->leftJoin('req.stockItem', 'component')
            ->leftJoin('req.allocations', 'alloc');
        return $qb;
    }

    /** @return PurchaseOrder */
    public function deepFetch($po)
    {
        $qb = $this->deepQuery();
        $qb->andWhere('po.id = :poID')
            ->setParameter('poID', $po);
        $result = $qb->getQuery()->getResult();
        return $result[0];
    }

    public function queryUninvoicedOrdersBySupplier(Supplier $supplier)
    {
        $qb = $this->createQueryBuilder('po');
        $qb->join('po.items', 'poItem')
            ->where('po.supplier = :supplierID')
            ->setParameter('supplierID', $supplier->getId())
            ->andWhere('po.datePrinted is not null')
            ->andWhere('poItem.qtyInvoiced < poItem.qtyOrdered');
        return $qb;
    }

    /**
     * Returns a list of purchase orders which are ready to be kitted and sent
     * to the manufacturer whose location is given.
     *
     * @param Facility $location
     * @return QueryBuilder
     */
    public function queryOrdersToKitByDestination(Facility $location)
    {
        return $this->createBuilder()
            ->needsKitting()
            ->byBuildLocation($location)
            ->getQueryBuilder();
    }

    /** @return string[][] */
    public function findOrdersToKitSummary()
    {
        $qb = $this->createBuilder()
            ->needsKitting()
            ->getQueryBuilder();

        $qb->join('po.buildLocation', 'location')
            ->select('location.name')
            ->addSelect('location.id')
            ->addSelect('count(distinct po.id) as numOrders')
            ->groupBy('po.buildLocation')
            ->andWhere('po.buildLocation != :hq')
            ->setParameter('hq', Facility::HEADQUARTERS_ID);
        return $qb->getQuery()->getResult();
    }

    /**
     * @return PurchaseOrder[]
     */
    public function findOpenByDeliveryLocation(Facility $loc)
    {
        return $this->createBuilder()
            ->isOpen()
            ->byDeliveryLocation($loc)
            ->getResult();
    }

    /** @return int */
    public function countOpenByDeliveryLocation(Facility $loc)
    {
        return $this->createBuilder()
            ->isOpen()
            ->byDeliveryLocation($loc)
            ->getCount();
    }

    /**
     * Returns orders that are "stale" according to the given definition.
     *
     * @return PurchaseOrder[]
     */
    public function findOrdersNeedingAttention(StaleOrderDefinition $params)
    {
        assertion(null !== $params->asOf);

        $qb = $this->createQueryBuilder('po');
        $qb->join(WorkOrder::class, 'wo', 'WITH', 'wo.purchaseOrder = po')
            ->andWhere('po.buildLocation = :location')
            ->setParameter('location', $params->location)
            ->andWhere('wo.dateClosed is null')
            ->setParameter('hours', $params->age)
            ->andWhere('po.datePrinted < :asOf')
            ->setParameter('asOf', $params->asOf)
            ->join('po.tasks', 'task')
            ->andWhere('task.status = :required')
            ->setParameter('required', ProductionTask::REQUIRED)
            ->andWhere('task.roles like :supplier')
            ->setParameter('supplier', '%' . Role::SUPPLIER_ADVANCED . '%')
            ->groupBy('po.id')
            ->andHaving("TIMESTAMPDIFF(HOUR, max(wo.dateUpdated), :asOf) >= :hours");

        if ($params->inProgress) {
            $qb->andWhere('wo.qtyReceived < wo.qtyOrdered');
        } else { // only orders that have not started yet
            $qb->andHaving('max(wo.qtyReceived) = 0');
        }

        if (null !== $params->rework) {
            $qb->andWhere('wo.rework = :rework')
                ->setParameter('rework', $params->rework);
        }
        $query = $qb->getQuery();
        return $query->getResult();
    }

    /**
     * @param string|int $id
     * @throws \InvalidArgumentException
     */
    public function get($id): PurchaseOrder
    {
        /** @var PurchaseOrder|null $purchaseOrder */
        $purchaseOrder = $this->find($id);
        if ($purchaseOrder === null) {
            throw new \InvalidArgumentException(
                "PO \"$id\" not found."
            );
        }
        return $purchaseOrder;
    }

    public function save(PurchaseOrder $order): void
    {
        $this->_em->persist($order);
    }

    public function delete(PurchaseOrder $order): void
    {
        $this->_em->remove($order);
    }
}
