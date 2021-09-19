<?php

namespace Rialto\Stock\Shelf;

use Gumstix\Geometry\Vector3D;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;

/**
 * A location on a shelf that can store at most one bin.
 */
class ShelfPosition
{
    /**
     * The number of decimal places to which we track shelf position.
     */
    const SCALE = 0;

    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $x;

    /**
     * @var int
     */
    private $y;

    /**
     * @var int
     */
    private $z;

    /**
     * @var Shelf
     */
    private $shelf;

    /**
     * The bin in this position, if any.
     *
     * @var StockBin|null
     */
    private $bin = null;

    /**
     * Factory method.
     *
     * @return ShelfPosition
     */
    public static function onShelf(Shelf $shelf, Vector3D $pos)
    {
        static::validatePosition($pos);
        $p = new static();
        $p->shelf = $shelf;
        $p->x = $pos->getX();
        $p->y = $pos->getY();
        $p->z = $pos->getZ();
        return $p;
    }

    private static function validatePosition(Vector3D $pos)
    {
        foreach ($pos->toArray() as $coord => $value) {
            if ($value <= 0) {
                throw new \InvalidArgumentException("$coord coordinate must be positive");
            }
        }
    }

    /**
     * @return ShelfPosition
     */
    public function copyTo(Shelf $newShelf)
    {
        return self::onShelf($newShelf, $this->getPosition());
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function isAt(Vector3D $pos)
    {
        return $pos->equals($this->getPosition(), self::SCALE);
    }

    private function getPosition()
    {
        return new Vector3D($this->x, $this->y, $this->z);
    }

    /**
     * @return Vector3D A vector that is large enough to contain this position.
     */
    public function getBoundingBox()
    {
        return $this->getPosition();
    }

    /**
     * If this position lies outside the limits of the given dimensions.
     *
     * Used when resizing shelves.
     *
     * @return bool
     */
    public function isOutside(Vector3D $dimensions)
    {
        return ($this->x > $dimensions->getX())
            || ($this->y > $dimensions->getY())
            || ($this->z > $dimensions->getZ());
    }

    /**
     * Allows warehouse staff to identify this shelf position.
     *
     * @return string
     */
    public function getShortLabel()
    {
        return sprintf("%s [%d, %d, %d]",
            $this->shelf->getShortLabel(),
            $this->x, $this->y, $this->z);
    }

    public function __toString()
    {
        return $this->getShortLabel();
    }

    public function isAtFacility(Facility $facility)
    {
        return $this->shelf->isAtFacility($facility);
    }

    /**
     * @return Facility
     */
    public function getFacility()
    {
        return $this->shelf->getFacility();
    }

    /**
     * @internal for use by StockBin only
     */
    public function setBin(StockBin $bin)
    {
        $this->bin = $bin;
    }

    /**
     * @internal for use by StockBin only
     */
    public function clearBin()
    {
        $this->bin = null;
    }

    public function isOccupied()
    {
        return null !== $this->bin;
    }

}
