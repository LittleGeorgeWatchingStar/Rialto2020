<?php

namespace Rialto\Allocation\Status;

use DateTime;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Web\AllocationExtension;
use Rialto\Purchasing\Producer\StockProducer;

/**
 * Adds additional information about stock producers to
 * the base RequirementStatus.
 */
class DetailedRequirementStatus extends RequirementStatus
{
    private $qtyOrdered = 0;
    private $qtyToOrder = 0;

    /** @var StockProducer[] */
    private $producers = [];

    protected function addAllocation(StockAllocation $alloc)
    {
        parent::addAllocation($alloc);
        if ($alloc->isFromProducer()) {
            $allocQty = $alloc->getQtyAllocated();
            /** @var StockProducer $producer */
            $producer = $alloc->getSource();
            $this->producers[] = $producer;
            if ($producer->isOrderSent()) {
                $this->qtyOrdered += $allocQty;
            } else {
                $this->qtyToOrder += $allocQty;
            }
        }
    }

    public function getNetQtyOrdered()
    {
        return $this->qtyOrdered;
    }

    public function getNetQtyToOrder()
    {
        return $this->qtyToOrder;
    }

    public function getNetQtyElsewhere()
    {
        return $this->getQtyBinsAllocated() - $this->getNetQtyAtLocation();
    }

    /**
     * Represents 'in stock' in the views, {@see AllocationExtension::consumerStatus()}
     */
    public function getNetQtyAtLocation()
    {
        return parent::getNetQtyAtLocation();
    }

    /**
     * @return DateTime|null
     *  The latest commitment date by which we can expect to have this
     *  part/product in stock, or null if we can't make any guarantees.
     */
    public function getLatestCommitmentDate()
    {
        $latest = null;
        foreach ($this->producers as $producer) {
            $date = $producer->getCommitmentDate();
            /* If any producer does not have a commitment date, then we
             * can't make any promises about when parts/products will arrive. */
            if (!$date) {
                return null;
            } elseif (!$latest) {
                $latest = $date;
            } elseif ($date > $latest) {
                $latest = $date;
            }
        }
        return $latest;
    }

    /**
     * @return StockProducer[]
     */
    public function getProducers()
    {
        return $this->producers;
    }

    /** @return StockProducer[] */
    public function getSentProducers()
    {
        return array_filter($this->producers, function (StockProducer $p) {
            return $p->isOrderSent();
        });
    }

    /** @return StockProducer[] */
    public function getUnsentProducers()
    {
        return array_filter($this->producers, function (StockProducer $p) {
            return !$p->isOrderSent();
        });
    }
}
