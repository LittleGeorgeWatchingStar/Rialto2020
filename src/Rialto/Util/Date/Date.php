<?php


namespace Rialto\Util\Date;


use DateTime;
use DateTimeInterface;

class Date
{
    /**
     * Converts (possibly null) DateTimeInterface objects into JSON-friendly
     * ISO strings.
     *
     * @param DateTimeInterface|null $date
     * @return null|string
     */
    public static function toIso(DateTimeInterface $date = null)
    {
        return (null === $date) ? null : $date->format(DateTime::ISO8601);
    }
}