<?php

namespace Rialto\Stock\Shelf\Shelf\Web;


use Gumstix\Geometry\Vector3D;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Shelf\Shelf;
use Rialto\Stock\Shelf\Velocity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * For editing an existing shelf.
 */
class ShelfEdit
{
    /**
     * @var Shelf
     * @Assert\Valid
     */
    private $shelf;

    /**
     * @var Vector3D|null
     * @Assert\NotNull
     */
    public $dimensions;

    public function __construct(Shelf $shelf)
    {
        $this->shelf = $shelf;
        $this->dimensions = $shelf->getDimensions();
    }

    public function getShelf()
    {
        return $this->shelf;
    }

    /**
     * @return Velocity
     */
    public function getVelocity()
    {
        return $this->shelf->getVelocity();
    }

    public function setVelocity(Velocity $velocity)
    {
        $this->shelf->setVelocity($velocity);
    }

    /**
     * @return BinStyle[]
     */
    public function getBinStyles()
    {
        return $this->shelf->getBinStyles();
    }

    public function addBinStyle(BinStyle $style)
    {
        $this->shelf->addBinStyle($style);
    }

    public function removeBinStyle(BinStyle $style)
    {
        $this->shelf->removeBinStyle($style);
    }

    /**
     * @Assert\Callback
     */
    public function validateNewDimensions(ExecutionContextInterface $context)
    {
        foreach ($this->shelf->getPositions() as $pos) {
            if ($pos->isOccupied() && $pos->isOutside($this->dimensions)) {
                $context->buildViolation('stock.shelf.position.outside')
                    ->setParameter('{{ pos }}', $pos)
                    ->atPath('dimensions')
                    ->addViolation();
            }
        }
    }

    public function applyChanges()
    {
        if ($this->dimensions) {
            $this->shelf->updateDimensions($this->dimensions);
        }
    }
}
