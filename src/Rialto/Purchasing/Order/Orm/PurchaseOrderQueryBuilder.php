<?php

namespace Rialto\Purchasing\Order\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Gumstix\Doctrine\HighLevelQueryBuilder;
use Gumstix\Time\DateRange;
use Rialto\Manufacturing\Task\ProductionTask;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Facility\Facility;

class PurchaseOrderQueryBuilder extends HighLevelQueryBuilder
{
    public function __construct(PurchaseOrderRepository $repo)
    {
        parent::__construct($repo, 'po');
        $this->qb
            ->leftJoin('po.items', 'lineItem')
            ->leftJoin('po.tasks', 'task');
    }

    /**
     * Execute a deep graph fetch of tasks and other related child
     * objects.
     */
    public function prefetchTasks()
    {
        $this->qb
            ->select(
                'po',
                'lineItem',
                'pd',
                'product',
                'version',
                'task'
            )
            ->join('lineItem.purchasingData', 'pd')
            ->join('pd.stockItem', 'product')
            ->leftJoin('product.versions', 'version');

        return $this;
    }

    public function byId($id)
    {
        $this->qb->andWhere('po.id = :id')
            ->setParameter('id', $id);
        return $this;
    }

    public function bySupplierReference($ref)
    {
        $this->qb->andWhere('po.supplierReference like :ref')
            ->setParameter('ref', "%$ref%");
        return $this;
    }

    /**
     * @param Supplier|string $supplier Supplier or supplier ID
     */
    public function bySupplier($supplier)
    {
        $this->qb->andWhere('po.supplier = :supplier')
            ->setParameter('supplier', $supplier);

        return $this;
    }

    public function bySku($sku)
    {
        $subquery = $this->createSubquery()
            ->select('1')
            ->from(PurchasingData::class, 'pd')
            ->andWhere('pd.stockItem = :sku')
            ->andWhere('lineItem.purchasingData = pd')
            ->getDQL();
        $this->qb->andWhere("exists ($subquery)")
            ->setParameter('sku', $sku);

        return $this;
    }

    private function createSubquery()
    {
        return $this->qb->getEntityManager()->createQueryBuilder();
    }

    public function byRework($rework)
    {
        $this->hasWorkOrder();
        $this->qb->andWhere('wo.rework = :rework')
            ->setParameter('rework', $rework);

        return $this;
    }

    public function byDeliveryLocation(Facility $location)
    {
        $this->qb
            ->andWhere('po.deliveryLocation = :deliveryLocation')
            ->setParameter('deliveryLocation', $location);

        return $this;
    }

    public function byBuildLocation(Facility $location)
    {
        $this->qb->andWhere('po.buildLocation = :location')
            ->setParameter('location', $location);
        return $this;
    }

    public function byDateOrdered(DateRange $range)
    {
        if ($range->hasStart()) {
            $this->qb->andWhere('date_diff(po.orderDate, :dateOrderedStart) >= 0')
                ->setParameter('dateOrderedStart', $range->getStart());
        }
        if ($range->hasEnd()) {
            $this->qb->andWhere('date_diff(po.orderDate, :dateOrderedEnd) <= 0')
                ->setParameter('dateOrderedEnd', $range->getEnd());
        }
        return $this;
    }

    /**
     * Select only open POs.
     */
    public function isOpen()
    {
        $this->qb
            ->distinct()
            ->andWhere('lineItem.dateClosed is null')
            ->andWhere('lineItem.qtyOrdered > lineItem.qtyReceived');

        return $this;
    }

    public function isClosed()
    {
        $subquery = $this->createSubquery()
            ->select('1')
            ->from(StockProducer::class, 'openItem')
            ->andWhere('openItem.dateClosed is null
                and openItem.qtyReceived < openItem.qtyOrdered')
            ->andWhere('lineItem = openItem')
            ->getDQL();

        $this->qb->andWhere("not exists ($subquery)");
        return $this;
    }

    /** @deprecated use isOpen() instead */
    public function open()
    {
        return $this->isOpen();
    }

    public function isSent()
    {
        $this->qb->andWhere('po.datePrinted is not null');
        return $this;
    }

    public function isNotSent()
    {
        $this->qb->andWhere('po.datePrinted is null');
        return $this;
    }

    /**
     * Only include POs with work orders.
     */
    public function hasWorkOrder()
    {
        $this->qb->join(WorkOrder::class, 'wo', Join::WITH,
            'lineItem = wo');

        return $this;
    }

    public function excludeInitiators(array $initiators)
    {
        $this->qb->andWhere('po.initiator not in (:exclude)')
            ->setParameter('exclude', $initiators);
        return $this;
    }

    /**
     * Work order POs that might need more parts to be sent to the CM.
     */
    public function needsKitting()
    {
        $this->hasWorkOrder();
        $this->qb->andWhere('wo.qtyIssued < wo.qtyOrdered')
            ->andWhere('wo.dateClosed is null');
        return $this;
    }

    public function hasSupplier()
    {
        $this->qb->andWhere('po.supplier is not null');

        return $this;
    }

    public function hasRequiredTasks()
    {
        $this->qb->andWhere('task.status = :task_required')
            ->setParameter('task_required', ProductionTask::REQUIRED);
        return $this;
    }

    public function byTaskRole($role)
    {
        $this->qb
            ->andWhere('task.roles like :task_role')
            ->setParameter('task_role', "%$role%");
        return $this;
    }

    /**
     * Sorts the results so POs with required tasks show up first.
     */
    public function requiredTasksFirst()
    {
        $this->qb->orderBy('if(task.status = :required, 0, 1)', 'asc')
            ->setParameter('required', ProductionTask::REQUIRED);

        return $this;
    }

    public function orderByRequestedDate()
    {
        $this->qb
            ->orderBy("IFNULL(lineItem.requestedDate, '9999-12-31')")
            ->addOrderBy('po.id');

        return $this;
    }

    public function getCount()
    {
        return $this->qb
            ->select('count(distinct po.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
