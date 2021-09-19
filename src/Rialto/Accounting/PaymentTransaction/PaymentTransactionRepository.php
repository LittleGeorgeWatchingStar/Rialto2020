<?php

namespace Rialto\Accounting\PaymentTransaction;

use Doctrine\ORM\QueryBuilder;

interface PaymentTransactionRepository
{
    /**
     * Creates a QueryBuilder to find credits that might match the given invoice.
     *
     * @return QueryBuilder
     */
    public function queryEligibleCreditsToMatch(PaymentTransaction $invoice);
}
