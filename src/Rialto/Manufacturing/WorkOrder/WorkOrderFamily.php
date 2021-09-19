<?php

namespace Rialto\Manufacturing\WorkOrder;

use Rialto\Purchasing\Order\PurchaseOrder;

/**
 * A work order "family" is a collection of WorkOrder objects that are
 * related to each other via parent-child relationships.
 *
 * The "parent" of the family is a build for the final desired product;
 * the "children" of the family are builds for the subcomponents of the parent.
 *
 * @see WorkOrder
 */
class WorkOrderFamily extends WorkOrderCollection
{
    /** @var WorkOrder */
    private $parent;

    /** @return WorkOrderFamily */
    public static function fromPurchaseOrder(PurchaseOrder $po)
    {
        $wos = $po->getWorkOrders();
        $wo = reset($wos);
        return new self($wo);
    }

    /** @return WorkOrderFamily */
    public static function fromWorkOrder(WorkOrder $order)
    {
        return new self($order);
    }

    /**
     * Any member of the family can be used as the constructor parameter.
     */
    public function __construct(WorkOrder $member)
    {
        $this->parent = $this->findParent($member);
        parent::__construct($this->getDescendants($this->parent));
    }

    private function findParent(WorkOrder $member): WorkOrder
    {
        while ($member->hasParent()) {
            $member = $member->getParent();
        }
        return $member;
    }

    /**
     * The parent and all of its children, grandchildren, etc.
     * @return WorkOrder[]
     */
    private function getDescendants(WorkOrder $parent): array
    {
        $current = $parent;
        $descendants = [$current];
        while ($current->hasChild()) {
            $current = $current->getChild();
            $descendants[] = $current;
        }
        return $descendants;
    }

    /**
     * The order at the top of the family tree.
     */
    public function getParent(): WorkOrder
    {
        return $this->parent;
    }

    /**
     * The order at the bottom of the family tree.
     *
     * For families with only one work order, this will be the same order
     * as returned by getParent().
     */
    public function getChild(): WorkOrder
    {
        $current = $this->parent;
        while ($current->hasChild()) {
            $current = $current->getChild();
        }
        return $current;
    }

    public function getPurchaseOrder(): PurchaseOrder
    {
        return $this->parent->getPurchaseOrder();
    }

    public function isApprovedToBuild(): bool
    {
        return $this->getChild()->isApprovedToBuild();
    }

}
