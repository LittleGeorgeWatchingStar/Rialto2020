<?php

namespace Rialto\Stock\Item\Version;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Entity\RialtoEntity;
use Rialto\Manufacturing\Bom\Bom;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Measurement\Dimensions;
use Rialto\Measurement\Temperature\TemperatureRange;
use Rialto\Stock\Item;
use Rialto\Stock\Item\PurchasedStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\VersionedItem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Represents a valid version of a stock item.
 */
class ItemVersion extends Version implements RialtoEntity, VersionedItem
{
    /**
     * @var StockItem
     */
    private $stockItem;

    /**
     * False if this version is known to be bad and cannot be used.
     * @var boolean
     */
    private $active = true;

    /**
     * @var BomItem[]
     * @Assert\Valid(traverse=true)
     */
    private $bomItems;

    /**
     * The weight of this version, in kilograms.
     *
     * @Assert\NotBlank
     * @Assert\Type(type="numeric", message="Weight must be a number.")
     * @Assert\Range(min=0.0001,
     *   minMessage="Weight must be at least {{ limit }} kg.",
     *   groups={"sellable"})
     */
    private $weight = 0;

    /** @var float Width in centimeters */
    private $dimensionX = 0;

    /** @var float Length in centimeters */
    private $dimensionY = 0;

    /** @var float Height in centimeters */
    private $dimensionZ = 0;

    public function __construct(StockItem $stockItem, $version)
    {
        $this->stockItem = $stockItem;
        parent::__construct($version);
        $this->bomItems = new ArrayCollection();
    }

    /**
     * Copies all of the fields from $other into $this.
     */
    public function copyFrom(ItemVersion $other)
    {
        $this->weight = $other->weight;
        $this->dimensionX = $other->dimensionX;
        $this->dimensionY = $other->dimensionY;
        $this->dimensionZ = $other->dimensionZ;
        foreach ($other->getBomItems() as $bomItem) {
            $bomItem->copyTo($this);
        }
    }

