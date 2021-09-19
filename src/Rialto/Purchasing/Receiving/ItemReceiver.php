<?php

namespace Rialto\Purchasing\Receiving;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Company\Company;
use Rialto\Database\Orm\DbManager;
use Rialto\Exception\InvalidDataException;
use Rialto\Manufacturing\Allocation\WorkOrderParentAllocator;
use Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssue;
use Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssuer;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Stock\Bin\StockCreationEvent;
use Rialto\Stock\Move\StockMove;
use Rialto\Stock\StockEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use UnexpectedValueException;

/**
 * Processes the receipt of GoodsReceivedItems.
 *
 * @see GoodsReceivedItem
 */
class ItemReceiver
{
    /** @var DbManager */
    private $dbm;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var WorkOrderIssuer */
    private $woIssuer;

    /** @var AllocationFactory */
    private $allocationFactory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        DbManager $dbm,
        EventDispatcherInterface $dispatcher,
        WorkOrderIssuer $woIssuer,
        AllocationFactory $allocationFactory,
        LoggerInterface $logger)
    {
        $this->dbm = $dbm;
        $this->dispatcher = $dispatcher;
        $this->woIssuer = $woIssuer;
        $this->allocationFactory = $allocationFactory;
        $this->logger = $logger;
    }

    public function receiveItem(
        GoodsReceivedItem $grnItem,
        Transaction $transaction)
    {
        assertion($grnItem->getQtyReceived() > 0);

        $materialCost = 0;
        if ($grnItem->isWorkOrder()) {
            $this->receiveChildIfNeeded($grnItem, $transaction);
            $this->issueIfNeeded($grnItem);
            $materialCost = $this->doWipAccounting($grnItem, $transaction);
        }
        $this->createStockIfNeeded($grnItem, $transaction, $materialCost);
        $this->doPurchaseAccounting($grnItem, $transaction);

        $grnItem->updateProducer();
        $this->logger->notice(sprintf("Received %s x %s",
            number_format($grnItem->getQtyReceived()),
            $grnItem->getDescription()
        ));
    }

    private function createStockIfNeeded(
        GoodsReceivedItem $grnItem,
        Transaction $transaction,
        $materialCost)
    {
        if (!$grnItem->isStockItem()) {
            return null;
        }
        if ($grnItem->isDiscarded()) {
            return null;
        }

        $bin = $grnItem->createBin();
        $bin->setMaterialCost($materialCost);
        $this->dbm->persist($bin);
        $bin->applyNewQty($transaction);
        $this->dbm->flush(); // bins needs IDs before we can transfer allocations

        $event = new StockCreationEvent($grnItem->getProducer(), $bin);
        $this->dispatcher->dispatch(StockEvents::STOCK_CREATION, $event);
        $grnItem->setAllocationsReceived($event->getAllocations());
    }


    private function receiveChildIfNeeded(
        GoodsReceivedItem $grnItem,
        Transaction $transaction)
    {
        /** @var $wo WorkOrder */
        $wo = $grnItem->getProducer();
        assertion($wo instanceof WorkOrder);

        if (!$wo->hasChild()) {
            return;
        }

        $this->allocateForParent($wo);
        $child = $wo->getChild();
        $childReceipt = $grnItem->createChildReceipt($child);
        $this->receiveItem($childReceipt, $transaction);
    }


    private function allocateForParent(WorkOrder $parent)
    {
        $allocator = new WorkOrderParentAllocator($parent);
        $qtyAllocated = $allocator->allocate($this->allocationFactory);

        $this->logger->notice(sprintf(
            'Allocated %s units for packaging work order %s.',
            $qtyAllocated, $parent->getId()
        ));
    }


    private function issueIfNeeded(GoodsReceivedItem $grnItem)
    {
        /** @var $wo WorkOrder */
        $wo = $grnItem->getProducer();
        assertion($wo instanceof WorkOrder);

        $qtyRequested = $grnItem->getQtyReceived();
        $unreceived = $wo->getQtyIssued() - $wo->getQtyReceived();
        $qtyToIssue = $qtyRequested - $unreceived;

        if ($qtyToIssue > 0) {
            $this->woIssuer->issue($wo, $qtyToIssue);
        }
    }


    /**
     * @return float The unit value received.
     */
    private function doWipAccounting(GoodsReceivedItem $grnItem, Transaction $glTrans)
    {
        /** @var $wo WorkOrder */
        $wo = $grnItem->getProducer();
        assertion($wo instanceof WorkOrder);
        $totalValueReceived = $this->calculateTotalValueOfReceipt(
            $wo, $grnItem->getQtyReceived()
        );

        assertion($totalValueReceived > 0);
        $memo = sprintf('%s - Materials: %s x %s',
            $glTrans->getMemo(),
            $wo->getSku(),
            $grnItem->getQtyReceived());
        $glTrans->addEntry(
            $grnItem->getStockAccount(),
            $totalValueReceived,
            $memo
        );
        $glTrans->addEntry(
            GLAccount::fetchWorkInProcess($this->dbm),
            -$totalValueReceived,
            $memo
        );

        return $totalValueReceived / $grnItem->getQtyReceived();
    }


    private function calculateTotalValueOfReceipt(WorkOrder $order, $qtyReceived)
    {
        $totalValueReceived = 0.0;
        $qtyLeftToReceive = $qtyReceived;

        $issues = $order->getIssues();
        foreach ($issues as $issue) {
            if ($qtyLeftToReceive <= 0) {
                break;
            }
            $qtyToReceive = min($qtyLeftToReceive, $issue->getQtyUnreceived());
            if ($qtyToReceive <= 0) {
                continue;
            }
            $totalValueReceived += $issue->calculateValueOfReceipt($qtyToReceive);
            $issue->addQtyReceived($qtyToReceive);
            $qtyLeftToReceive -= $qtyToReceive;
        }
        assertion($qtyLeftToReceive == 0);

        return $totalValueReceived;
    }


    private function doPurchaseAccounting(
        GoodsReceivedItem $grnItem,
        Transaction $transaction)
    {
        if (!$grnItem->requiresPurchaseAccounting()) {
            return;
        }

        $unitCost = $grnItem->getUnitPurchaseCost();
        assertion($unitCost > 0);
        $totalCost = $unitCost * $grnItem->getQtyReceived();
        assertion($totalCost != 0);

        $memo = sprintf("%s - %s x %s @ cost of %s",
            $transaction->getMemo(),
            $grnItem->getDescription(),
            number_format($grnItem->getQtyReceived()),
            number_format($unitCost, 4));

        $transaction->addEntry(
            $grnItem->getStockAccount(),
            $totalCost,
            $memo
        );
        $company = Company::findDefault($this->dbm);
        $transaction->addEntry(
            $company->getGrnAccount(),
            -$totalCost,
            $memo
        );
    }


    /**
     * Reverses the receipt of the given item.
     *
     * @return Transaction
     */
    public function reverseReceipt(GoodsReceivedItem $original, $qtyToReverse)
    {
        $error = $this->validateQtyToReverse($original, $qtyToReverse);
        if ($error) {
            throw new InvalidArgumentException($error);
        }
        $grn = $original->getGoodsReceivedNotice();
        $poItem = $original->getProducer();
        $reversal = $grn->addItem($poItem, -$qtyToReverse);
        $reversal->setDiscarded($original->isDiscarded());
        $transaction = Transaction::fromEvent($grn);
        $transaction->setMemo(sprintf("Reverse GRN item %s", $original->getId()));
        if ($original->isStockItem() && (!$original->isDiscarded())) {
            $this->reverseStockMoves($reversal, $original->getStockMoves(), $transaction);
        }
        if ($original->isWorkOrder()) {
            $this->reverseWipAccounting($reversal, $transaction);
            $this->reverseIssuesIfNeeded($reversal);
        }
        $this->doPurchaseAccounting($reversal, $transaction);

        $poItem->addQtyReceived(-$qtyToReverse);

        return $transaction;
    }


    private function validateQtyToReverse(GoodsReceivedItem $grnItem, $qtyToReverse)
    {
        if ($qtyToReverse < 1) {
            return "Qty to reverse must be positive";
        } elseif ($qtyToReverse > $grnItem->getQtyReceived()) {
            return "Cannot reverse more than has been receieved.";
        }
        return null;
    }


    /**
     * @param GoodsReceivedItem $grnItem
     * @param StockMove[] $stockMoves
     * @param Transaction $transaction
     */
    private function reverseStockMoves(
        GoodsReceivedItem $grnItem,
        array $stockMoves,
        Transaction $transaction)
    {
        $qtyLeft = -$grnItem->getQtyReceived();
        foreach ($stockMoves as $move) {
            if ($qtyLeft <= 0) {
                break;
            }
            $bin = $move->getStockBin();
            if (!$bin) {
                continue;
            }
            $binAdj = min($bin->getQuantity(), $qtyLeft);
            if ($binAdj <= 0) {
                continue;
            }
            $memo = sprintf('%s: %s x %s',
                $transaction->getMemo(),
                $grnItem->getSku(),
                -$binAdj);
            $bin->setQtyDiff(-$binAdj);
            $bin->applyNewQty($transaction, $memo);
            $qtyLeft -= $binAdj;
        }
        if ($qtyLeft > 0) {
            $this->throwQtyLeftException($grnItem, $qtyLeft);
        }
    }


    private function throwQtyLeftException(GoodsReceivedItem $grnItem, $qtyLeft)
    {
        $msg = sprintf(
            "Unable to reverse %s of %s units; " .
            "the stock may have already been used.",
            number_format($qtyLeft),
            number_format(-$grnItem->getQtyReceived()));
        $producer = $grnItem->getProducer();
        if ($producer->isWorkOrder() && $producer->hasParent()) {
            $msg .= ' You may need to reverse receipt of the parent work order first.';
        }

        throw new InvalidDataException($msg);
    }


    private function reverseWipAccounting(GoodsReceivedItem $reversal, Transaction $glTrans)
    {
        /* @var $wo WorkOrder */
        $wo = $reversal->getProducer();
        assertion($wo instanceof WorkOrder);
        $issues = $wo->getIssues();
        $issues = $this->reverseSortByDate($issues);

        $totalValue = 0;
        $qtyLeft = -$reversal->getQtyReceived();
        foreach ($issues as $issue) {
            $issueQty = min($qtyLeft, $issue->getQtyReceived());
            if ($issueQty <= 0) {
                continue;
            }
            $stdCost = $issueQty * $issue->getUnitValueIssued();
            $totalValue += $stdCost;
            $issue->addQtyReceived(-$issueQty);

            if ($issue->getQtyReceived() == 0) {
                $totalValue += $issue->getScrapValueIssued();
            }
            $qtyLeft -= $issueQty;
        }
        if ($qtyLeft != 0) {
            throw new UnexpectedValueException(
                "Unable to reverse $qtyLeft issued units");
        }
        if ($totalValue == 0) {
            throw new UnexpectedValueException("Nothing to reverse");
        }

        $memo = sprintf('%s - Materials: %s x %s',
            $glTrans->getMemo(),
            $wo->getSku(),
            $reversal->getQtyReceived());
        $glTrans->addEntry($reversal->getStockAccount(), -$totalValue, $memo);
        $glTrans->addEntry(GLAccount::fetchWorkInProcess($this->dbm), $totalValue, $memo);
    }


    /**
     * We have to reverse issuances for parent work orders because
     * this re-creates the child stock, which will then be un-received.
     */
    private function reverseIssuesIfNeeded(GoodsReceivedItem $reversal)
    {
        /* @var $wo WorkOrder */
        $wo = $reversal->getProducer();
        assertion($wo instanceof WorkOrder);
        if (!$wo->hasChild()) {
            return;
        }

        $qtyLeft = -$reversal->getQtyReceived();
        assertion($qtyLeft > 0);
        $issues = $wo->getIssues();
        $issues = $this->reverseSortByDate($issues);
        foreach ($issues as $issue) {
            if ($qtyLeft <= 0) {
                break;
            }
            $toUnissue = min($qtyLeft, $issue->getQtyUnreceived());
            if ($toUnissue <= 0) {
                continue;
            }
            $this->woIssuer->reverseIssue($issue, $toUnissue);
            $qtyLeft -= $toUnissue;
        }
    }

    /**
     * @param WorkOrderIssue[] $list
     * @return WorkOrderIssue[]
     */
    private function reverseSortByDate(array $list)
    {
        @ usort($list, function ($a, $b) {
            return $b->getTimestamp() - $a->getTimestamp();
        });
        return $list;
    }
}
