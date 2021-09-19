<?php

namespace Rialto\Purchasing\Order;

use Rialto\Manufacturing\BuildFiles\BuildFilesRequest;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event related to a purchase order.
 */
class PurchaseOrderEvent extends Event implements BuildFilesRequest
{
    /** @var PurchaseOrder */
    private $order;

    public function __construct(PurchaseOrder $order)
    {
        $this->order = $order;
    }

    /** @return PurchaseOrder */
    public function getOrder()
    {
        return $this->order;
    }

    public function getItemsNeedingBuildFiles()
    {
        $items = [];
        foreach ($this->order->getLineItems() as $poItem) {
            if ($poItem->isWorkOrder()) {
                /** @var WorkOrder $poItem */
                foreach ($poItem->getRequirements() as $woReq) {
                    if ($woReq->isPCB()) $items[] = $woReq;
                }
            } elseif ($poItem->isPCB()) {
                $items[] = $poItem;
            }
        }
        return $items;
    }
}
