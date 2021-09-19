<?php

namespace Rialto\Accounting\Period;


use Rialto\Accounting\Period\Orm\PeriodRepository;

/**
 * For selecting a range of periods.
 */
class PeriodRange
{
    /** @var PeriodRepository */
    private $repo;

    /** @var Period */
    public $lastPeriod;

    /** @var int  */
    public $numPeriods = 2;

    /** @var int  */
    public $interval = 12;

    public function __construct(PeriodRepository $repo)
    {
        $this->repo = $repo;
        $this->lastPeriod = $repo->findCurrent();
    }

    /**
     * Convert the user inputs $lastPeriod, $numPeriods, and $interval into
     * an array of Periods.
     *
     * @param PeriodRepository $repo
     * @return Period[]
     */
    public function getPeriods()
    {
        $periods = [$this->lastPeriod];
        $date = $this->lastPeriod->getStartDate();
        $modify = sprintf('-%s months', $this->interval);
        for ($i = 1; $i < $this->numPeriods; $i++) {
            $date->modify($modify);
            if($this->repo->checkIfPeriodRangeIsValid($date)) {
                $period = $this->repo->findForDate($date);
                $periods[] = $period;
            }
        }
        return $periods;
    }
}
