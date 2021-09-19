<?php

namespace Rialto\Manufacturing\WorkOrder;

use Symfony\Component\EventDispatcher\Event;

class WorkOrderCreatedEvent extends Event
{
    private $workOrder;

    public function __construct(WorkOrder $workOrder)
    {
        $this->workOrder = $workOrder;
    }

    /** @return WorkOrder */
    public function getWorkOrder()
    {
        return $this->workOrder;
    }
}
