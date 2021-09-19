<?php

namespace Rialto\Accounting\Bank\Statement\Match;


/**
 * A match strategy that does nothing; used when there is no other match.
 */
class NullStrategy
extends MatchStrategy
{
    public function loadMatchingRecords()
    {
        /* do nothing */
    }

    public function hasMatchingRecords(): bool
    {
        /* This strategy does not have to find matching records to be
         * applicable -- it wouldn't do anything with any matching
         * records anyway! */
        return true;
    }

    public function save()
    {
        /* do nothing */
    }

    public function getMatchingBankTransactions()
    {
        return [];
    }
}