    /**
     * @return StockItem
     */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function getSku()
    {
        return $this->stockItem->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @return boolean */
    public function isCategory($category)
    {
        return $this->stockItem->isCategory($category);
    }

    public function isSellable()
    {
        return $this->stockItem->isSellable();
    }

    public function hasSubcomponents()
    {
        return $this->stockItem->hasSubcomponents();
    }

    /** @deprecated Use getFullSku() instead */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    public function getFullSku()
    {
        return $this->getSku() . $this->getStockCodeSuffix();
    }

    /** @return Customization|null */
    public function getCustomization()
    {
        return null;
    }

    /** @return Version */
    public function getVersion()
    {
        return $this;
    }

    public function getCodeWithDimensions(): string
    {
        return sprintf('%s (%s)', $this->version, $this->getDimensions());
    }

    public function getFullCodeWithDimensions(): string
    {
        return sprintf('%s (%s)', $this->getFullSku(), $this->getDimensions());
    }

    public function getCodeWithStatus(): string
    {
        return $this->version . ($this->isActive() ? '' : ' (inactive)');
    }

    /**
     * False if this version is bad and cannot be used.
     */
    public function isActive(): bool
    {
        return (bool) $this->active;
    }

    public function canBeDeactivated(): bool
    {
        return $this->isActive()
            && $this->isSpecified()
            && (!$this->isNone())
            && (count($this->stockItem->getActiveVersions()) > 1);
    }

    /**
     * Indicates that this version is bad and cannot be used.
     */
    public function deactivate()
    {
        $this->active = false;
    }

    /**
     * Some invalid versions exist in the database for legacy reasons.
     */
    public function isValid(): bool
    {
        if ($this->stockItem->isVersioned()) {
            return !$this->isNone();
        }
        return $this->isNone();
    }

    public function isSpecified(): bool
    {
        return parent::isSpecified() && $this->isValid();
    }

    public function hasBomItems(): bool
    {
        return count($this->bomItems) > 0;
    }

    /**
     * @return BomItem[]
     */
    public function getBomItems()
    {
        return $this->bomItems->toArray();
    }

    /**
     * @return BomItem|null
     */
    public function getBomItem(Item $item)
    {
        foreach ($this->bomItems as $bomItem) {
            if ($bomItem->getSku() == $item->getSku()) {
                return $bomItem;
            }
        }
        return null;
    }


    /**
     * Adds $component to the BOM if it is not already there
     * and increases the quantity by $quantity;
     */
    public function addComponent(StockItem $component, $quantity): BomItem
    {
        $bomItem = $this->getBomItem($component);
        if (!$bomItem) {
            $bomItem = new BomItem($component);
            $this->addBomItem($bomItem);
        }
        $bomItem->addQuantity($quantity);
        return $bomItem;
    }

    public function addBomItem(BomItem $item)
    {
        if (!$this->hasSubcomponents()) {
            throw new \LogicException($this->getSku() . ' has no subcomponents');
        }
        $item->setParent($this);
        $this->bomItems[] = $item;
    }

    /**
     * Removes the item from the bill of materials (BOM) for this version.
     */
    public function removeBomItem(BomItem $item)
    {
        $this->bomItems->removeElement($item);
    }

    /**
     * @return Bom|null
     * @Assert\Valid
     * @Assert\Count(min=1,
     *   minMessage="BOM is empty.",
     *   groups={"bom_required"})
     */
    public function getBom()
    {
        if ($this->hasSubcomponents()) {
            return new Bom($this);
        }
        return null;
    }

    public function clearBom()
    {
        $this->bomItems->clear();
    }

    /** @Assert\Callback(groups={"Default", "bom"}) */
    public function validateBomItems(ExecutionContextInterface $context)
    {
        $values = [];
        foreach ($this->bomItems as $item) {
            $code = $item->getSku();
            if (isset($values[$code])) {
                $context->addViolation('stock.bom.dup_item', ['%code%' => $code]);
                return;
            }
            $values[$code] = true;
        }
    }

    public function getTotalStandardCost(): float
    {
        $total = 0.0;
        foreach ($this->bomItems as $bomItem) {
            $total += $bomItem->getExtendedCost();
        }
        return $total;
    }

    public function getTotalWeight(): float
    {
        $total = 0.0;
        foreach ($this->bomItems as $bomItem) {
            $total += $bomItem->getExtendedWeight();
        }
        return $total;
    }

    /**
     * The *extended* total, not the number of *distinct* components.
     */
    public function getTotalNumberOfComponents(): int
    {
        $total = 0;
        foreach ($this->bomItems as $bomItem) {
            $total += $bomItem->getUnitQty();
        }
        return $total;
    }

    public function getTemperatureRange(): TemperatureRange
    {
        if ($this->hasSubcomponents()) {
            return $this->getBom()->getTemperatureRange();
        } elseif ($this->stockItem instanceof PurchasedStockItem) {
            return $this->stockItem->getTemperatureRange();
        }
        return TemperatureRange::unspecified();
    }

    /**
     * @param Version|string $other
     */
    public function equals($other): bool
    {
        if ($other instanceof ItemVersion) {
            return ($this->getSku() == $other->getSku())
                && $this->equals((string) $other);
        } else {
            return parent::equals($other);
        }
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight($weight)
    {
        $this->weight = (float) $weight;
    }

    public function resetWeightFromBom()
    {
        $bom = $this->getBom();
        $this->weight = $bom ? $bom->getTotalWeight() : $this->weight;
    }

    /**
     * The dimensions of this item, in centimeters.
     *
     * @return Dimensions|null
     * @Assert\NotNull(message="Dimensions are required.",
     *   groups={"sellable", "dimensions"})
     * @Assert\Valid
     */
    public function getDimensions()
    {
        if (!$this->hasDimensions()) {
            return null;
        }
        return new Dimensions(
            $this->dimensionX,
            $this->dimensionY,
            $this->dimensionZ
        );
    }

    public function hasDimensions(): bool
    {
        return
            ($this->dimensionX > 0) ||
            ($this->dimensionY > 0) ||
            ($this->dimensionZ > 0);
    }

    public function setDimensions(Dimensions $dim = null)
    {
        $dim = $dim ? $dim->inCm() : Dimensions::zero();
        $this->dimensionX = $dim->getX();
        $this->dimensionY = $dim->getY();
        $this->dimensionZ = $dim->getZ();
    }

    /**
     * The volume of this version, in cubic centimeters (eg, for shipping).
     */
    public function getVolume(): float
    {
        $dim = $this->getDimensions();
        return $dim ? $dim->getVolume() : 0;
    }

    public function canContain(ItemVersion $other, $tolerance = 0, $dimensions = 3)
    {
        return $this->getDimensions()
            ->isLargerThan($other->getDimensions(), $tolerance, $dimensions);
    }

    public function isAutoBuildVersion()
    {
        return $this->equals($this->stockItem->getAutoBuildVersion());
    }

    public function isShippingVersion()
    {
        return $this->equals($this->stockItem->getShippingVersion());
    }
}
