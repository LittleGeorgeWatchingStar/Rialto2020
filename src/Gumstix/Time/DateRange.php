<?php

namespace Gumstix\Time;

use \DateTimeInterface as Date;

/**
 * A date range. Start and end can be null to indicate an open-ended range.
 */
class DateRange
{
    /**
     * @var Date|null
     */
    private $start = null;

    /**
     * @var Date|null
     */
    private $end = null;

    /**
     * Convenience factory method for fluent interface, eg:
     *
     *     $range = DateRange::create()->withStart('-1 year')
     *
     * @return DateRange
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @param Date|string $start
     * @return DateRange
     */
    public function withStart($start)
    {
        $new = new static();
        $new->start = static::normalize($start);
        $new->end = static::normalize($this->end);
        return $new;
    }

    /**
     * @param Date|string $end
     * @return DateRange
     */
    public function withEnd($end)
    {
        $new = new static();
        $new->start = static::normalize($this->start);
        $new->end = static::normalize($end);
        return $new;
    }

    /**
     * Convert $string to Date, if it isn't one already.
     *
     * @param Date|string $date
     * @return Date
     */
    private static function normalize($date)
    {
        return ($date instanceof Date) ? clone $date : self::stringToDate($date);
    }

    /**
     * @param string $string
     * @return Date|null
     */
    private static function stringToDate($string)
    {
        return $string ? new \DateTimeImmutable($string) : null;
    }

    public function hasStart()
    {
        return null !== $this->start;
    }

    /**
     * @return Date|null
     */
    public function getStart()
    {
        return $this->start ? clone $this->start : null;
    }

    public function formatStart($format)
    {
        return $this->start ? $this->start->format($format) : '';
    }

    public function hasEnd()
    {
        return null !== $this->end;
    }

    /**
     * @return Date|null
     */
    public function getEnd()
    {
        return $this->end ? clone $this->end : null;
    }

    public function formatEnd($format)
    {
        return $this->end ? $this->end->format($format) : '';
    }

    public function __toString()
    {
        $start = $this->formatStart(DATE_ISO8601);
        $start = $start ? "from $start" : '';
        $end = $this->formatEnd(DATE_ISO8601);
        $end = $end ? "until $end" : '';
        return trim("$start $end");
    }

    /**
     * True if this range, inclusive, contains $date. True if $date is
     * equal to either the start or end date of this range.
     *
     * @param Date $date
     * @return bool
     */
    public function contains(Date $date)
    {
        $startIsOpen = !$this->hasStart();
        $containsStart = $startIsOpen || $this->start <= $date;

        $endIsOpen = !$this->hasEnd();
        $containsEnd = $endIsOpen || $this->end >= $date;

        return $containsStart && $containsEnd;
    }
}
