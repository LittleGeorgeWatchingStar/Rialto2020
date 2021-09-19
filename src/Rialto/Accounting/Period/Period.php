<?php

namespace Rialto\Accounting\Period;

use DateTime;
use Rialto\Accounting\Period\Orm\PeriodRepository;
use Rialto\Database\Orm\DbManager;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;


class Period implements RialtoEntity
{
    /**************\
     STATIC MEMBERS
    \**************/

    /** @return Period */
    public static function fetchCurrent(DbManager $dbm = null)
    {
        $dbm = $dbm ?: ErpDbManager::getInstance();
        return $dbm->getRepository(self::class)->findCurrent();
    }

    /**
     * @deprecated
     */
    public static function fetchForDate(DateTime $date)
    {
        $dbm = ErpDbManager::getInstance();
        return self::findForDate($date, $dbm);
    }

    /** @return Period */
    public static function findForDate(DateTime $date, DbManager $dbm)
    {
        /** @var $repo PeriodRepository */
        $repo = $dbm->getRepository(self::class);
        return $repo->findForDate($date);
    }


    /****************\
     INSTANCE MEMBERS
    \****************/

    private $id;
    private $endDate;

    public function __construct(DateTime $endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getEndDate()
    {
        $endDate = clone $this->endDate;
        $endDate->setTime(23, 59, 59);
        return $endDate;
    }

    /** @return string */
    public function formatEndDate($format)
    {
        return $this->getEndDate()->format($format);
    }

    /** @return DateTime */
    public function getStartDate()
    {
        $startDate = clone $this->endDate;
        $year = (int) $startDate->format('Y');
        $month = (int) $startDate->format('m');
        $startDate->setDate($year, $month, 1);
        $startDate->setTime(0, 0, 0);
        return $startDate;
    }

    /** @return string */
    public function formatStartDate($format)
    {
        return $this->getStartDate()->format($format);
    }

    public function getLabel()
    {
        return sprintf('%s - %s', $this->id, $this->getStartDate()->format('M Y'));
    }

    public function __toString()
    {
        return $this->getLabel();
    }

    public function contains(DateTime $date)
    {
        return ($this->getStartDate() <= $date) &&
            ($this->getEndDate() >= $date);
    }
}
