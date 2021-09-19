<?php

namespace Rialto\Manufacturing\WorkOrder;

use Rialto\Purchasing\Producer\StockProducerEvent;

/**
 * This event is fired when something happens to a work order.
 */
class WorkOrderEvent extends StockProducerEvent
{
    public function __construct(WorkOrder $workOrder)
    {
        parent::__construct($workOrder);
    }

    /** @return WorkOrder */
    public function getOrder()
    {
        return $this->getProducer();
    }

}
