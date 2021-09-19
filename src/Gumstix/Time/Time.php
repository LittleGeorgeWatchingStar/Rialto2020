<?php

namespace Gumstix\Time;

use DateTime;


/**
 * Used to retrieve instances of the DateTime class to allow for simpler unit
 * testing.
 */
class Time
{
    /**
     * This is a a fake of the current time which is set to a desired time
     * during a test.
     *
     * @var DateTime
     */
    private static $testTime = null;

    /**
     * @param string|DateTime $dateTime
     */
    public static function setTime($dateTime)
    {
        if ($dateTime instanceof DateTime) {
            self::$testTime = clone $dateTime;
        } else {
            self::$testTime = new DateTime($dateTime);
        }
    }

    /**
     * @param string $time
     * @return DateTime
     */
    public static function getTime($time)
    {
        if (self::$testTime) {
            $clonedTime = clone self::$testTime;
            return $clonedTime->modify($time);
        } else {
            return new DateTime($time);
        }
    }

    /**
     * @return DateTime
     */
    public static function now()
    {
        if (self::$testTime) {
            self::$testTime;
        }
        return self::getTime('+0 seconds');
    }
}
