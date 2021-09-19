<?php

namespace Rialto\Stock\Shelf;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Stock\Facility\Facility;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A shelving rack in the warehouse on which bins are stored.
 *
 * @UniqueEntity(fields={"name"}, message="There is already a rack with that name.")
 */
class Rack
{
    /**
     * @var string
     */
    private $id;

    /**
     * The human-friendly name of this rack.
     *
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(
     *     max=10, maxMessage="stock.shelf.rack.name.length.max")
     * @Assert\Regex(pattern="/^[a-zA-Z]+$/",
     *     message="stock.shelf.rack.name.regex")
     */
    private $name;

    /**
     * Whether this rack has electrostatic discharge (ESD) protection.
     *
     * @var bool
     */
    private $esdProtection = false;

    /**
     * The shelves on this rack.
     *
     * @var Shelf[]
     * @Assert\Valid(traverse=true)
     */
    private $shelves;

    /**
     * The facility at which this rack is located.
     *
     * @var Facility
     */
    private $facility;

    public function __construct()
    {
        $this->shelves = new ArrayCollection();
    }

    /**
     * Factory method
     *
     * @return Rack
     */
    public static function atFacility(Facility $facility)
    {
        $r = new static();
        $r->facility = $facility;
        return $r;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = trim($name);
    }

    public function __toString()
    {
        return "rack {$this->name}";
    }

    /**
     * @return Shelf
     */
    public function createShelf()
    {
        $shelf = Shelf::onRack($this);
        $this->shelves[] = $shelf;
        return $shelf;
    }

    public function cloneShelf($indexNo)
    {
        $original = $this->getShelf($indexNo);
        $clone = $this->createShelf();
        $clone->copyFrom($original);
        return $clone;
    }

    private function getShelf($indexNo)
    {
        foreach ($this->shelves as $shelf) {
            if ($shelf->isIndex($indexNo)) {
                return $shelf;
            }
        }
        return null;
    }

    /**
     * Removes the highest-indexed shelf.
     */
    public function removeShelf()
    {
        $shelf = $this->getLastShelf();
        assertion(null !== $shelf);
        assertion(!$shelf->isOccupied());
        $this->shelves->removeElement($shelf);
    }

    /**
     * The shelf with the highest index number.
     * @return Shelf|null
     */
    public function getLastShelf()
    {
        return $this->getShelf($this->getNumShelves());
    }

    /**
     * @return Shelf[]
     */
    public function getShelves()
    {
        return $this->shelves->getValues();
    }

    public function getNumShelves()
    {
        return count($this->shelves);
    }

    /**
     * The total number of bins this rack can hold.
     *
     * @return int
     */
    public function getCapacity()
    {
        $total = 0;
        foreach ($this->shelves as $shelf) {
            $total += $shelf->getCapacity();
        }
        return $total;
    }

    /**
     * The number of occupied positions on this rack.
     *
     * @return int
     */
    public function getNumOccupied()
    {
        $total = 0;
        foreach ($this->shelves as $shelf) {
            $total += $shelf->getNumOccupied();
        }
        return $total;
    }

    public function isOccupied()
    {
        return $this->getNumOccupied() > 0;
    }

    public function isAtFacility(Facility $facility)
    {
        return $this->facility->equals($facility);
    }

    /**
     * @return Facility
     */
    public function getFacility()
    {
        return $this->facility;
    }

    /**
     * @return bool
     */
    public function hasEsdProtection()
    {
        return $this->esdProtection;
    }

    /**
     * @param bool $esdProtection
     */
    public function setEsdProtection($esdProtection)
    {
        $this->esdProtection = $esdProtection;
    }
}
