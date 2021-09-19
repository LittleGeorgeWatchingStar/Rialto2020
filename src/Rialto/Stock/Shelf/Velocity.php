<?php

namespace Rialto\Stock\Shelf;

use DateTimeInterface as Date;
use Gumstix\Time\DateRange;

/**
 * The "velocity" of an item is how often it moves to and from a facility.
 *
 * High-velocity parts should be stored for quick and easy access.
 */
class Velocity
{
    const HIGH = 'high';
    const MEDIUM = 'medium';
    const LOW = 'low';

    const HIGH_CUTOFF = '-6 months, 00:00:00.0'; // months ago
    const MEDIUM_CUTOFF = '-18 months, 00:00:00.0';

    /** @var string */
    private $value;

    /**
     * Factory method
     *
     * @param Date|string|null $date
     * @return Velocity
     */
    public static function fromDate($date)
    {
        $date = self::makeDate($date);
        if (null === $date) {
            return self::low();
        }
        if ($date >= self::makeDate(self::HIGH_CUTOFF)) {
            return self::high();
        } elseif ($date >= self::makeDate(self::MEDIUM_CUTOFF)) {
            return self::medium();
        } else {
            return self::low();
        }
    }

    /**
     * @param Date|string|null $date
     * @return Date|null
     */
    private static function makeDate($date)
    {
        return $date ? new \DateTimeImmutable($date) : null;
    }

    public static function high()
    {
        return new self(self::HIGH);
    }

    public static function medium()
    {
        return new self(self::MEDIUM);
    }

    public static function low()
    {
        return new self(self::LOW);
    }

    public function __construct($value)
    {
        assertion(self::isValid($value));
        $this->value = $value;
    }

    private static function isValid($value)
    {
        return in_array($value, [
            self::HIGH, self::MEDIUM, self::LOW,
        ]);
    }

    public static function getValidValues()
    {
        return [
            self::HIGH => self::high(),
            self::MEDIUM => self::medium(),
            self::LOW => self::low(),
        ];
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->getValue();
    }

    /**
     * @return DateRange
     */
    public function getDateRange()
    {
        $range = new DateRange();
        switch ($this->value) {
            case self::HIGH:
                return $range->withStart(self::HIGH_CUTOFF);
            case self::MEDIUM:
                return $range->withStart(self::MEDIUM_CUTOFF)
                    ->withEnd(self::HIGH_CUTOFF);
            default:
                return $range->withEnd(self::MEDIUM_CUTOFF);
        }
    }
}
