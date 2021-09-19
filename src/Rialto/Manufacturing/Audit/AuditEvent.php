<?php

namespace Rialto\Manufacturing\Audit;

use Rialto\Purchasing\Order\PurchaseOrder;
use Symfony\Component\EventDispatcher\Event;

class AuditEvent extends Event
{
    /** @var PurchaseOrderAudit */
    private $audit;

    public function __construct(PurchaseOrderAudit $audit)
    {
        $this->audit = $audit;
    }

    public function getPurchaseOrder(): PurchaseOrder
    {
        return $this->audit->getPurchaseOrder();
    }

    /** @return AuditItem[] */
    public function getAdjustedItems()
    {
        return array_filter($this->audit->getItems(), function (AuditItem $i) {
            return $i->hasAdjustment();
        });
    }

    /** @return AuditItem[] */
    public function getShortItems()
    {
        return array_filter($this->audit->getItems(), function (AuditItem $i) {
            return $i->getQtyShort() > 0;
        });
    }

    public function isSendEmail()
    {
        return $this->audit->isSendEmail();
    }
}
