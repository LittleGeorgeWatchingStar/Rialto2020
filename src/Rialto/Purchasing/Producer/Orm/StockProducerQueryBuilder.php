<?php

namespace Rialto\Purchasing\Producer\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Allocation\Allocation\ProducerAllocation;
use Rialto\Allocation\Requirement\RequirementInterface;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\VersionedItem;

class StockProducerQueryBuilder
{
    /** @var QueryBuilder */
    private $qb;

    public function __construct(StockProducerRepository $repo)
    {
        $this->qb = $repo->createQueryBuilder('prod');
    }

    public function getQueryBuilder()
    {
        return clone $this->qb;
    }

    public function getResult()
    {
        return $this->qb->getQuery()->getResult();
    }

    /**
     * @deprecated Use isOpen() instead
     */
    public function open()
    {
        return $this->isOpen();
    }

    public function isOpen()
    {
        $this->qb->andWhere('prod.dateClosed is null')
            ->andWhere('prod.qtyReceived < prod.qtyOrdered');
        return $this;
    }

    public function openForAllocation()
    {
        $this->isOpen();
        $this->qb->andWhere('prod.openForAllocation = 1');
        if ($this->isForWorkOrder()) {
            /* Only the parent can allocate from a child work order. */
            $this->qb->andWhere('prod.parent is null');
        }
        return $this;
    }

    /**
     * Filter out PurchaseOrders that we should not auto-allocate from.
     * @return $this
     */
    public function canAutoAllocate()
    {
        $this->qb->andWhere('po.autoAddItems = 1');
        return $this;
    }

    public function forItem(Item $item)
    {
        $this->qb->join('prod.purchasingData', 'pd')
            ->andWhere('pd.stockItem = :item')
            ->setParameter('item', $item->getSku());
        return $this;
    }

    public function forVersion(Version $version)
    {
        if ($version->isSpecified() ) {
            $this->qb->andWhere('prod.version = :version')
                ->setParameter('version', (string) $version);
        }
        return $this;
    }

    public function forVersionedItem(VersionedItem $item)
    {
        $this->forItem($item);
        $this->forVersion($item->getVersion());
        if ($this->isForWorkOrder()) {
            $this->forCustomization($item->getCustomization());
        }
        return $this;
    }

    private function isForWorkOrder()
    {
        return in_array(WorkOrder::class, $this->qb->getRootEntities());
    }

    public function forCustomization(Customization $cmz = null)
    {
        if ($cmz) {
            $this->qb->andWhere('prod.customization = :cmzID')
                ->setParameter('cmzID', $cmz->getId());
        } else {
            $this->qb->andWhere('prod.customization is null');
        }
        return $this;
    }

    /**
     * Limits the results to those producers that are being delivered either
     * to the warehouse or directly to the requirement's CM.
     */
    public function byRequirementLocation(RequirementInterface $requirement)
    {
        $this->qb->join('prod.purchaseOrder', 'po')
            ->andWhere('po.deliveryLocation in (:locations)')
            ->setParameter('locations', [
                $requirement->getFacility(),
                Facility::HEADQUARTERS_ID
            ]);
        return $this;
    }

    public function byDeliveryLocation(Facility $location)
    {
        $this->qb->join('prod.purchaseOrder', 'po')
            ->andWhere('po.deliveryLocation = :location')
            ->setParameter('location', $location);
        return $this;
    }

    /** @return int|float */
    public function getQtyOnOrder()
    {
        $this->qb->select('sum(prod.qtyOrdered - prod.qtyReceived)')
            ->setMaxResults(1);
        return $this->qb->getQuery()->getSingleScalarResult();
    }

    public function isAllocatedFromOrder(PurchaseOrder $po)
    {
        $this->qb->join(WorkOrder::class, 'wo', 'WITH', 'prod = wo')
            ->join('wo.requirements', 'woReq')
            ->join('woReq.allocations', 'alloc')
            ->join(ProducerAllocation::class, 'palloc', 'WITH', 'alloc = palloc')
            ->join('palloc.source', 'source')
            ->andWhere('source.purchaseOrder = :partsPo')
            ->setParameter('partsPo', $po);
        return $this;
    }
}
