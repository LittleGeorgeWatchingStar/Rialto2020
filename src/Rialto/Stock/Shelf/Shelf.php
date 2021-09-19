<?php

namespace Rialto\Stock\Shelf;

use Doctrine\Common\Collections\ArrayCollection;
use Gumstix\Geometry\Vector3D;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A shelf in a rack on which bins are stored.
 */
class Shelf
{
    /**
     * @var string
     */
    private $id;

    /**
     * The position of this shelf on the rack. Starts at 1.
     *
     * @var int
     */
    private $indexNo;

    /**
     * Whether this shelf is for storing high-, medium-, or low-velocity
     * parts.
     *
     * @var string
     * @Assert\NotBlank
     */
    private $velocity = Velocity::LOW;

    /**
     * The positions or "slots" on this shelf in which a bin can be stored.
     *
     * @var ShelfPosition[]
     */
    private $positions;

    /**
     * The styles of bin that this shelf can accomodate.
     *
     * @var BinStyle[]
     * @Assert\Count(min=1, minMessage="At least one bin style is required.")
     */
    private $binStyles;

    /**
     * The rack of which this shelf is a member.
     *
     * @var Rack
     */
    private $rack;

    public function __construct()
    {
        $this->positions = new ArrayCollection();
        $this->binStyles = new ArrayCollection();
    }

    public function copyFrom(self $original)
    {
        $this->velocity = $original->getVelocity()->getValue();
        $this->binStyles = new ArrayCollection($original->getBinStyles());
        foreach ($original->getPositions() as $pos) {
            $this->positions[] = $pos->copyTo($this);
        }
    }

    /**
     * Factory method.
     *
     * @param Rack $rack
     * @param int $index
     * @return Shelf
     */
    public static function onRack(Rack $rack)
    {
        $s = new static();
        $s->rack = $rack;
        $s->indexNo = $rack->getNumShelves() + 1;
        return $s;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->getDescription();
    }

    private function getDescription()
    {
        return "shelf {$this->indexNo} on {$this->rack}";
    }

    /**
     * A compact identifier for this shelf, suitable for printing on a
     * bin label, where space is limited.
     */
    public function getShortLabel()
    {
        return sprintf('%s %d',
            $this->rack->getName(),
            $this->indexNo);
    }

    /**
     * @return Rack
     */
    public function getRack()
    {
        return $this->rack;
    }

    /**
     * @return int
     */
    public function getIndexNo()
    {
        return $this->indexNo;
    }

    public function isIndex($index)
    {
        return $index == $this->indexNo;
    }

    /**
     * @return ShelfPosition
     */
    public function createPosition(Vector3D $pos)
    {
        if ($this->hasPosition($pos)) {
            throw new \InvalidArgumentException("$this already has position $pos");
        }
        $p = ShelfPosition::onShelf($this, $pos);
        $this->positions[] = $p;
        return $p;
    }

    private function hasPosition(Vector3D $pos)
    {
        foreach ($this->positions as $sp) {
            if ($sp->isAt($pos)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return ShelfPosition[]
     */
    public function getPositions()
    {
        return $this->positions->getValues();
    }

    /**
     * @return Vector3D
     */
    public function getDimensions()
    {
        $dimensions = new Vector3D(0, 0, 0);
        foreach ($this->positions as $pos) {
            $dimensions = $this->getBoundingBox($dimensions, $pos->getBoundingBox());
        }
        return $dimensions;
    }

    private function getBoundingBox(Vector3D $a, Vector3D $b)
    {
        return new Vector3D(
            max($a->getX(), $b->getX()),
            max($a->getY(), $b->getY()),
            max($a->getZ(), $b->getZ()));
    }

    public function updateDimensions(Vector3D $newDimensions)
    {
        $this->addMissingDimensions($newDimensions);
        $this->removeExtraPositions($newDimensions);
    }

    private function addMissingDimensions(Vector3D $new)
    {
        for ($x = 1; $x <= $new->getX(); $x++) {
            for ($y = 1; $y <= $new->getY(); $y++) {
                for ($z = 1; $z <= $new->getZ(); $z++) {
                    $pos = new Vector3D($x, $y, $z);
                    if (!$this->hasPosition($pos)) {
                        $this->createPosition($pos);
                    }
                }
            }
        }
    }

    private function removeExtraPositions(Vector3D $bounds)
    {
        foreach ($this->positions as $pos) {
            if ($pos->isOutside($bounds)) {
                assertion(!$pos->isOccupied());
                $this->positions->removeElement($pos);
            }
        }
    }

    /**
     * The total number of bins this shelf can hold.
     *
     * @return int
     */
    public function getCapacity()
    {
        return count($this->positions);
    }

    /**
     * The number of occupied positions on this shelf.
     *
     * @return int
     */
    public function getNumOccupied()
    {
        $total = 0;
        foreach ($this->positions as $pos) {
            if ($pos->isOccupied()) {
                $total ++;
            }
        }
        return $total;
    }

    public function isOccupied()
    {
        return $this->getNumOccupied() > 0;
    }

    public function isAtFacility(Facility $facility)
    {
        return $this->rack->isAtFacility($facility);
    }

    /**
     * @return Facility
     */
    public function getFacility()
    {
        return $this->rack->getFacility();
    }

    /**
     * @return Velocity
     */
    public function getVelocity()
    {
        return new Velocity($this->velocity);
    }

    public function setVelocity(Velocity $velocity)
    {
        $this->velocity = $velocity->getValue();
    }

    /**
     * @return BinStyle[]
     */
    public function getBinStyles()
    {
        return $this->binStyles->getValues();
    }

    public function addBinStyle(BinStyle $style)
    {
        $this->binStyles[] = $style;
    }

    public function removeBinStyle(BinStyle $style)
    {
        $this->binStyles->removeElement($style);
    }
}
