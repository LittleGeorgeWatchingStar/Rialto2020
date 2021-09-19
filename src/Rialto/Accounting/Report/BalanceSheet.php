<?php

namespace Rialto\Accounting\Report;


use Rialto\Accounting\Balance\AccountBalance;
use Rialto\Accounting\Balance\Orm\AccountBalanceRepository;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Period\Period;

class BalanceSheet
{
    /** @var Period[] */
    private $periods = [];

    /** @var float[section][periodID]  */
    private $sectionTotals = [];

    /** @var float[periodID] */
    private $periodTotals = [];

    /**
     * @var AccountBalance[]
     */
    private $index = [];

    /** @var GLAccount[accountID] */
    private $accounts = [];

    private $profitAndLossTotals = [];

    /**
     * Convert the user inputs $lastPeriod, $numPeriods, and $interval into
     * an array of Periods.
     *
     * @param Period[] $periods
     */
    public function __construct(array $periods)
    {
        $this->periods = $periods;
    }

    public function getPeriods()
    {
        return $this->periods;
    }

    public function loadBalances(AccountBalanceRepository $repo)
    {
        $balances = $repo->findForBalanceSheet($this->periods);
        $this->buildIndex($balances);
        $this->loadProfitAndLossTotals($repo);
    }

    /**
     * @param AccountBalance[] $balances
     */
    private function buildIndex(array $balances)
    {
        $this->index = [];
        $this->sectionTotals = [];
        $this->periodTotals = [];
        $this->accounts = [];
        foreach ( $balances as $balance ) {
            $periodID = $balance->getPeriodNo();
            $section = $balance->getSectionName();
            $group = $balance->getGroupName();
            $accountID = $balance->getAccountCode();
            $this->index[$section][$group][$accountID][$periodID] = $balance;
            $this->accounts[$accountID] = $balance->getAccount();

            if (! isset($this->sectionTotals[$section][$periodID])) {
                $this->sectionTotals[$section][$periodID] = 0;
            }
            $this->sectionTotals[$section][$periodID] += $balance->getBalanceToDateForReporting();
            if (! isset($this->periodTotals[$periodID])) {
                $this->periodTotals[$periodID] = 0;
            }
            $this->periodTotals[$periodID] += $balance->getBalanceToDate();
        }
    }

    private function loadProfitAndLossTotals(AccountBalanceRepository $repo)
    {
        foreach ( $this->periods as $period ) {
            $id = $period->getId();
            $total = $repo->findProfitAndLossTotalForBalanceSheet($period);
            $this->profitAndLossTotals[$id] = $total;
            $this->sectionTotals['Retained Earnings'][$id] += $total; // todo: kludge
            $this->periodTotals[$id] -= $total;
        }
    }

    public function getSections()
    {
        return $this->index;
    }

    public function getSectionTotal($section, Period $period)
    {
        return $this->sectionTotals[$section][$period->getId()];
    }

    public function getPeriodTotal(Period $period)
    {
        return $this->periodTotals[$period->getId()];
    }

    public function getProfitAndLossTotal(Period $period)
    {
        return $this->profitAndLossTotals[$period->getId()];
    }

    /** @return GLAccount */
    public function getAccount($accountID)
    {
        return $this->accounts[$accountID];
    }
}
