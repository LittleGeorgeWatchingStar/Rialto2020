<?php

namespace Rialto\Manufacturing\PurchaseOrder;


use Rialto\Manufacturing\WorkOrder\WorkOrderFamily;
use Rialto\Purchasing\Order\PurchaseOrder;

/**
 * An index of build POs, keyed by order status
 */
class OrderStatusIndex implements \IteratorAggregate
{
    private $index = [];

    /**
     * @param PurchaseOrder[] $orders
     */
    public function __construct(array $orders)
    {
        $newOrders = [];
        $onHold = [];
        $audit = [];
        $inProduction = [];
        $receiving = [];

        $priority = 1;
        foreach ($orders as $po) {
            assertion($po instanceof PurchaseOrder);
            $family = WorkOrderFamily::fromPurchaseOrder($po);
            $child = $family->getChild();
            if (!$po->isSent()) {
                $newOrders[] = $po;
            } elseif (!$family->isApprovedToBuild()) {
                $onHold[] = $po;
                $po->setPriority($priority++);
            } elseif (!$family->isIssued()) {
                $audit[] = $po;
                $po->setPriority($priority++);
            } elseif ($child->getQtyReceived() == 0) {
                $inProduction[] = $po;
                $po->setPriority($priority++);
            } else {
                $receiving[] = $po;
            }
        }
        $this->index = [
            'new orders' => $newOrders,
            'on hold' => $onHold,
            'audit' => $audit,
            'in production' => $inProduction,
            'partially shipped' => $receiving,
        ];
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->index);
    }
}
