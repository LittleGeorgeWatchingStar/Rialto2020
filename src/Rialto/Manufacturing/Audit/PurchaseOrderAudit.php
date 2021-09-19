<?php

namespace Rialto\Manufacturing\Audit;

use Rialto\Manufacturing\ManufacturingEvents;
use Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssueEvent;
use Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssuer;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Supplier\SupplierEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Creates and manages a collection of WorkOrderAudit objects
 * for the work orders in a purchase order.
 */
class PurchaseOrderAudit
{
    /** @var PurchaseOrder */
    private $po;

    /**
     * @var AuditItem[]
     * @Assert\Valid(traverse=true)
     */
    private $items = [];

    /** @var EventDispatcherInterface */
    private $dispatcher;

    private $sendEmail = true;

    public function __construct(PurchaseOrder $po,
                                EventDispatcherInterface $dispatcher)
    {
        $this->po = $po;
        $this->dispatcher = $dispatcher;
        foreach ($po->getWorkOrders() as $wo) {
            foreach ($wo->getRequirements() as $req) {
                if ($req->isProvidedByChild()) {
                    continue;
                }
                $key = $req->getFullSku();
                if (!isset($this->items[$key])) {
                    $this->items[$key] = new AuditItem($po->getBuildLocation());
                }
                $this->items[$key]->addRequirement($req);
            }
        }
        ksort($this->items); // sort by SKU
    }

    public function __toString()
    {
        return "audit {$this->po}";
    }

    public function getPurchaseOrder(): PurchaseOrder
    {
        return $this->po;
    }

    /** @return AuditItem[] */
    public function getItems()
    {
        return array_values($this->items);
    }

    /**
     * @param boolean $sendEmail
     */
    public function setSendEmail($sendEmail)
    {
        $this->sendEmail = $sendEmail;
    }

    /**
     * @return boolean
     */
    public function isSendEmail()
    {
        return $this->sendEmail;
    }

    /**
     * Adjust allocations to match the shortages (or lack thereof)
     * entered by the CM.
     */
    public function adjustAllocations(AuditAdjuster $adjuster)
    {
        foreach ($this->items as $item) {
            $adjuster->adjustAllocations($item);
        }
    }

    public function getWarnings()
    {
        return array_filter(array_map(function (AuditItem $item) {
            return $item->getFailureDescription();
        }, $this->items));
    }

    public function sendNotifications()
    {
        $event = new AuditEvent($this);
        $this->dispatcher->dispatch(SupplierEvents::AUDIT, $event);

        if ($this->hasAdjustments() && $this->hasShortages()) {
            $this->dispatcher->dispatch(ManufacturingEvents::PURCHASE_ORDER_SHORTAGE, $event);
        }
    }

    private function hasAdjustments()
    {
        foreach ($this->items as $item) {
            if ($item->hasAdjustment()) {
                return true;
            }
        }
        return false;
    }

    public function hasShortages()
    {
        $status = $this->po->getAllocationStatus();
        return !$status->isKitComplete();
    }

    /**
     * @return bool True if all of the child work orders are kit complete
     * and ready to be issued.
     *
     * The parent work orders can be issued at receipt time.
     */
    public function childrenAreKitComplete()
    {
        foreach ($this->getChildWorkOrders() as $wo) {
            if (!$wo->isKitComplete()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Issue all unissued child work orders.
     *
     * The parent work orders can be issued at receipt time.
     */
    public function issueChildOrders(WorkOrderIssuer $issuer)
    {
        foreach ($this->getChildWorkOrders() as $wo) {
            $toIssue = $wo->getQtyUnissued();
            if ($toIssue <= 0) {
                continue;
            }
            $issue = $issuer->issue($wo, $toIssue);
            $event = new WorkOrderIssueEvent($issue);
            $this->dispatcher->dispatch(ManufacturingEvents::WORK_ORDER_ISSUE, $event);
        }
    }

    /** @return WorkOrder[] */
    private function getChildWorkOrders()
    {
        return array_filter($this->po->getWorkOrders(), function (WorkOrder $wo) {
            return !$wo->hasChild();
        });
    }
}
