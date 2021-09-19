<?php

namespace Rialto\Measurement\Temperature;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class TemperatureRange
{
    /**
     * @var float|null
     *
     * @Assert\Type(type="numeric", message="Min temperature must be a number.")
     * @Assert\Range(
     *     min=-1000, minMessage="Min temperature cannot be less than {{ limit }}.",
     *     max=1000, maxMessage="Min temperature cannot be more than {{ limit }}.")
     */
    private $min;

    /**
     * @var float|null
     *
     * @Assert\Type(type="numeric", message="Max temperature must be a number.")
     * @Assert\Range(
     *     min=-1000, minMessage="Max temperature cannot be less than {{ limit }}.",
     *     max=1000, maxMessage="Max temperature cannot be more than {{ limit }}.")
     */
    private $max;

    /**
     * Factory method that returns a completely unspecified temperature range.
     * @return TemperatureRange
     */
    public static function unspecified()
    {
        return new self(null, null);
    }

    public function __construct($min, $max)
    {
        $this->min = $min;
        $this->max = $max;
    }

    public function __toString()
    {
        return $this->format();
    }

    /**
     * Represents this temperature range as a string; eg:
     *
     *   $range->format() => "-5 ≤ T ≤ 80"
     *   $range->format('temp') => "-5 ≤ temp ≤ 80"
     * @param string $string For example, "T" (default), or "temp", etc.
     * @return string
     */
    public function format($string = 'T')
    {
        if (! ($this->hasMin() || $this->hasMax())) {
            return 'unspecified';
        }
        if ($this->hasMin()) {
            $string = sprintf('%.1f ≤ %s', $this->min, $string);
        }
        if ($this->hasMax()) {
            $string .= sprintf(' ≤ %.1f', $this->max);
        }
        return $string;
    }

    /**
     * @Assert\Callback
     */
    public function validateMaxIsGreater(ExecutionContextInterface $context)
    {
        if ($this->isSpecified() && ($this->min >= $this->max)) {
            $context->buildViolation("Min temperature must be less than max.")
                ->atPath('min')
                ->addViolation();
        }
    }

    /**
     * @return bool Has both a min and a max.
     */
    public function isSpecified()
    {
        return $this->hasMin() && $this->hasMax();
    }

    /**
     * @return float|null
     */
    public function getMin()
    {
        return $this->min;
    }

    private function hasMin()
    {
        return null !== $this->min;
    }

    /**
     * @return float|null
     */
    public function getMax()
    {
        return $this->max;
    }

    private function hasMax()
    {
        return null !== $this->max;
    }

    /** @return TemperatureRange */
    public function intersection(TemperatureRange $other = null)
    {
        if (null === $other) {
            return $this;
        }
        $min = $this->maxNotNull($this->min, $other->min);
        $max = $this->minNotNull($this->max, $other->max);
        return new self($min, $max);
    }

    private static function minNotNull($a, $b)
    {
        if (null === $a) {
            return $b;
        } elseif (null === $b) {
            return $a;
        } else {
            return min($a, $b);
        }
    }
    private static function maxNotNull($a, $b)
    {
        if (null === $a) {
            return $b;
        } elseif (null === $b) {
            return $a;
        } else {
            return max($a, $b);
        }
    }

    public function isWithin(TemperatureRange $other)
    {
        if ($other->hasMin()) {
            if (!$this->hasMin()) {
                return false;
            } elseif ($this->min < $other->min) {
                return false;
            }
        }
        if ($other->hasMax()) {
            if (!$this->hasMax()) {
                return false;
            } elseif ($this->max > $other->max) {
                return false;
            }
        }
        return true;
    }
}
