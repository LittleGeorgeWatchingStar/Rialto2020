<?php

namespace Gumstix\Geometry;


class Rectangle
{
    /** @var Vector2D */
    private $origin;

    /** @var Vector2D */
    private $opposite;

    /**
     * Factory method that creates a rectangle from its two opposite corners.
     *
     * @return Rectangle
     */
    public static function fromCorners(Vector2D $origin, Vector2D $opposite)
    {
        return new self($origin, $opposite);
    }

    /**
     * Factory method that creates a rectangle from its origin corner and
     * dimensions.
     *
     * @return Rectangle
     */
    public static function fromDimensions(Vector2D $origin, Vector2D $dimensions)
    {
        $opposite = $origin->add($dimensions);
        return new self($origin, $opposite);
    }

    private function __construct(Vector2D $origin, Vector2D $opposite)
    {
        $this->origin = $origin;
        $this->opposite = $opposite;
    }

    public function __toString()
    {
        return sprintf('%s -> %s', $this->origin, $this->opposite);
    }

    /**
     * @return Rectangle an equivalent rectangle whose origin is in the
     *   bottom-left and whose dimensions are positive.
     */
    public function normalize()
    {
        $origin = new Vector2D($this->getMinX(), $this->getMinY());
        $opposite = new Vector2D($this->getMaxX(), $this->getMaxY());
        return self::fromCorners($origin, $opposite);
    }

    /**
     * The position of the origin corner of this rectangle.
     *
     * @return Vector2D
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * The position of the corner opposite the origin of this rectangle.
     *
     * @return Vector2D
     */
    public function getOpposite()
    {
        return $this->opposite;
    }

    /**
     * The size of this rectangle.
     *
     * @return Vector2D
     */
    public function getDimensions()
    {
        return $this->opposite->subtract($this->origin);
    }

    public function equals(Rectangle $other = null)
    {
        if (! $other) {
            return false;
        }
        $other = $other->normalize();
        $n = $this->normalize();
        return $other->origin->equals($n->origin)
            && $other->opposite->equals($n->opposite);
    }

    /** @return Rectangle */
    public function rotate(Angle $angle)
    {
        return self::fromCorners(
            $angle->rotate($this->origin),
            $angle->rotate($this->opposite));
    }

    /**
     * @return bool True if $other is completely contained within $this.
     */
    public function contains(Rectangle $other)
    {
        return ($this->getMinX() <= $other->getMinX())
            && ($this->getMinY() <= $other->getMinY())
            && ($this->getMaxX() >= $other->getMaxX())
            && ($this->getMaxY() >= $other->getMaxY());
    }

    /**
     * @return bool True if this overlaps with $other.
     */
    public function overlaps(Rectangle $other)
    {
        return ($this->getMaxX() > $other->getMinX())
            && ($this->getMinX() < $other->getMaxX())
            && ($this->getMaxY() > $other->getMinY())
            && ($this->getMinY() < $other->getMaxY());
    }

    private function getMinX()
    {
        return min($this->origin->getX(), $this->getOpposite()->getX());
    }

    private function getMinY()
    {
        return min($this->origin->getY(), $this->getOpposite()->getY());
    }

    private function getMaxX()
    {
        return max($this->origin->getX(), $this->getOpposite()->getX());
    }

    private function getMaxY()
    {
        return max($this->origin->getY(), $this->getOpposite()->getY());
    }

}
