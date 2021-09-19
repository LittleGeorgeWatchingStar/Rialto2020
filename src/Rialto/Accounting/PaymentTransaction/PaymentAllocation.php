<?php

namespace Rialto\Accounting\PaymentTransaction;

use Rialto\Entity\RialtoEntity;


/**
 * Allocates payments and credits against invoices.
 * This allows us to know which invoice a payment was made for.
 */
interface PaymentAllocation extends RialtoEntity
{
    public function getId();

    /** @return float */
    public function getAmount();

    /** @return PaymentTransaction */
    public function getCredit();

    /**
     * @return bool True if $trans is the credit side of this allocation.
     */
    public function isForCredit(PaymentTransaction $trans);

    /** @return PaymentTransaction */
    public function getInvoice();
}
