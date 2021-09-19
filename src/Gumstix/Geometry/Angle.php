<?php

namespace Gumstix\Geometry;


class Angle
{
    /** @var float */
    private $radians;

    /**
     * Factory method
     *
     * @return Angle
     */
    public static function degrees($degrees)
    {
        return new self(deg2rad($degrees));
    }

    private function __construct($radians)
    {
        $this->radians = (float) $radians;
    }

    /**
     * Rotates the vector around the origin.
     *
     * @param Vector2D $vector
     * @return Vector2D
     */
    public function rotate(Vector2D $vector)
    {
        $x = $vector->getX();
        $y = $vector->getY();
        return new Vector2D(
            (cos($this->radians) * $x) - (sin($this->radians) * $y),
            (sin($this->radians) * $x) + (cos($this->radians) * $y)
        );
    }

}
