<?php

namespace Rialto\Accounting\Supplier;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */
class SupplierCreditItem
{
    /**
     * @var GLAccount
     */
    private $account;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $memo;

    public function getAccount()
    {
        return $this->account;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getMemo()
    {
        return $this->memo;
    }

    public function setAccount(GLAccount $account)
    {
        $this->account = $account;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function setMemo($memo)
    {
        $this->memo = $memo;
    }



}
