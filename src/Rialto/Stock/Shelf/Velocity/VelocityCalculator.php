<?php

namespace Rialto\Stock\Shelf\Velocity;

use DateTime;
use Gumstix\Time\DateRange;
use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Shelf\Velocity;

/**
 * Calculates the "velocity" of a stock item; that is, how often a stock
 * item moves to and from a facility.
 */
class VelocityCalculator
{
    const DEFAULT_SINCE = '-2 years';

    /** @var DbManager */
    private $dbm;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
    }

    /**
     * @return Velocity
     */
    public function getVelocity(Item $item, Facility $facility)
    {
        return $this->getVelocitySince($item, $facility, new DateTime(self::DEFAULT_SINCE));
    }

    /**
     * @return Velocity
     */
    private function getVelocitySince(Item $item, Facility $facility, DateTime $since)
    {
        $qb = new VelocityQueryBuilder($this->dbm);
        $qb->byItem($item);
        $qb->byFacility($facility);
        $range = DateRange::create()->withStart($since);
        $qb->byDates($range);
        return $qb->getSingleResult();
    }
}
