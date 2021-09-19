<?php

namespace Rialto\Accounting\PaymentTransaction;


interface InvoiceTransaction
{
    /**
     * @param CreditTransaction $credit
     * @param float $amount
     *  (optional) Defaults to the maximum amount that can be allocated.
     *
     * @return PaymentAllocation
     */
    public function allocateFrom(CreditTransaction $credit, $amount = null);
}
