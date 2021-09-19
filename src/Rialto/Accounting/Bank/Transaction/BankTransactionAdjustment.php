<?php

namespace Rialto\Accounting\Bank\Transaction;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\DbManager;

/**
 * Adjusts a bank transaction after it has already been entered.
 */
class BankTransactionAdjustment
{
    /** @var DbManager */
    private $dbm;

    /** @var BankTransaction */
    private $bankTrans;

    /** @var double */
    private $adjustment;

    /** @var GLAccount */
    private $adjustmentAccount;

    /** @var string */
    private $memo;

    public function __construct(
        DbManager $dbm,
        BankTransaction $bankTrans)
    {
        $this->dbm = $dbm;
        $this->bankTrans = $bankTrans;
        $this->memo = $bankTrans->getMemo();
    }

    public function getBankTransaction()
    {
        return $this->bankTrans;
    }

    public function getAdjustment()
    {
        return $this->adjustment;
    }

    public function setAdjustment($diff)
    {
        $this->adjustment = $diff;
    }

    public function getAdjustmentAccount()
    {
        return $this->adjustmentAccount;
    }

    public function setAdjustmentAccount(GLAccount $account)
    {
        $this->adjustmentAccount = $account;
    }

    public function getMemo()
    {
        return $this->memo;
    }

    public function setMemo($memo)
    {
        $this->memo = $memo;
    }

    public function save()
    {
        $newAmount = $this->bankTrans->getAmount() + $this->adjustment;
        $this->bankTrans->setAmount($newAmount);

        $glTrans = Transaction::fromEvent($this->bankTrans);
        $glTrans->addEntry($this->bankTrans->getBankGLAccount(), $this->adjustment, $this->memo);
        $glTrans->addEntry($this->adjustmentAccount, -$this->adjustment, $this->memo);

        $this->dbm->persist($glTrans);
        $this->dbm->flush();
    }
}
