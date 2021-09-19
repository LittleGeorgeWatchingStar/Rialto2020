<?php

namespace Rialto\Allocation\Status;


use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Allocation\Requirement\RequirementInterface;
use Rialto\Stock\Facility\Facility;

class RequirementStatus implements AllocationStatus
{
    /** @var Facility */
    private $neededAt;

    private $qtyNeeded = 0;
    private $qtyDelivered = 0;
    private $qtyAllocated = 0;
    private $qtyBinsAllocated = 0;
    private $qtyInTransitToLocation = 0;
    private $qtyAtLocation = 0;
    private $qtyAtWarehouse = 0;
    private $qtyOnOrderDirect = 0;


    /**
     * Factory method.
     * @return static
     */
    public static function forConsumer(StockConsumer $consumer)
    {
        $status = new static($consumer->getLocation());
        foreach ($consumer->getRequirements() as $req) {
            $status->addRequirement($req);
        }
        return $status;
    }

    /**
     * Factory method.
     * @return static
     */
    public static function forRequirement(RequirementInterface $requirement)
    {
        $status = new static($requirement->getFacility());
        $status->addRequirement($requirement);
        return $status;
    }

    /**
     * @param Facility $neededAt
     *  The location at which the stock is ultimately needed.
     */
    public function __construct(Facility $neededAt)
    {
        $this->neededAt = $neededAt;
    }

    public function addRequirement(RequirementInterface $requirement)
    {
        $this->qtyNeeded += $requirement->getTotalQtyOrdered();
        $this->qtyDelivered += $requirement->getTotalQtyDelivered();
        foreach ($requirement->getAllocations() as $alloc) {
            $this->addAllocation($alloc);
        }
    }

    protected function addAllocation(StockAllocation $alloc)
    {
        $allocQty = $alloc->getQtyAllocated();
        $this->qtyAllocated += $allocQty;

        if ($alloc->isFromStock()) {
            $this->qtyBinsAllocated += $allocQty;

            if ($alloc->isAtLocation($this->neededAt)) {
                $this->qtyAtLocation += $allocQty;
            } elseif ($alloc->isInTransitTo($this->neededAt)) {
                $this->qtyInTransitToLocation += $allocQty;
            } else {
                $this->qtyAtWarehouse += $allocQty;
            }
        } elseif ($alloc->isOnOrderTo($this->neededAt)) {
            $this->qtyOnOrderDirect += $allocQty;
        }
    }

    public function isFullyAllocated()
    {
        return $this->qtyDelivered
            + $this->qtyAllocated
            >= $this->qtyNeeded;
    }

    public function isStartedToBeAllocated()
    {
        return $this->qtyAtWarehouse > 0;
    }

    public function isFullyStocked()
    {
        return $this->qtyDelivered
            + $this->qtyBinsAllocated
            >= $this->qtyNeeded;
    }

    public function getQtyReadyToKit()
    {
        return $this->qtyDelivered
            + $this->qtyBinsAllocated
            + $this->qtyOnOrderDirect;
    }

    public function isReadyToKit()
    {
        return $this->getQtyReadyToKit() >= $this->qtyNeeded;
    }

    /**
     * True if all parts are either at the destination or are in transit.
     * @return boolean
     */
    public function isEnRoute()
    {
        return $this->qtyDelivered
            + $this->qtyAtLocation
            + $this->qtyInTransitToLocation
            + $this->qtyOnOrderDirect
            >= $this->qtyNeeded;
    }

    public function getQtyKitComplete()
    {
        return $this->getQtyAtLocation();
    }

    public function isKitComplete()
    {
        return $this->getQtyKitComplete() >= $this->qtyNeeded;
    }

    public function getQtyNeeded()
    {
        return $this->qtyNeeded;
    }

    public function getQtyDelivered()
    {
        return $this->qtyDelivered;
    }

    public function getQtyAllocated()
    {
        return $this->qtyAllocated + $this->qtyDelivered;
    }

    public function getQtyUnallocated()
    {
        return $this->qtyNeeded - $this->getQtyAllocated();
    }

    public function getQtyInTransitToLocation()
    {
        return $this->qtyInTransitToLocation;
    }

    public function getQtyAtWarehouse()
    {
        return $this->qtyAtWarehouse;
    }

    public function getQtyOnOrderDirect()
    {
        return $this->qtyOnOrderDirect;
    }

    public function getQtyAtLocation()
    {
        return $this->qtyDelivered + $this->qtyAtLocation;
    }

    public function getQtyInStock()
    {
        return $this->qtyBinsAllocated + $this->qtyDelivered;
    }

    public function getQtyBinsAllocated()
    {
        return $this->qtyBinsAllocated;
    }

    /**
     * Represents 'in stock' in the views, {@see AllocationExtension::consumerStatus()}
     */
    public function getNetQtyAtLocation()
    {
        return $this->qtyAtLocation;
    }

    public function getNetQtyAllocated()
    {
        return $this->qtyAllocated;
    }
}
