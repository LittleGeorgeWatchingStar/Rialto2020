<?php

namespace Rialto\Accounting\Debtor\Match;

use Rialto\Accounting\Debtor\DebtorTransaction;

/**
 * Takes an array of DebtorTransaction objects and returns a list of
 * TransactionMatch objects.
 */
interface TransactionMatcher
{
    /**
     * @param DebtorTransaction[] $transactions
     * @return TransactionMatch[]
     */
    public function findMatches(array $transactions);
}
