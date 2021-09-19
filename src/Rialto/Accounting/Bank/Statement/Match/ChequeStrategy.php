<?php

namespace Rialto\Accounting\Bank\Statement\Match;

use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Bank\Transaction\Orm\BankTransactionRepository;

/**
 * Matches a bank statement line against a cheque that we've written.
 *
 * Extends BankTransactionStrategy because cheques are recorded as
 * BankTransactions.
 */
class ChequeStrategy extends BankTransactionStrategy
{
    public function loadMatchingRecords()
    {
        /** @var $repo BankTransactionRepository */
        $repo = $this->dbm->getRepository(BankTransaction::class);
        $this->matchingBank = $repo->findMatchingCheques($this->pattern, $this);
        $this->autoSelectExactMatches();
    }

    public function getChequeNumber()
    {
        return $this->getStatement()->getCustomerReference();
    }

}
