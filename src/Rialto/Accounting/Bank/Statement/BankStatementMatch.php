<?php

namespace Rialto\Accounting\Bank\Statement;

use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Entity\RialtoEntity;

class BankStatementMatch implements RialtoEntity
{
    private $id;

    /** @var BankStatement */
    private $bankStatement;

    /** @var BankTransaction */
    private $bankTransaction;

    /** @var float */
    private $amountCleared = 0;

    public function __construct(BankStatement $statement, BankTransaction $trans)
    {
        $this->bankStatement = $statement;
        $this->bankTransaction = $trans;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBankStatement()
    {
        return $this->bankStatement;
    }

    public function getBankTransaction()
    {
        return $this->bankTransaction;
    }

    public function getAmountCleared()
    {
        return $this->amountCleared;
    }

    public function setAmountCleared($amount)
    {
        $this->amountCleared = $amount;
    }
}
