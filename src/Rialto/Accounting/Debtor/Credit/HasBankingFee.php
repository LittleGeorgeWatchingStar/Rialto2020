<?php

namespace Rialto\Accounting\Debtor\Credit;

/**
 * Any transaction that has an additional banking fee.
 */
interface HasBankingFee
{
    /** @return float */
    public function getFeeAmount();

    /** @return CreditNote */
    public function createCreditNoteForBankingFee();
}
