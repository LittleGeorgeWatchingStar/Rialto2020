<?php

namespace Rialto\Accounting\Balance;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Period\Period;
use Rialto\Entity\RialtoEntity;

/**
 * Records the balance for a single GL account for a single period.
 */
class AccountBalance implements RialtoEntity
{
    /**
     * @var GLAccount
     */
    private $account;

    /**
     * @var Period
     */
    private $period;

    /**
     * The total balance for this period.
     *
     * @var float $actual
     */
    private $actual;

    /**
     * The amount brought forward from the previous period.
     * This number does NOT include the balance from this period itself.
     *
     * @var float $balanceFwd
     */
    private $balanceFwd;

    /**
     * @var float $budget
     */
    private $budget;

    /**
     * @var float $budgetFwd
     */
    private $budgetFwd;

    /**
     * @param AccountBalance[] $balances
     * @return AccountBalance[] Indexed by account ID
     */
    public static function indexByAccountID(array $balances)
    {
        $index = [];
        foreach ( $balances as $balance ) {
            $accountID = $balance->getAccountCode();
            $index[$accountID] = $balance;
        }
        return $index;
    }

    /** @return GLAccount */
    public function getAccount()
    {
        return $this->account;
    }

    public function getAccountCode()
    {
        return $this->account->getId();
    }

    /** @return string */
    public function getAccountName()
    {
        return $this->account->getName();
    }

    /** @return string */
    public function getGroupName()
    {
        return $this->account->getGroupName();
    }

    /** @return string */
    public function getSectionName()
    {
        return $this->account->getSectionName();
    }

    public function isProfitAndLoss()
    {
        return $this->account->isProfitAndLoss();
    }

    /** @return Period */
    public function getPeriod()
    {
        return $this->period;
    }

    public function getPeriodNo()
    {
        return $this->period->getId();
    }

    public function setBudget($budget)
    {
        $this->budget = $budget;
    }

    public function getBudget()
    {
        return (float) $this->budget;
    }

    public function setActual($actual)
    {
        $this->actual = $actual;
    }

    public function getActual()
    {
        return (float) $this->actual;
    }

    public function setBalanceFwd($balanceFwd)
    {
        $this->balanceFwd = $balanceFwd;
    }

    public function getBalanceFwd()
    {
        return (float) $this->balanceFwd;
    }

    /**
     * The total balance of this account up through this period.
     * @return float
     */
    public function getBalanceToDate()
    {
        return $this->actual + $this->balanceFwd;
    }

    public function getBalanceToDateForReporting()
    {
        return $this->getBalanceToDate() * $this->account->getSignForReporting();
    }

    public function setBudgetFwd($budgetFwd)
    {
        $this->budgetFwd = $budgetFwd;
    }

    public function getBudgetFwd()
    {
        return (float) $this->budgetFwd;
    }
}
