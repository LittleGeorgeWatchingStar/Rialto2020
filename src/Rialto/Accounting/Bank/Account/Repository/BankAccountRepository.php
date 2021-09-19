<?php

namespace Rialto\Accounting\Bank\Account\Repository;


use Rialto\Accounting\Bank\Account\BankAccount;

/**
 * A repository for retrieving BankAccount objects.
 */
interface BankAccountRepository
{
    public function getDefaultChecking(): BankAccount;
}
