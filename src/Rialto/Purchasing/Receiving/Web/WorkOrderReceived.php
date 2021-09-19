<?php

namespace Rialto\Purchasing\Receiving\Web;

use Rialto\Manufacturing\Allocation\WorkOrderStatus;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Subclass of ItemReceived for receiving a work order.
 */
class WorkOrderReceived
extends StockReceived
{
    public function __construct(WorkOrder $wo)
    {
        parent::__construct($wo);
    }

    /**
     * For work orders, make sure that all of the requirements are fully
     * allocated.
     *
     * @Assert\Callback
     */
    public function validateAllocationStatus(ExecutionContextInterface $context)
    {
        $status = new WorkOrderStatus($this->poItem);

        // $needed = previously received + receiving now
        $needed = $this->poItem->getQtyReceived() + $this->getTotalReceived();
        $found = $status->getQtyAtLocation();
        if ($found < $needed) {
            $neededAt = $this->poItem->getLocation();
            $item = $this->poItem->getSku();
            $context->addViolation("Not enough stock for $item is allocated at $neededAt. 
            (Found $found out of $needed)");
        }
    }
}
