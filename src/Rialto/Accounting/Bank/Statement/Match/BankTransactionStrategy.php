<?php

namespace Rialto\Accounting\Bank\Statement\Match;

use Doctrine\Common\Collections\Collection;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Bank\Transaction\BankTransactionAdjustment;
use Rialto\Accounting\Bank\Transaction\Orm\BankTransactionRepository;

/**
 * Matches a bank statement line against an existing bank transaction.
 */
class BankTransactionStrategy extends MatchStrategy
{
    /** @var BankTransaction[] */
    protected $matchingBank = [];

    public function loadMatchingRecords()
    {
        $this->loadMatchingBankTransactions();
    }

    public function hasMatchingRecords(): bool
    {
        return count($this->matchingBank) > 0;
    }

    /**
     * @return boolean
     *  True if matches were found, false otherwise.
     */
    protected function loadMatchingBankTransactions()
    {
        /** @var $repo BankTransactionRepository */
        $repo = $this->dbm->getRepository(BankTransaction::class);
        $this->matchingBank = $repo->findMatchingTransactions($this->pattern, $this);
        $this->autoSelectExactMatches();
        return $this->hasMatchingRecords();
    }

    protected function autoSelectExactMatches()
    {
        if ( count($this->matchingBank) > 0) {
            /* Bank transactions are sorted by exact-ness, so if there's
             * an exact match, it'll be first. */
            $firstMatch = reset($this->matchingBank);
            $amount = $this->round($firstMatch->getAmount());
            $needed = $this->round($this->getTotalOutstanding());
            if ( $amount == $needed ) {
                $this->bankTransactions[] = $firstMatch;
            }
        }
    }

    /** @return BankTransaction[] */
    public function getMatchingBankTransactions()
    {
        return $this->matchingBank;
    }

    public function setAcceptedBankTransactions(Collection $bankTransactions)
    {
        $this->bankTransactions = $bankTransactions;
    }

    /**
     * @return Collection<BankTransaction>
     */
    public function getAcceptedBankTransactions()
    {
        return $this->bankTransactions;
    }

    public function save()
    {
        $this->adjustTransactionAmountsIfNeeded();
        $this->linkBankTransactions();
    }

    private function adjustTransactionAmountsIfNeeded()
    {
        foreach ( $this->bankTransactions as $bankTrans ) {
            $this->adjustTransactionAmount($bankTrans);
        }
    }

    private function adjustTransactionAmount(BankTransaction $bankTrans)
    {
        if (! $this->canAdjustTransaction($bankTrans) ) return;

        $sOutstanding = $this->getTotalOutstanding();
        $tOutstanding = $this->getTransactionTotal();
        $diff = $sOutstanding - $tOutstanding;
        if ( $this->round($diff) == 0 ) return;

        $adjustment = new BankTransactionAdjustment($this->dbm, $bankTrans);
        $adjustment->setAdjustment($diff);
        $adjustment->setAdjustmentAccount($this->pattern->getAdjustmentAccount());
        $adjustment->save();
    }

    public function canAdjustTransaction(BankTransaction $bankTrans)
    {
        return $this->pattern->matchesUpdatePattern($bankTrans->getReference());
    }

    private function getTransactionTotal()
    {
        $total = 0;
        foreach ( $this->bankTransactions as $bankTrans ) {
            $total += $bankTrans->getAmountOutstanding();
        }
        return $total;
    }

}
