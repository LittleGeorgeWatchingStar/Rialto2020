<?php

namespace Rialto\Accounting\Period\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Accounting\Period\Period;
use Rialto\Database\Orm\FilteringRepositoryAbstract;

class PeriodRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('p');
        return $builder->buildQuery($params);
    }

    /** @return Period */
    public function findCurrent()
    {
        return $this->findForDate(new \DateTime());
    }

    /**
     * @return QueryBuilder
     */
    public function queryRecent(\DateTime $since = null)
    {
        if (null === $since) {
            $since = new \DateTime();
            $since->modify('-2 year');
        }
        $qb = $this->createQueryBuilder('period');
        $qb->where('period.endDate >= :since')
            ->setParameter('since', $since)
            ->orderBy('period.id', 'desc');
        return $qb;
    }

    /**
     * Check if the period range is valid in the first place
     *
     * @param \DateTime|string $date
     * @return boolean
     */
    public function checkIfPeriodRangeIsValid($date){

        if (is_string($date)) {
            $date = new \DateTime($date);
        } else {
            $date = clone $date; // defensive copy
        }
        $date->setTime(0, 0, 0);

        if ($this->dateIsTooEarly($date)) {
            return false;
        } else if ($this->dateIsTooLate($date)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns the accounting period for the given date.
     *
     * @param \DateTime|string $date
     * @return Period
     */
    public function findForDate($date)
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        } else {
            $date = clone $date; // defensive copy
        }
        $date->setTime(0, 0, 0);

        if ($this->dateIsTooEarly($date)) {
            throw new \InvalidArgumentException(sprintf(
                "Date '%s' is before the first period in the system",
                $date->format('Y-m-d')
            ));
        }
        if ($this->dateIsTooLate($date)) {
            throw new \InvalidArgumentException(sprintf(
                "Date '%s' is too far in the future", $date->format('Y-m-d')));
        }

        $qb = $this->createQueryBuilder('period')
            ->where('period.endDate >= :date')
            ->orderBy('period.endDate', 'ASC')
            ->setParameter('date', $date)
            ->setMaxResults(1);

        $query = $qb->getQuery();
        while (null == ($period = $query->getOneOrNullResult())) {
            $this->addPeriod();
        }
        return $period;
    }

    private function dateIsTooEarly(\DateTime $date)
    {
        /* Make sure the given date is not before the first date
         * in the system. */
        $lastInMonth = $this->getLastDateOfMonth($date);
        $qb = $this->createQueryBuilder('period')
            ->where('period.endDate <= :lastInMonth')
            ->orderBy('period.endDate', 'DESC')
            ->setParameter('lastInMonth', $lastInMonth)
            ->setMaxResults(1);

        $prev = $qb->getQuery()->getOneOrNullResult();
        return !$prev;
    }

    private function dateIsTooLate(\DateTime $date)
    {
        $now = new \DateTime();
        $diff = $date->diff($now);
        static $maxYearsInFuture = 5;
        return ($date > $now) && ($diff->y >= $maxYearsInFuture);
    }

    private function getLastDateOfMonth(\DateTime $date)
    {
        $time = $date->getTimestamp();
        $dateArray = getdate($time);
        $month = $dateArray['mon'];
        $year = $dateArray['year'];
        $lastDayTimestamp = mktime(0, 0, 0, $month + 1, 0, $year);
        $lastDate = new \DateTime();
        $lastDate->setTimestamp($lastDayTimestamp);
        return $lastDate;
    }


    /**
     * Adds a new period to the end of the list; that is, after the last
     * period currently on the list.
     */
    private function addPeriod()
    {
        $conn = $this->_em->getConnection();
        $sql = 'INSERT INTO Periods (LastDate_in_Period)
            SELECT adddate(
                adddate(
                    adddate(
                        LastDate_in_Period, INTERVAL 1 DAY
                    ), INTERVAL 1 MONTH
                ), INTERVAL -1 DAY
            )
            FROM Periods
            ORDER BY PeriodNo DESC LIMIT 1';

        $conn->exec($sql);
        $periodNo = $conn->lastInsertId();
        $this->updateChartDetails($periodNo);
    }

    private function updateChartDetails($periodNo)
    {
        $sql = 'INSERT INTO ChartDetails (AccountCode, Period)
            SELECT AccountCode, :periodNo FROM ChartMaster';
        $conn = $this->_em->getConnection();
        $conn->executeUpdate($sql, [
            'periodNo' => $periodNo
        ]);
    }
}
