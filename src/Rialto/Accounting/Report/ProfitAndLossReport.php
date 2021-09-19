<?php

namespace Rialto\Accounting\Report;


use ArrayIterator;
use Countable;
use IteratorAggregate;
use Rialto\Accounting\Balance\Orm\AccountBalanceRepository;
use Rialto\Accounting\Ledger\Account\AccountSection;
use Rialto\Accounting\Period\Orm\PeriodRepository;
use Rialto\Accounting\Period\PeriodRange;

class ProfitAndLossReport implements Countable, IteratorAggregate
{
    const GROSS_PROFIT = 'Gross profit';
    const GROSS_MARGIN = 'Gross margin';
    const PRETAX_PROFIT = 'Pretax profit';
    const PRETAX_MARGIN = 'Pretax margin';
    const AFTER_TAX_PROFIT = 'After-tax profit';
    const AFTER_TAX_MARGIN = 'After-tax margin';

    /** @var PeriodRepository */
    private $repo;

    /** @var PeriodRange */
    public $periods;

    /** @var int */
    public $periodLength = 1;

    /** @var ProfitAndLossColumn[] */
    private $columns = [];

    public function __construct(PeriodRepository $repo)
    {
        $this->repo = $repo;
        $this->periods = new PeriodRange($repo);
    }

    public function loadPeriods()
    {
        $this->columns = [];
        $modify = sprintf('-%s months', $this->periodLength - 1);
        foreach ( $this->periods->getPeriods() as $endPeriod ) {
            $startDate = $endPeriod->getStartDate();
            $startDate->modify($modify);
            $startPeriod = $this->repo->findForDate($startDate);
            $this->columns[] = new ProfitAndLossColumn($startPeriod, $endPeriod);
        }
    }

    public function loadBalances(AccountBalanceRepository $repo)
    {
        foreach ( $this->columns as $report) {
            $report->loadBalances($repo);
        }
    }

    public function getSections()
    {
        $first = reset($this->columns);
        return (count($this) > 0) ? $first->getSections() : [];
    }

    public function getSectionName($sectionID)
    {
        $first = reset($this->columns);
        return (count($this) > 0) ? $first->getSectionName($sectionID) : null;
    }

    public function getSectionAnalysis($sectionID)
    {
        switch ($sectionID) {
            case AccountSection::COST_OF_GOODS_SOLD:
                return [self::GROSS_PROFIT, self::GROSS_MARGIN];
            case AccountSection::EXPENSES:
                return [self::PRETAX_PROFIT, self::PRETAX_MARGIN];
            case AccountSection::INCOME_TAXES:
                return [self::AFTER_TAX_PROFIT, self::AFTER_TAX_MARGIN];
            default:
                return [];
        }
    }

    /** @return ProfitAndLossColumn[] */
    public function getColumns()
    {
        return $this->columns;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->columns);
    }

    public function count()
    {
        return count($this->columns);
    }

}
