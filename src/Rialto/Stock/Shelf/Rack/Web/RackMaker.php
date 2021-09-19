<?php

namespace Rialto\Stock\Shelf\Rack\Web;

use Doctrine\Common\Collections\ArrayCollection;
use Gumstix\Geometry\Vector3D;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Shelf\Rack;
use Rialto\Stock\Shelf\Velocity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * For creating new racks.
 */
class RackMaker
{
    /**
     * @var Facility
     * @Assert\NotNull
     */
    public $facility;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(
     *     max=10, maxMessage="stock.shelf.rack.name.length.max")
     * @Assert\Regex(pattern="/^[a-zA-Z]+$/",
     *     message="stock.shelf.rack.name.regex")
     */
    public $name;

    /**
     * @var int
     * @Assert\NotBlank
     * @Assert\Range(min=1, max=100)
     */
    public $numShelves;

    /**
     * @var int
     * @Assert\NotBlank
     * @Assert\Range(min=1, max=1000)
     */
    public $positionsPerShelf;

    /**
     * @var Velocity
     * @Assert\NotNull
     */
    public $defaultVelocity;

    /**
     * @var BinStyle[]|ArrayCollection
     * @Assert\Count(min=1, minMessage="Choose at least one bin style.")
     */
    private $binStyles;

    /**
     * @var bool
     */
    public $esdProtection = false;

    public function __construct()
    {
        $this->binStyles = new ArrayCollection();
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

    /**
     * @return Rack
     */
    public function makeRack()
    {
        $rack = Rack::atFacility($this->facility);
        $rack->setName($this->name);
        $rack->setEsdProtection($this->esdProtection);
        for ($index = 0; $index < $this->numShelves; $index++) {
            $shelf = $rack->createShelf();
            $shelf->setVelocity($this->defaultVelocity);
            foreach ($this->binStyles as $style) {
                $shelf->addBinStyle($style);
            }
            for ($x = 1; $x <= $this->positionsPerShelf; $x++) {
                $shelf->createPosition(new Vector3D($x, 1, 1));
            }
        }
        return $rack;
    }
}
