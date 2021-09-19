<?php

namespace Rialto\Manufacturing\Bom;

use Rialto\Measurement\Temperature\TemperatureRange;
use Rialto\Stock\Item;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\ItemIndex;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Represents the bill of materials (BOM) for a single stock item.  The BOM
 * is the list of components required to manufacture or assemble the item.
 *
 * Notice that Bom implements Iterator and Countable, so you can pass it
 * to count() or use it as the subject of a foreach loop.
 */
class Bom extends ItemIndex
{
    /** @var ItemVersion */
    private $version;

    public function __construct(ItemVersion $version)
    {
        $this->version = $version;
        parent::__construct($version->getBomItems());
    }

    /** @return ItemVersion */
    public function getParent()
    {
        return $this->version;
    }

    public function getDescription()
    {
        return "BOM for {$this->version->getFullSku()}";
    }

    public function __toString()
    {
        return $this->getDescription();
    }

    /**
     * @param string|Item $item
     * @return BomItem|null
     */
    public function getItem($item)
    {
        return $this->get($item);
    }

    /**
     * Returns the list of BomItems in this BOM
     * @return BomItem[]
     * @Assert\Valid(traverse="true")
     */
    public function getItems()
    {
        return $this->toArray();
    }

    public function getTotalStandardCost()
    {
        return $this->version->getTotalStandardCost();
    }

    public function getTotalWeight()
    {
        return $this->version->getTotalWeight();
    }

    /**
     * The *extended* total, not the number of *distinct* components.
     *
     * @return int
     */
    public function getTotalNumberOfComponents()
    {
        return $this->version->getTotalNumberOfComponents();
    }

    /**
     * @return TemperatureRange
     */
    public function getTemperatureRange()
    {
        $range = TemperatureRange::unspecified();
        foreach ($this->getItems() as $component) {
            $range = $component->getTemperatureRange()->intersection($range);
        }
        return $range;
    }

    public function isEmpty()
    {
        return count($this) == 0;
    }

    public function createCopy(): Bom
    {
        return new self($this->version);
    }

    /**
     * True if any component can be damaged by electro-static discharge.
     */
    public function isEsdSensitive(): bool
    {
        foreach ($this->getItems() as $component) {
            if ($component->isEsdSensitive()) {
                return true;
            }
        }
        return false;
    }
}
