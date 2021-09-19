<?php

namespace Gumstix\Geometry;


class Vector3D
{
    /** @var Vector2D */
    private $xy;

    /** @var float */
    private $z;

    public function __construct($x, $y, $z)
    {
        $this->xy = new Vector2D($x, $y);
        $this->z = (float) $z;
    }

    /**
     * @return float
     */
    public function getX()
    {
        return $this->xy->getX();
    }

    /**
     * @return float
     */
    public function getY()
    {
        return $this->xy->getY();
    }

    /**
     * @return Vector2D of the x and y components
     */
    public function to2D()
    {
        return $this->xy;
    }

    /**
     * @return float
     */
    public function getZ()
    {
        return $this->z;
    }

    public function __toString()
    {
        return sprintf('[%f, %f, %f]', $this->getX(), $this->getY(), $this->z);
    }

    public function toArray()
    {
        return [
            'x' => $this->getX(),
            'y' => $this->getY(),
            'z' => $this->getZ(),
        ];
    }

    /**
     * @param int $places
     * @return bool True if the two vectors are equal to the given number
     *   of decimal places.
     */
    public function equals(Vector3D $other = null, $places = 4)
    {
        if (! $other) {
            return false;
        }
        $r = $this->round($places);
        $other = $other->round($places);
        return ($r->xy->equals($other->xy))
            && ($r->getZ() == $other->getZ());
    }

    private function round($places = 4)
    {
        return new Vector3D(
            round($this->getX(), $places),
            round($this->getY(), $places),
            round($this->getZ(), $places));
    }

    /** @return Vector3D */
    public function translate(Vector3D $other)
    {
        return $this->add($other);
    }

    /** @return Vector3D */
    public function add(Vector3D $other)
    {
        return new Vector3D(
            $this->getX() + $other->getX(),
            $this->getY() + $other->getY(),
            $this->getZ() + $other->getZ());
    }
}
