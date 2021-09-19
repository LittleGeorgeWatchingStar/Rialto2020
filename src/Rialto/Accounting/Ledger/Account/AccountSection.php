<?php

namespace Rialto\Accounting\Ledger\Account;


use Rialto\Entity\RialtoEntity;

class AccountSection implements RialtoEntity
{
    const INCOME = 1;
    const COST_OF_GOODS_SOLD = 2;
    const EXPENSES = 90;
    const INCOME_TAXES = 100;

    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /**
     * Multiply the account balance by this amount when reporting.
     * @var int
     */
    private $sign;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    function __toString()
    {
        return $this->name;
    }

    public function getSignForReporting()
    {
        return $this->sign;
    }
}
