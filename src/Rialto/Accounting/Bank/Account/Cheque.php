<?php

namespace Rialto\Accounting\Bank\Account;


/**
 * Payments that can be made by cheque should implement this interface.
 *
 * This interface allows cheque numbers to be validated.
 */
interface Cheque
{
    /** @return int */
    public function getChequeNumber();

    /** @return BankAccount */
    public function getBankAccount();
}
