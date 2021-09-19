<?php

namespace Rialto\Accounting\Bank\Transfer;


use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Statement\BankStatementMatch;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Entity\RialtoEntity;

class BankTransfer implements AccountingEvent, RialtoEntity
{

    /** @var string */
    private $id;

    /** @var BankTransaction */
    private $fromTransaction;

    /** @var BankTransaction */
    private $toTransaction;

    public static function create(Transaction $glTrans,
                                  BankAccount $from,
                                  BankAccount $to,
                                  float $amount): self
    {
        if ($from->getId() == $to->getId()) {
            throw new \InvalidArgumentException("Cannot create a transfer to the same bank account.");
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException("Amount transferred must be greater than zero.");
        }

        $transfer = new self();
        $memo = "BANK TRANSFER \$$amount $from to $to";
        $glTrans->addEntry($from->getGLAccount(), -$amount, $memo);
        $glTrans->addEntry($to->getGLAccount(), $amount, $memo);
        $glTrans->setMemo($memo);
        $transfer->fromTransaction = new BankTransaction($glTrans, $from);
        $transfer->fromTransaction->setAmount(-$amount);
        $transfer->toTransaction = new BankTransaction($glTrans, $to);
        $transfer->toTransaction->setAmount($amount);
        return $transfer;
    }

    public function __toString()
    {
        return "bank transfer {$this->id}";
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDate()
    {
        return $this->fromTransaction->getDate();
    }

    public function getMemo(): string
    {
        return $this->fromTransaction->getMemo();
    }

    public function getSystemType()
    {
        return $this->fromTransaction->getSystemType();
    }

    public function getSystemTypeNumber()
    {
        return $this->fromTransaction->getSystemTypeNumber();
    }

    /**
     * @return BankStatementMatch[]
     */
    public function getMatches(): array
    {
        return array_merge($this->fromTransaction->getMatches(),
            $this->toTransaction->getMatches());
    }

    /**
     * @return GLEntry[]
     */
    public function getGLEntries(): array
    {
        return $this->fromTransaction->getGLEntries();
    }

    public function getAmount(): float
    {
        return $this->toTransaction->getAmount();
    }

    public function getAmountCleared()
    {
        return array_reduce($this->getMatches(),
            function (float $amount, BankStatementMatch $match) {
                return $amount + $match->getAmountCleared();
            }, 0.0);
    }

    public function getFromTransaction(): BankTransaction
    {
        return $this->fromTransaction;
    }

    public function getToTransaction(): BankTransaction
    {
        return $this->toTransaction;
    }
}
