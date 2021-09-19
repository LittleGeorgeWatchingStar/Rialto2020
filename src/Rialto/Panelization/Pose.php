<?php

namespace Rialto\Panelization;


use Gumstix\Geometry\Angle;
use Gumstix\Geometry\Rectangle;
use Gumstix\Geometry\Vector2D;
use Rialto\Measurement\Dimensions;

/**
 * The position and orientation of a PlacedBoard on a Panel.
 *
 * The term "pose" comes from robotics:
 * @see https://en.wikipedia.org/wiki/Pose_(computer_vision)
 *
 * This class is immutable by design.
 */
class Pose
{
    /** Number of decimal places of precision. */
    const NUM_PLACES = 4;

    /**
     * The x-y coordinates.
     * @var Vector2D
     */
    private $position;

    /**
     * The rotation in degrees; must be a multiple of 90.
     * @var int
     */
    private $rotation;

    public function __construct($x, $y, $rotation)
    {
        $this->position = (new Vector2D($x, $y))->round(self::NUM_PLACES);
        $this->rotation = $rotation % 360;
        assertion($rotation >= 0);
        assertion($rotation % 90 == 0);
    }

    public static function fromVector(Vector2D $xy, int $rotation): self
    {
        return new self($xy->getX(), $xy->getY(), $rotation);
    }

    /**
     * @return float
     */
    public function getX()
    {
        return $this->position->getX();
    }

    /**
     * @return float
     */
    public function getY()
    {
        return $this->position->getY();
    }

    /**
     * @return int
     */
    public function getRotation()
    {
        return $this->rotation;
    }

    public function __toString()
    {
        static $degree = 'deg'; // "\u00b0";  // TODO: php7
        return sprintf("%.4f, %.4f @ %d$degree",
            $this->getX(),
            $this->getY(),
            $this->rotation);
    }

    public function translate(Vector2D $vector): Pose
    {
        $newXY = $this->position->add($vector);
        return self::fromVector($newXY, $this->rotation);
    }

    /**
     * Applies this pose as a transformation to the given pose.
     *
     * The use-case here is: Given the pose of a component on a board,
     * find the pose of that component on a panel, on which
     * the board will itself be positioned and rotated.
     *
     * Returns the new transformed pose.
     */
    public function transform(Pose $other): Pose
    {
        $vec = $this->transformVector($other->position);
        $rot = $this->rotation + $other->rotation;
        return Pose::fromVector($vec, $rot);
    }

    private function transformVector(Vector2D $vec): Vector2D
    {
        $a = Angle::degrees($this->rotation);
        $vec = $a->rotate($vec);
        $vec = $this->position->add($vec);
        return $vec->round(self::NUM_PLACES);
    }

    public function createRectangle(Dimensions $dimensions): Rectangle
    {
        $origin = $this->position;
        $size = $dimensions->getVector2D();
        $opposite = $this->transformVector($size);
        return Rectangle::fromCorners($origin, $opposite);
    }
}
