<?php

namespace Rialto\Accounting\Bank\Account;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Entity\RialtoEntity;

/**
 * A regular old bank account, like a chequing or savings account.
 */
class BankAccount implements RialtoEntity
{
    /** @var GLAccount */
    private $glAccount;
    private $name = '';
    private $BankAccountNumber;
    private $BankAddress;
    private $nextChequeNumber;

    /**
     * Factory method.
     */
    public static function fromGLAccount(GLAccount $account): self
    {
        $ba = new self($account->getName());
        $ba->glAccount = $account;
        return $ba;
    }

    public function __construct($name, $nextChequeNumber = 1)
    {
        $this->name = trim($name);
        $this->nextChequeNumber = $nextChequeNumber;
    }

    public function getId()
    {
        return $this->glAccount->getId();
    }

    public function getGLAccount(): GLAccount
    {
        return $this->glAccount;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getNextChequeNumber()
    {
        return $this->nextChequeNumber;
    }

    /**
     * Validates the cheque number and updates the next cheque number
     * for the bank account;
     */
    public function confirmChequeNumber($chequeNo)
    {
        if ( $chequeNo < $this->nextChequeNumber ) {
            throw new UsedChequeNumberException($this, $chequeNo);
        }

        if ($chequeNo == $this->nextChequeNumber) {
            $this->nextChequeNumber ++;
        }

        /*
        If $chequeNo is not the next one, it means that the user
        has requested a non-contiguous number that is higher than
        the current range. This is an atypical case that happens
        sometimes for obscure reasons (ask Gordon). When this does happen,
        we don't want to lose track of the typical next cheque number,
        so we do nothing.

        So cheque numbers proceed like this:
        1, 2, 3, SOME BIG NUMBER, 4, 5, 6, 7, ANOTHER BIG NUMBER, 8, 9...

        After each BIG NUMBER, we need to pick up where we left off.
        */
    }
}
