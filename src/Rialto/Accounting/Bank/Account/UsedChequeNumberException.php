<?php

namespace Rialto\Accounting\Bank\Account;

use Rialto\Exception\ConcurrencyException;

/**
 * Indicates that the user has tried to print a cheque with a cheque number
 * that has already been used.
 */
class UsedChequeNumberException extends ConcurrencyException
{
    public function __construct(BankAccount $account, $chequeNo)
    {
        $message = "Cheque number $chequeNo for $account has already been used";
        parent::__construct($message);
    }

}
