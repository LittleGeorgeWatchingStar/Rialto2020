<?php

namespace Rialto\Allocation\Status;

use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Allocation\Requirement\Requirement;

/**
 * Determines the allocation status of an entire StockConsumer.
 */
class ConsumerStatus implements AllocationStatus
{
    private $neededAt;
    private $qtyNeeded = 0;
    private $bomQty = [];

    /** @var RequirementStatus[] */
    private $reqStatus = [];

    public function __construct(StockConsumer $consumer)
    {
        $this->neededAt = $consumer->getLocation();
        $this->qtyNeeded = $consumer->getQtyOrdered();
        $this->addRequirements($consumer);
    }

    protected function addRequirements(StockConsumer $consumer)
    {
        foreach ( $consumer->getRequirements() as $req ) {
            $this->addRequirement($req);
        }
    }

    protected function addRequirement(Requirement $req)
    {
        $key = $req->getSku();
        $this->bomQty[$key] = $req->getUnitQtyNeeded();
        $reqStatus = new RequirementStatus($this->neededAt);
        $reqStatus->addRequirement($req);
        $this->reqStatus[$key] = $reqStatus;
    }

    public function getQtyNeeded()
    {
        return $this->qtyNeeded;
    }

    public function getQtyAllocated()
    {
        return $this->getQty(__FUNCTION__);
    }

    public function getQtyUnallocated()
    {
        return $this->getQtyNeeded() - $this->getQtyAllocated();
    }

    public function getQtyAtLocation()
    {
        return $this->getQty(__FUNCTION__);
    }

    public function getQtyInTransitToLocation()
    {
        return $this->getQty(__FUNCTION__);
    }

    private function getQtyInStock()
    {
        return $this->getQty(__FUNCTION__);
    }

    public function getQtyDelivered()
    {
        return $this->getQty(__FUNCTION__);
    }

    public function getQtyOnOrderDirect()
    {
        return $this->getQty(__FUNCTION__);
    }

    private function getQty($method)
    {
        $assembledQty = null;

        foreach ( $this->reqStatus as $stockCode => $status ) {
            assertion($status instanceof RequirementStatus);
            $qty = $status->$method();
            $bomQty = $this->bomQty[$stockCode];
            if ( $bomQty == 0 ) {
                // This CAN happen, eg: scrap parts for a work order.
                continue;
            }
            $assemblyUnits = floor($qty / $bomQty);
            if ( null === $assembledQty ) {
                $assembledQty = $assemblyUnits;
            } else {
                $assembledQty = min($assembledQty, $assemblyUnits);
            }
        }
        return $assembledQty;
    }

    public function getQtyReadyToKit()
    {
        return $this->getQty(__FUNCTION__);
    }

    public function isReadyToKit()
    {
        foreach ($this->reqStatus as $status) {
            if (!$status->isReadyToKit()) {
                return false;
            }
        }
        return true;
    }

    public function getQtyKitComplete()
    {
        return $this->getQty(__FUNCTION__);
    }

    public function isKitComplete()
    {
        foreach ($this->reqStatus as $status) {
            if (!$status->isKitComplete()) {
                return false;
            }
        }
        return true;
    }

    public function isEnRoute()
    {
        foreach ($this->reqStatus as $status) {
            if (!$status->isEnRoute()) {
                return false;
            }
        }
        return true;
    }

    public function isFullyStocked()
    {
        foreach ($this->reqStatus as $status) {
            if (!$status->isFullyStocked()) {
                return false;
            }
        }
        return true;
    }

    public function isFullyAllocated()
    {
        foreach ($this->reqStatus as $status) {
            if (!$status->isFullyAllocated()) {
                return false;
            }
        }
        return true;
    }
}
