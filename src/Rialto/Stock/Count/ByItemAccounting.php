<?php

namespace Rialto\Stock\Count;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Item\StockItem;

/**
 * Creates a separate GL entry for every stock item adjusted.
 */
class ByItemAccounting implements AdjustmentAccounting
{
    /** @var string */
    private $memo;

    /** @var \SplObjectStorage */
    private $totalsByItem;

    /** @var GLAccount */
    private $adjustmentAccount = null;

    public function __construct()
    {
        $this->totalsByItem = new \SplObjectStorage();
    }

    /**
     * @param string $memo
     */
    public function setMemo($memo)
    {
        $this->memo = trim($memo);
    }

    /**
     * Adds the adjusted bin for inclusion in the final accounting.
     */
    public function addBin(StockBin $bin)
    {
        $item = $bin->getStockItem();
        if (! isset($this->totalsByItem[$item]) ) {
            $this->totalsByItem[$item] = 0;
        }
        $this->totalsByItem[$item] += $bin->getQtyDiff();
    }

    /**
     * Adds the final GLEntries to the transaction.
     */
    public function addEntries(Transaction $trans)
    {
        foreach ( $this->totalsByItem as $item ) {
            $qtyDiff = $this->totalsByItem[$item];
            $unitCost = $item->getStandardCost();
            $extCost = GLEntry::round($unitCost * $qtyDiff);
            if ( $extCost == 0 ) continue;

            $trans->addEntry(
                $item->getStockAccount(),
                $extCost,
                $this->getGLMemo($item, $qtyDiff)
            );

            $trans->addEntry(
                $this->getAdjustmentAccount($item),
                -$extCost,
                $this->getGLMemo($item, $qtyDiff)
            );
        }
    }

    private function getGLMemo(StockItem $item, $netQtyDiff)
    {
        return sprintf('%s: %s x %s @ std cost of %s',
            $this->memo,
            $item->getId(),
            number_format($netQtyDiff),
            number_format($item->getStandardCost(), 4)
        );
    }

    public function setAdjustmentAccount(GLAccount $account)
    {
        $this->adjustmentAccount = $account;
    }

    /** @return GLAccount */
    private function getAdjustmentAccount(StockItem $item)
    {
        return $this->adjustmentAccount ?: $item->getAdjustmentAccount();
    }

}
