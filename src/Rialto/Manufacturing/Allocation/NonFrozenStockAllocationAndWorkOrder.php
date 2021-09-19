<?php

namespace Rialto\Manufacturing\Allocation;

use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Stock\Facility\Facility;

/**
 * Data Class of Non Frozen Stock Allocation and Work Order
 */
class NonFrozenStockAllocationAndWorkOrder
{
    /** @var WorkOrder */
    private $workOrder;
    /** @var string */
    private $location;
    private $qty;
    /** @var \DateTime */
    private $date;
    /** @var StockAllocation */
    private $stockAllocation;


    public function __construct(?WorkOrder $workOrder, $qty, ?\DateTime $date, StockAllocation $stockAllocation)
    {
        $this->workOrder = $workOrder;
        $this->location = $workOrder->getLocation()->getName();
        $this->qty = $qty;
        $this->date = $date;
        $this->stockAllocation = $stockAllocation;
    }

    public function getWorkOrder()
    {
        return $this->workOrder;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getWorkOrderId()
    {
        return $this->workOrder->getId();
    }

    public function getPurchaseOrderId()
    {
        return $this->workOrder->getPurchaseOrder()->getId();
    }

    public function getQty()
    {
        return $this->qty;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getStockAllocation()
    {
        return $this->stockAllocation;
    }
}

