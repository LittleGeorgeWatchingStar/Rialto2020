<?php

namespace Rialto\Accounting\Debtor\Match;

/**
 * @author Ian Phillips <ian@gumstix.com>
 */
abstract class DebtorTransactionMatcher implements TransactionMatcher
{
    /**
     * Removes any matches with a non-zero balance.
     */
    public function filterExactMatches(array $matches)
    {
        return array_filter($matches, function(TransactionMatch $match) {
            return $match->isBalanced();
        });
    }

}
