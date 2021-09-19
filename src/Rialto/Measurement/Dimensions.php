<?php

namespace Rialto\Measurement;

use Gumstix\Geometry\Vector2D;
use Gumstix\Geometry\Vector3D;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents the x, y, and z dimensions of an object.
 *
 * This class is immutable by design.
 *
 * @property-read float $x
 * @property-read float $y
 * @property-read float $z
 */
class Dimensions
{
    /**
     * Unit definitions.
     */
    const CENTIMETERS = 'cm';
    const MILLIMETERS = 'mm';

    private static $factor = [
        self::CENTIMETERS => 0.01,
        self::MILLIMETERS => 0.001
    ];

    /** @var Vector3D */
    private $vec;

    /** @var string */
    private $units;

    /**
     * @param float $x
     * @param float $y
     * @param float $z
     * @param string $units
     */
    public function __construct($x, $y, $z, $units=self::CENTIMETERS)
    {
        $this->vec = new Vector3D($x, $y, $z);
        $this->units = $units;
    }

    public static function zero(): self
    {
        return new self(0, 0, 0);
    }

    /**
     * @return float
     *
     * @Assert\NotBlank(message="X dimension cannot be blank.")
     * @Assert\Type(type="numeric", message="X dimension must be a number.")
     * @Assert\Range(
     *      min=0.0001,
     *      minMessage="X dimension must be at least {{ limit }} cm.")
     */
    public function getX()
    {
        return $this->vec->getX();
    }

    /**
     * @return float
     *
     * @Assert\NotBlank(message="Y dimension cannot be blank.")
     * @Assert\Type(type="numeric", message="Y dimension must be a number.")
     * @Assert\Range(
     *      min=0.0001,
     *      minMessage="Y dimension must be at least {{ limit }} cm.")
     */
    public function getY()
    {
        return $this->vec->getY();
    }

    /**
     * @return float
     * @Assert\NotBlank(message="Z dimension cannot be blank.")
     * @Assert\Type(type="numeric", message="Z dimension must be a number.")
     * @Assert\Range(
     *      min=0.0001,
     *      minMessage="Z dimension must be at least {{ limit }} cm.")
     */
    public function getZ()
    {
        return $this->vec->getZ();
    }

    /** @return Vector2D of the x and y components */
    public function getVector2D()
    {
        return $this->vec->to2D();
    }

    public function __get($name)
    {
        switch ($name) {
            case "x":
                return $this->getX();
            case "y":
                return $this->getY();
            case "z":
                return $this->getZ();
        }
        throw new InvalidArgumentException("class Dimensions has no such property $name");
    }

    /** @return float */
    public function getVolume()
    {
        return $this->x * $this->y * $this->z;
    }

    public function __toString()
    {
        return sprintf('%.4f x %.4f x %.4f',
            $this->x,
            $this->y,
            $this->z);
    }

    public function format2D($scale = 4)
    {
        $data = $this->toArray();
        unset($data['z']);
        return $this->format($data, $scale);
    }

    private function format(array $data, $scale)
    {
        $round = function($x) use ($scale) {
            return number_format($x, $scale);
        };
        $addUnits = function($x) {
            return "{$x}{$this->units}";
        };
        $rounded = array_map($round, $data);
        $withUnits = array_map($addUnits, $rounded);
        return join(' x ', $withUnits);
    }

    public function toArray()
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'z' => $this->z,
        ];
    }

    /**
     * Return a copy of this Dimensions in the specified units.
     */
    private function inUnits(string $units): Dimensions
    {
        $factor = self::$factor[$this->units] / self::$factor[$units];
        return new Dimensions(
            $this->x * $factor,
            $this->y * $factor,
            $this->z * $factor,
            $units
        );
    }

    /**
     * Return a normalized copy of this Dimensions in centimeters.
     */
    public function inCm(): Dimensions
    {
        return $this->inUnits(self::CENTIMETERS);
    }

    /**
     * Return a normalized copy of this Dimensions in millimeters.
     */
    public function inMm(): Dimensions
    {
        return $this->inUnits(self::MILLIMETERS);
    }

    /**
     * True if $this is larger than $other, irrespective of orientation.
     *
     * @param Dimensions $other
     * @param float $tolerance
     *   $this must be larger than $other by $tolerance in every dimension
     * @param integer $dimensions
     *   How many dimensions to check. Sometimes we only care about the largest
     *   two dimensions; for example: when trying to find bags that fit boards
     *   (bags being basically 2-dimensional)
     * @return boolean
     */
    public function isLargerThan(Dimensions $other, $tolerance = 0, $dimensions = 3)
    {
        $mine = array_values($this->inCm()->toArray());
        $theirs = array_values($other->inCm()->toArray());
        rsort($mine, SORT_NUMERIC);
        rsort($theirs, SORT_NUMERIC);

        for ($i = 0; $i < $dimensions; $i ++) {
            if ( $mine[$i] <= $theirs[$i] + $tolerance ) {
                return false;
            }
        }
        return true;
    }
}
