<?php

namespace Rialto\Stock\Count;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Item\StockItem;

/**
 * Creates a GL entry for the total change in each stock account.
 */
class ByAccountAccounting implements AdjustmentAccounting
{
    /** @var string */
    private $memo;

    /** @var \SplObjectStorage */
    private $totalsByAccount;

    /** @var GLAccount */
    private $adjustmentAccount = null;

    public function __construct()
    {
        $this->totalsByAccount = new \SplObjectStorage();
    }

    /**
     * @param string $memo
     */
    public function setMemo($memo)
    {
        $this->memo = trim($memo);
    }

    public function setAdjustmentAccount(GLAccount $account)
    {
        if ( count($this->totalsByAccount) > 0 ) {
            throw new \LogicException(sprintf(
                "You must call %s before adding bins", __METHOD__));
        }
        $this->adjustmentAccount = $account;
    }

    /** @return GLAccount */
    private function getAdjustmentAccount(StockItem $item)
    {
        return $this->adjustmentAccount ?: $item->getAdjustmentAccount();
    }

    /**
     * Adds the adjusted bin for inclusion in the final accounting.
     */
    public function addBin(StockBin $bin)
    {
        $item = $bin->getStockItem();
        $unitCost = $item->getStandardCost();
        $extCost = GLEntry::round($unitCost * $bin->getQtyDiff());

        $stkAct = $item->getStockAccount();
        if (! isset($this->totalsByAccount[$stkAct]) ) {
            $this->totalsByAccount[$stkAct] = 0;
        }
        $this->totalsByAccount[$stkAct] += $extCost;

        $adjAct = $this->getAdjustmentAccount($item);
        if (! isset($this->totalsByAccount[$adjAct]) ) {
            $this->totalsByAccount[$adjAct] = 0;
        }
        $this->totalsByAccount[$adjAct] -= $extCost;
    }

    /**
     * Adds the final GLEntries to the transaction.
     */
    public function addEntries(Transaction $trans)
    {
        foreach ( $this->totalsByAccount as $stkAct ) {
            $extCost = $this->totalsByAccount[$stkAct];
            if ($extCost == 0) continue;

            $trans->addEntry($stkAct, $extCost, $this->memo);
        }
    }

}
