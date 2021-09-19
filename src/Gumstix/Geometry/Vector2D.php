<?php

namespace Gumstix\Geometry;


class Vector2D
{
    /** @var float */
    private $x;

    /** @var float */
    private $y;

    public function __construct($x, $y)
    {
        $this->x = (float) $x;
        $this->y = (float) $y;
    }

    /**
     * Factory method.
     *
     * @return Vector2D
     */
    public static function origin()
    {
        return new self(0, 0);
    }

    /**
     * @return float
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return float
     */
    public function getY()
    {
        return $this->y;
    }

    public function __toString()
    {
        return sprintf('[%f, %f]', $this->x, $this->y);
    }

    public function toArray()
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
        ];
    }

    /**
     * @param Vector2D|null $other
     * @param int $places
     * @return bool True if the two vectors are equal to the given number
     *   of decimal places.
     */
    public function equals(Vector2D $other = null, $places = 4)
    {
        if (! $other) {
            return false;
        }
        $r = $this->round($places);
        $other = $other->round($places);
        return ($r->x == $other->x) && ($r->y == $other->y);
    }

    public function round($places = 4)
    {
        return new Vector2D(round($this->x, $places), round($this->y, $places));
    }

    /** @return Vector2D */
    public function add(Vector2D $other)
    {
        return new Vector2D($this->x + $other->x, $this->y + $other->y);
    }

    /** @return Vector2D */
    public function subtract(Vector2D $other)
    {
        return new Vector2D($this->x - $other->x, $this->y - $other->y);
    }

    /** @return Vector2D */
    public function scale($scalar)
    {
        return new Vector2D($this->x * $scalar, $this->y * $scalar);
    }
}
