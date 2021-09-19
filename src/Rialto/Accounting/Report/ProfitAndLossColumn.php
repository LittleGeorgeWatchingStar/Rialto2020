<?php

namespace Rialto\Accounting\Report;


use Rialto\Accounting\Balance\Orm\AccountBalanceRepository;
use Rialto\Accounting\Ledger\Account\AccountSection;
use Rialto\Accounting\Period\Period;

class ProfitAndLossColumn
{
    /** @var Period */
    private $startPeriod;

    /** @var Period */
    private $endPeriod;

    /** @var float[section]  */
    private $sectionTotals = [];

    /** @var float[accountID] */
    private $amounts = [];

    /** @var string[section][group][accountID] */
    private $accounts = [];

    /** @var string[sectionID] */
    private $sections = [];

    public function __construct(Period $start, Period $end)
    {
        $this->startPeriod = $start;
        $this->endPeriod = $end;
    }

    /**
     * @return Period
     */
    public function getStartPeriod()
    {
        return $this->startPeriod;
    }

    /**
     * @return Period
     */
    public function getEndPeriod()
    {
        return $this->endPeriod;
    }

    /** @return string */
    public function formatDates($format)
    {
        return sprintf('%s to %s',
            $this->startPeriod->formatStartDate($format),
            $this->endPeriod->formatEndDate($format));
    }

    public function loadBalances(AccountBalanceRepository $repo)
    {
        $data = $repo->findForProfitAndLoss($this->startPeriod, $this->endPeriod);
        $this->buildIndex($data);
    }

    private function buildIndex(array $data)
    {
        $this->amounts = [];
        $this->sectionTotals = [];
        $this->accounts = [];
        foreach ( $data as $row ) {
            $sectionID = $row['sectionID'];
            $sectionName = $row['sectionName'];
            $groupName = $row['groupName'];
            $accountID = $row['accountID'];
            $accountName = $row['accountName'];
            $amount = $row['amount'];

            $this->amounts[$accountID] = $amount;
            $this->accounts[$sectionID][$groupName][$accountID] = $accountName;
            $this->sections[$sectionID] = $sectionName;

            if (! isset($this->sectionTotals[$sectionID])) {
                $this->sectionTotals[$sectionID] = 0;
            }
            $this->sectionTotals[$sectionID] += $amount;
        }
    }

    public function getSections()
    {
        return $this->accounts;
    }

    public function getSectionName($sectionID)
    {
        return $this->sections[$sectionID];
    }

    public function getAmount($accountID)
    {
        return $this->amounts[$accountID];
    }

    public function getSectionTotal($sectionID)
    {
        return $this->sectionTotals[$sectionID];
    }

    /**
     * @return string
     */
    public function getSectionAnalysis($name)
    {
        switch ($name) {
            case ProfitAndLossReport::GROSS_PROFIT:
                return $this->formatMoney($this->getGrossProfit());

            case ProfitAndLossReport::GROSS_MARGIN:
                return $this->formatPercentage($this->getGrossMargin());

            case ProfitAndLossReport::PRETAX_PROFIT:
                return $this->formatMoney($this->getPretaxProfit());

            case ProfitAndLossReport::PRETAX_MARGIN:
                return $this->formatPercentage($this->getPretaxMargin());

            case ProfitAndLossReport::AFTER_TAX_PROFIT:
                return $this->formatMoney($this->getAfterTaxProfit());

            case ProfitAndLossReport::AFTER_TAX_MARGIN:
                return $this->formatPercentage($this->getAfterTaxMargin());

            default:
                throw new \UnexpectedValueException("Unknown analysis $name");
        }
    }

    private function formatMoney($amount)
    {
        return number_format($amount, 0);
    }

    private function formatPercentage($ratio)
    {
        return sprintf('%s%%', number_format($ratio * 100, 1));
    }

    /** @return float */
    private function getGrossProfit()
    {
        return $this->getSectionTotal(AccountSection::INCOME)
            + $this->getSectionTotal(AccountSection::COST_OF_GOODS_SOLD);
    }

    /** @return float */
    private function getGrossMargin()
    {
        return $this->getSectionMargin($this->getGrossProfit(), AccountSection::INCOME);
    }

    private function getSectionMargin($numerator, $sectionID)
    {
        $total = $this->getSectionTotal($sectionID);
        if ($total == 0) {
            return NAN;
        }
        return $numerator / $total;
    }

    /** @return float */
    private function getPretaxProfit()
    {
        return $this->getGrossProfit()
            + $this->getSectionTotal(AccountSection::EXPENSES);
    }

    /** @return float */
    private function getPretaxMargin()
    {
        return $this->getSectionMargin($this->getPretaxProfit(), AccountSection::INCOME);
    }

    /** @return float */
    private function getAfterTaxProfit()
    {
        return $this->getPretaxProfit()
            + $this->getSectionTotal(AccountSection::INCOME_TAXES);
    }

    /** @return float */
    private function getAfterTaxMargin()
    {
        return $this->getSectionMargin($this->getAfterTaxProfit(), AccountSection::INCOME);
    }

    public function formatPeriod($format)
    {
        return $this->endPeriod->formatEndDate($format);
    }
}
