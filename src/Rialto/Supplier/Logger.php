<?php

namespace Rialto\Supplier;

use Psr\Log\LoggerInterface;
use Rialto\Allocation\Allocation\StockAllocationEvent;
use Rialto\Allocation\AllocationEvents;
use Rialto\Manufacturing\Audit\AuditEvent;
use Rialto\Manufacturing\Audit\AuditItem;
use Rialto\Manufacturing\ManufacturingEvents;
use Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssueEvent;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\Event\PurchaseOrderApproved;
use Rialto\Purchasing\Order\Event\PurchaseOrderRejected;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Order\PurchaseOrderEvent;
use Rialto\Purchasing\Producer\StockProducerEvent;
use Rialto\Stock\Item;
use Rialto\Stock\StockEvents;
use Rialto\Stock\Transfer\TransferEvent;
use Rialto\Stock\Transfer\Web\TransferReceipt;
use Rialto\Supplier\Order\AdditionalPart;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Logs supplier dashboard events.
 *
 * Detailed logging of what contract manufacturers do on the dashboard is
 * important so that we can trace problems and hold them accountable.
 */
class Logger implements EventSubscriberInterface
{
    /** @var LoggerInterface */
    private $logger;

    public static function getSubscribedEvents()
    {
        return [
            SupplierEvents::ADDITIONAL_PART => 'additionalPart',
            PurchaseOrderApproved::class => 'approveOrder',
            PurchaseOrderRejected::class => 'rejectOrder',
            SupplierEvents::SUPPLIER_REFERENCE => 'supplierReference',
            AllocationEvents::ALLOCATION_CHANGE => 'allocationChange',
            StockEvents::TRANSFER_SENT => 'transferSent',
            StockEvents::TRANSFER_RECEIPT => 'transferReceipt',
            SupplierEvents::COMMITMENT_DATE => 'commitmentDate',
            SupplierEvents::AUDIT => 'audit',
            ManufacturingEvents::WORK_ORDER_ISSUE => 'workOrderIssue',
        ];
    }

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function additionalPart(AdditionalPart $part)
    {
        $workOrder = $part->getWorkOrder();
        $context = $this->createContext($workOrder->getPurchaseOrder(), $part);
        $context['unitQty'] = $part->getUnitQty();
        $context['scrapCount'] = $part->getScrapCount();
        $msg = "Additional $part requested for $workOrder";
        $this->logger->notice($msg, $context);
    }

    public function approveOrder(PurchaseOrderApproved $event)
    {
        $order = $event->getPurchaseOrder();
        $reason = $order->getApprovalReason();
        $message = "$order approved" . ($reason ? ": $reason" : '');
        $context = $this->createContext($order);
        $this->logger->notice($message, $context);
    }

    public function rejectOrder(PurchaseOrderRejected $event)
    {
        $order = $event->getPurchaseOrder();
        $reason = $order->getApprovalReason();
        $message = "$order rejected" . ($reason ? ": $reason" : '');
        $context = $this->createContext($order);
        $this->logger->notice($message, $context);
    }

    public function supplierReference(PurchaseOrderEvent $event)
    {
        $order = $event->getOrder();
        $msg = sprintf('Supplier reference for %s updated to "%s"',
            $order,
            $order->getSupplierReference());
        $this->logger->info($msg, $this->createContext($order));
    }

    public function allocationChange(StockAllocationEvent $event)
    {
        $consumer = $event->getConsumer();
        if ($consumer instanceof WorkOrder) {
            $po = $consumer->getPurchaseOrder();
            $alloc = $event->getAllocation();
            $msg = sprintf('Allocation of %s from %s for %s updated to %s',
                $alloc->getSku(),
                $alloc->getSourceDescription(),
                $alloc->getConsumerDescription(),
                number_format($alloc->getQtyAllocated()));
            $context = $this->createContext($po, $alloc->getSource());
            $this->logger->info($msg, $context);
        }
    }

    public function transferSent(TransferEvent $event)
    {
        $transfer = $event->getTransfer();
        $pickedUpBy = $transfer->getPickedUpBy();
        $msg = $pickedUpBy
            ? ucfirst("$transfer was picked up by $pickedUpBy")
            : "Sent $transfer";
        $orders = $transfer->getPurchaseOrders();
        $context = $this->createContext($orders);
        $this->logger->notice($msg, $context);
    }

    public function transferReceipt(TransferReceipt $receipt)
    {
        $transfer = $receipt->getTransfer();
        $orders = $transfer->getPurchaseOrders();

        foreach ($transfer->getLineItems() as $item) {
            $sent = $item->getQtySent();
            $recd = $item->getQtyReceived();
            $bin = $item->getStockBin();
            $msg = "Received $recd of $sent on $bin via $transfer";
            $context = $this->createContext($orders, $item);
            $this->logger->notice($msg, $context);
        }

        foreach ($receipt->getExtraItems() as $newItem) {
            $bin = $newItem->getStockBin();
            $msg = "$transfer arrived with $bin extra";
            $context = $this->createContext($orders, $newItem);
            $this->logger->notice($msg, $context);
        }
    }

    public function commitmentDate(StockProducerEvent $event)
    {
        $poItem = $event->getProducer();
        $date = $poItem->getCommitmentDate();
        if (!$date) {
            return;
        }
        $order = $poItem->getPurchaseOrder();
        $msg = sprintf('Commitment date for %s updated to %s',
            $order, $date->format('Y-m-d'));
        $this->logger->notice($msg, $this->createContext($order));
    }

    public function audit(AuditEvent $event)
    {
        foreach ($event->getAdjustedItems() as $item) {
            $this->auditItem($item);
        }
    }

    private function auditItem(AuditItem $item)
    {
        $adjustment = $item->getAdjustment();
        assertion(0 != $adjustment);

        $sku = $item->getSku();
        $context = $this->createContext($item->getPurchaseOrder(), $item);
        if ($item->isSuccessful()) {
            $msg = "$sku adjusted by $adjustment";
            $this->logger->notice($msg, $context);
        } else {
            $context['reason'] = $item->getFailureReason();
            $msg = "unable to adjust $sku by $adjustment";
            $this->logger->warning($msg, $context);
        }
    }

    public function workOrderIssue(WorkOrderIssueEvent $event)
    {
        $issue = $event->getIssue();
        $workOrder = $event->getProducer();
        $context = $this->createContext($workOrder->getPurchaseOrder(), $workOrder);
        $msg = sprintf("Issued %s units of %s",
            number_format($issue->getQtyIssued()),
            $workOrder);
        $this->logger->info($msg, $context);
    }

    /**
     * Adds info about the PO and/or stock item to the log context.
     * @param PurchaseOrder[]|PurchaseOrder|null $POs
     * @return string[]
     */
    private function createContext($POs = null, Item $item = null)
    {
        $context = [];
        if ($POs) {
            $context['tags']['po'] = $this->createPOTag($POs);
        }
        if ($item) {
            $context['tags']['stockItem'] = $item->getSku();
        }
        return $context;
    }

    private function createPOTag($POs)
    {
        if (!is_array($POs)) {
            $POs = [$POs];
        }
        return array_map(function (PurchaseOrder $po) {
            /* Type cast to string is important -- Mongo is type-sensitive. */
            return (string) $po->getId();
        }, $POs);
    }
}
