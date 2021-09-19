<?php

namespace Rialto\Accounting\Bank\Statement\Match;

use Rialto\Accounting\Debtor\DebtorTransactionFactory;

/**
 *
 */
abstract class CustomerPaymentStrategy
extends BankTransactionStrategy
{
    /** @var DebtorTransactionFactory */
    protected $factory;

    public function setDebtorTransactionFactory(DebtorTransactionFactory $factory)
    {
        $this->factory = $factory;
    }

}
