<?php

namespace Rialto\Manufacturing\WorkOrder\Issue;

use DateTime;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Exception\InvalidDataException;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Stock\Consumption\StockConsumption;


/**
 * Issues a work order.
 *
 * @see WorkOrderIssue
 */
class WorkOrderIssuer
{
    /** @var DbManager */
    private $dbm;

    /** @var GLAccount */
    private $wipAccount;

    /** @var float[] */
    private $accountingSums = [];

    /** @var Transaction */
    private $transaction = null;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
        $this->wipAccount = GLAccount::fetchWorkInProcess($dbm);
    }

    public function issue(WorkOrder $order,
                          $quantity,
                          DateTime $date = null): WorkOrderIssue
    {
        $issue = new WorkOrderIssue($order, $quantity, $date);
        if (!$issue->hasPartsToIssue()) {
            return $issue; // abort
        }
        $order->addIssue($issue);
        $this->dbm->persist($issue);
        $this->dbm->flush(); // So the issue record gets an ID.
        assertion($issue->getSystemTypeNumber());

        $this->transaction = Transaction::fromEvent($issue);

        $this->consumeStock($issue);

        $this->accountingSums = [];
        foreach ($issue->getIssuedItems() as $issueItem) {
            $this->updateValueIssued($issueItem);
        }
        $this->roundAccountingSums();
        $order->addQtyIssued($quantity);

        $this->addGLEntries();
        $this->dbm->persist($this->transaction);
        $order->setUpdated();

        return $issue;
    }

    private function consumeStock(WorkOrderIssue $issue)
    {
        foreach ($issue->getIssuedItems() as $issueItem) {
            $this->consumeItem($issueItem);
        }
    }

    private function consumeItem(WorkOrderIssueItem $issueItem)
    {
        $woReq = $issueItem->getRequirement();
        $consumption = new StockConsumption($woReq, $this->transaction);
        $consumption->consume($issueItem->getTotalQtyIssued());
    }

    private function updateValueIssued(WorkOrderIssueItem $issueItem)
    {
        $this->updateAccountingSums($issueItem, $issueItem->getTotalStandardCost());
    }

    private function updateAccountingSums(WorkOrderIssueItem $issueItem, $amount)
    {
        $account = $issueItem->getStockAccount();
        $account_id = $account->getId();
        if (!isset($this->accountingSums[$account_id])) {
            $this->accountingSums[$account_id] = 0.0;
        }
        $this->accountingSums[$account_id] += $amount;
    }

    /**
     * Correct rounding is important to ensure that numbers are accurate
     * and sum to zero.
     */
    private function roundAccountingSums()
    {
        $rounded = [];
        foreach ($this->accountingSums as $accountID => $amount) {
            $rounded[$accountID] = GLEntry::round($amount);
        }
        $this->accountingSums = $rounded;
    }

    private function getTotalValue()
    {
        return array_sum($this->accountingSums);
    }

    private function addGLEntries()
    {
        $this->transaction->addEntry($this->wipAccount, $this->getTotalValue());

        foreach ($this->accountingSums as $accountID => $value) {
            $account = GLAccount::fetch($accountID, $this->dbm);
            $this->transaction->addEntry($account, -$value);
        }
    }

    /**
     * Rolls back previous issuance transactions.
     *
     * @param WorkOrderIssue $issue
     *  The issuance that will be rolled back.
     * @param int $quantity
     *  The number of units to un-issue.
     */
    public function reverseIssue(WorkOrderIssue $issue, $quantity)
    {
        if (!$this->validateReverseQty($issue, $quantity)) {
            throw new \InvalidArgumentException("Invalid quantity $quantity");
        }

        $this->accountingSums = [];
        $this->transaction = Transaction::fromEvent($issue);
        $this->transaction->setDate(new DateTime());
        $wo = $issue->getWorkOrder();
        $this->transaction->setMemo(sprintf(
            'Reverse issuance of %s x %s from WO %s',
            number_format($quantity),
            $wo->getSku(),
            $wo->getId()
        ));

        foreach ($issue->getIssuedItems() as $issueItem) {
            /* If this issue is being completely reversed, remember to reverse
             * any scrap that was consumed. */
            $includeScrap = ($issue->getQtyIssued() == $quantity);

            $this->recreateStock($issueItem, $quantity, $includeScrap);
            $this->updateValueReversed($issueItem, $quantity, $includeScrap);
            if ($includeScrap) {
                $issueItem->setScrapIssued(0);
            }
        }
        $this->roundAccountingSums();

        $issue->reverseQtyIssued($quantity);
        $wo = $issue->getWorkOrder();
        $wo->addQtyIssued(-$quantity);
        $this->addGLEntries();
        $wo->setUpdated();

        $this->dbm->persist($this->transaction);
    }

    private function validateReverseQty(WorkOrderIssue $issue, $quantity)
    {
        if ($quantity < 1) return false;
        if ($issue->getQtyIssued() - $quantity < $issue->getQtyReceived()) {
            return false;
        }
        return true;
    }

    private function recreateStock(
        WorkOrderIssueItem $issueItem,
        $parentQty,
        $includeScrap)
    {
        $childQty = $issueItem->getUnitQtyIssued() * $parentQty;
        if ($includeScrap) {
            $childQty += $issueItem->getScrapIssued();
        }

        $qtyLeft = $childQty;

        /* Put the stock back into the bins from which it came. */
        foreach ($issueItem->getIssuedBins() as $bin) {
            if ($qtyLeft <= 0) break;
            $issueQty = $issueItem->getTotalQtyIssuedFromBin($bin);
            assert($issueQty >= 0);
            $toCreate = min($issueQty, $qtyLeft);
            if ($toCreate <= 0) continue;

            $bin->setQtyDiff($toCreate);
            $bin->applyNewQty($this->transaction);

            $alloc = $issueItem->getAllocation($bin);
            if ($alloc) {
                $alloc->addQtyDelivered(-$toCreate);
            }

            $qtyLeft -= $toCreate;
        }

        if ($qtyLeft > 0) {
            throw new InvalidDataException("Unable to reverse $qtyLeft of $childQty units");
        }
    }

    private function updateValueReversed(
        WorkOrderIssueItem $issueItem,
        $parentQty,
        $includeScrap)
    {
        $amount = $issueItem->getUnitStandardCost()
            * $issueItem->getUnitQtyIssued()
            * $parentQty;
        if ($includeScrap) {
            $amount += $issueItem->getUnitStandardCost() * $issueItem->getScrapIssued();
        }

        /* Negate the amount because this is a reversal. */
        $this->updateAccountingSums($issueItem, -$amount);
    }
}
