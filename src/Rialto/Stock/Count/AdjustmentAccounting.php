<?php

namespace Rialto\Stock\Count;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Stock\Bin\StockBin;

/**
 * Implements a strategy for doing the accounting for stock adjustments.
 */
interface AdjustmentAccounting
{
    public function setMemo($memo);

    /**
     * Adds the adjusted bin for inclusion in the final accounting.
     */
    public function addBin(StockBin $bin);

    /**
     * Adds the final GLEntries to the transaction.
     */
    public function addEntries(Transaction $trans);

    /**
     * Call this method to override the default stock adjustment account;
     * for example, when throwing away defective stock covered under
     * warranty.
     */
    public function setAdjustmentAccount(GLAccount $account);
}
