<?php

namespace Rialto\Stock\Shelf\Rack\Web;

use Rialto\Stock\Shelf\Rack;
use Rialto\Stock\Shelf\Shelf;
use Rialto\Stock\Shelf\Shelf\Web\ShelfEdit;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * For editing an existing rack.
 */
class RackEdit
{
    /**
     * @var Rack
     * @Assert\Valid
     */
    private $rack;

    /**
     * @var ShelfEdit[]
     * @Assert\Valid(traverse=true)
     */
    private $shelves;

    private $addRemove = '';

    public function __construct(Rack $rack)
    {
        $this->rack = $rack;
        $this->shelves = array_map(function (Shelf $s) {
            return new ShelfEdit($s);
        }, $rack->getShelves());
    }

    public function getName()
    {
        return $this->rack->getName();
    }

    public function setName($name)
    {
        $this->rack->setName($name);
    }

    public function hasEsdProtection()
    {
        return $this->rack->hasEsdProtection();
    }

    public function setEsdProtection($protection)
    {
        $this->rack->setEsdProtection($protection);
    }

    /**
     * @return ShelfEdit[]
     */
    public function getShelves()
    {
        return $this->shelves;
    }

    /**
     * @param string $action
     */
    public function setAddRemove($action)
    {
        $this->addRemove = $action;
    }

    public function isAddRemove()
    {
        return !!$this->addRemove;
    }

    private function removeShelf()
    {
        return 'remove' === $this->addRemove;
    }

    private function addShelf()
    {
        return $this->isAddRemove() && (!$this->removeShelf());
    }

    /**
     * @Assert\Callback
     */
    public function validateRemoveShelf(ExecutionContextInterface $context)
    {
        if (!$this->removeShelf()) {
            return;
        }
        $toRemove = $this->rack->getLastShelf();
        if ($toRemove && $toRemove->isOccupied()) {
            $context->buildViolation('stock.shelf.rack.removeshelf.occupied')
                ->setParameter('{{ shelf }}', $toRemove)
                ->addViolation();
        }
    }

    public function applyChanges()
    {
        foreach ($this->shelves as $shelfEdit) {
            $shelfEdit->applyChanges();
        }
        if ($this->addShelf()) {
            $this->rack->cloneShelf($this->addRemove);
        } elseif ($this->removeShelf()) {
            $this->rack->removeShelf();
        }
    }
}
