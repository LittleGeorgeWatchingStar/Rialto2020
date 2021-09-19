<?php

namespace Rialto\Sales\Stats;

use Rialto\Purchasing\LeadTime\LeadTimeCalculator;
use Rialto\Sales\Price\Orm\ProductPriceRepository;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Level\StockLevelService;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Options for the sales stats report.
 */
class SalesStatOptions
{
    const DEFAULT_TARGET_DAYS = 60;
    const DEFAULT_START_DATE = '-6 months';

    /**
     * @var integer
     * @Assert\Type(type="integer")
     * @Assert\Range(min=1)
     */
    private $targetDays;

    /**
     * @var \DateTime
     * @Assert\Date
     */
    private $startDate;

    /** @var string[] */
    private $filters = [];

    /** @var SalesType */
    private $salesType = null;

    /** @var StockLevelService */
    private $stockLevels = null;

    /** @var ProductPriceRepository */
    private $prices = null;

    /** @var LeadTimeCalculator */
    private $leadTimeCalculator = null;

    public function __construct()
    {
        $this->targetDays = self::DEFAULT_TARGET_DAYS;
        $this->startDate = new \DateTime();
        $this->startDate->modify(self::DEFAULT_START_DATE);
    }

    public function getTargetDays()
    {
        return $this->targetDays;
    }

    public function setTargetDays($targetDays)
    {
        $this->targetDays = $targetDays;
    }

    public function getStartDate()
    {
        return clone $this->startDate;
    }

    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = clone $startDate;
    }

    public function getSalesType()
    {
        return $this->salesType;
    }

    public function setSalesType(SalesType $salesType = null)
    {
        $this->salesType = $salesType;
    }

    private function getNumDays()
    {
        return (time() - $this->startDate->getTimestamp()) / (24 * 60 * 60);
    }

    public function setStockLevels(StockLevelService $stockLevels)
    {
        $this->stockLevels = $stockLevels;
    }

    public function setPrices(ProductPriceRepository $prices)
    {
        $this->prices = $prices;
    }

    public function setLeadTimeCalculator(LeadTimeCalculator $leadTimeCalculator)
    {
        $this->leadTimeCalculator = $leadTimeCalculator;
    }

    public function configureStat(SalesStat $stat)
    {
        $stat->setNumDays($this->getNumDays());
        $stat->setTargetDays($this->targetDays);

        if ($this->stockLevels) {
            $stat->loadStockLevels($this->stockLevels);
        }

        if ($this->prices) {
            $stat->setPrice($this->prices);
        }

        if ($this->leadTimeCalculator) {
            $stat->setLeadTime($this->leadTimeCalculator);
        }
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    public static function getFilterChoices()
    {
        return [
            'on order' => SalesStat::STATUS_ON_ORDER,
            'insufficient' => SalesStat::STATUS_INSUFFICIENT,
        ];
    }

    public function applyFilter(array $stats)
    {
        $allow = $this->filters;
        if (count($allow) == 0) {
            return $stats;
        }
        return array_filter($stats, function (SalesStat $stat) use ($allow) {
            return in_array($stat->getStockStatus(), $allow);
        });
    }
}
