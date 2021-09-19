<?php

namespace Rialto\Accounting\Transaction\Web;


use Rialto\Accounting\Ledger\Account\GLAccount;
use Symfony\Component\Validator\Constraints as Assert;

class EntryTemplate
{
    /**
     * @var GLAccount
     * @Assert\NotNull(message="Account is required.")
     */
    public $account;

    /**
     * @var float
     * @Assert\Type(type="numeric")
     * @Assert\NotEqualTo(value=0, message="Amount cannot be zero.")
     */
    public $amount;
}
