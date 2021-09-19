<?php

namespace Rialto\Accounting\Ledger\Entry;

use DateTime;
use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Money;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Entity\RialtoEntity;
use Rialto\Sales\Order\SalesOrder;

/**
 * A general ledger (GL) entry.  Generally speaking, you should NOT instantiate
 * these directly; instead, create a Transaction and use its addEntry()
 * method.
 */
class GLEntry implements RialtoEntity, AccountingEvent
{
    const MONEY_PRECISION = 2;

    public static function round($amount): float
    {
        return Money::round($amount, self::MONEY_PRECISION);
    }

    /**
     * Compare two monetary amounts for equality.
     */
    public static function areEqual($a, $b): bool
    {
        return bceq($a, $b, self::MONEY_PRECISION);
    }

    /** @var string */
    private $id;

    /** @var Transaction */
    private $transaction;

    /** @deprecated use $transaction instead */
    private $systemType;
    /** @deprecated use $transaction instead */
    private $systemTypeNumber;

    /** @var Period */
    private $period;

    /** @var GLAccount */
    private $account;

    /** @var int */
    private $chequeNumber = 0;

    /** @var DateTime */
    private $date;

    /** @var string */
    private $narrative = '';

    /** @var float */
    private $amount;

    /** @var bool */
    private $posted = false;

    /** @var string */
    private $jobRef = '';

    public function __construct(
        Transaction $transaction,
        GLAccount $account,
        $amount,
        $memo)
    {
        if (! is_numeric($amount)) {
            throw new \InvalidArgumentException('GL entry amount must be numeric');
        }
        if (self::round($amount) == 0) {
            throw new \InvalidArgumentException('GL entry amount cannot be zero');
        }

        $this->transaction = $transaction;
        $this->systemType = $transaction->getSystemType();
        $this->systemTypeNumber = $transaction->getSystemTypeNumber();
        $this->date = $transaction->getDate();
        $this->period = $transaction->getPeriod();
        $this->narrative = trim($memo) ?: $transaction->getMemo();
        $this->account = $account;
        $this->amount = self::round($amount);
    }

    public function getSystemType()
    {
        return $this->transaction->getSystemType();
    }

    public function getSystemTypeNumber()
    {
        return $this->transaction->getGroupNo();
    }

    public function getAccount(): GLAccount
    {
        return $this->account;
    }

    /**
     * @return int
     */
    public function getAccountCode()
    {
        return (int) $this->account->getId();
    }

    public function getPeriod(): Period
    {
        return $this->period;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return clone $this->date;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    public function getNarrative(): string
    {
        return $this->getMemo();
    }

    /**
     * @return string
     */
    public function getMemo(): string
    {
        return $this->narrative;
    }

    /**
     * @return int
     */
    public function getPeriodNumber()
    {
        return $this->getPeriod()->getId();
    }

    public function setSalesOrder(SalesOrder $order = null)
    {
        $this->jobRef = $order ? $order->getOrderNumber() : '';
    }

    public function setChequeNumber($chequeNo)
    {
        $this->chequeNumber = (int) $chequeNo;
    }
}
