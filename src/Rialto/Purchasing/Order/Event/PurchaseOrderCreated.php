<?php

namespace Rialto\Purchasing\Order\Event;


use Rialto\Entity\DomainEvent;
use Rialto\Purchasing\Order\PurchaseOrder;
use Symfony\Component\EventDispatcher\Event;

/**
 * When a PO is first created.
 */
final class PurchaseOrderCreated extends Event implements DomainEvent
{
    /** @var PurchaseOrder */
    private $purchaseOrder;

    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    public function getEventName()
    {
        return get_class($this);
    }

    public function getPurchaseOrder(): PurchaseOrder
    {
        return $this->purchaseOrder;
    }
}
