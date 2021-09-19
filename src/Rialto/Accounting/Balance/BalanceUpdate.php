<?php

namespace Rialto\Accounting\Balance;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Period\Period;

/**
 * Shows the change in an AccountBalance after balances have been reposted.
 */
class BalanceUpdate
{
    /** @var GLAccount */
    public $account;

    /** @var Period */
    public $period;

    public $actual;
    public $balanceFwd;
    public $budget;
    public $budgetFwd;

    /**
     * @param AccountBalance $before The account balance before changes
     *   have been reposted.
     */
    public function __construct(AccountBalance $before)
    {
        $this->account = $before->getAccount();
        $this->period = $before->getPeriod();
        $this->actual = new BalanceAmounts($before->getActual());
        $this->balanceFwd = new BalanceAmounts($before->getBalanceFwd());
        $this->budget = new BalanceAmounts($before->getBudget());
        $this->budgetFwd = new BalanceAmounts($before->getBudgetFwd());
    }

    /**
     * @param AccountBalance $after The account balance after changes
     *   have been reposted.
     */
    public function setAfter(AccountBalance $after)
    {
        $this->actual->after = $after->getActual();
        $this->balanceFwd->after = $after->getBalanceFwd();
        $this->budget->after = $after->getBudget();
        $this->budgetFwd->after = $after->getBudgetFwd();
    }
}

class BalanceAmounts
{
    public $before;
    public $after;

    public function __construct($before)
    {
        $this->before = $before;
    }

    public function isChanged()
    {
        return $this->round($this->before) != $this->round($this->after);
    }

    private function round($amt)
    {
        return round($amt, 2);
    }
}
