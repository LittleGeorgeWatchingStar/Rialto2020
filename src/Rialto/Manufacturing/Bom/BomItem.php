<?php

namespace Rialto\Manufacturing\Bom;

use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\IllegalStateException;
use Rialto\Manufacturing\Component\Component;
use Rialto\Manufacturing\Component\Designators;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Measurement\Temperature\TemperatureRange;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\ItemVersionException;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Item\Version\VersionException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Represents a single component in a bill of materials (BOM).
 */
class BomItem implements RialtoEntity, Component
{
    /** @var int */
    private $id;

    /**
     * @var ItemVersion
     */
    private $parent;

    /**
     * @var PhysicalStockItem
     */
    private $component;

    /** @var bool */
    private $primary = false;

    /**
     * @var string The version of the component required by the BOM.
     */
    private $version = Version::ANY;

    /**
     * @var Customization|null
     */
    private $customization = null;

    /**
     * @var WorkType
     * @Assert\NotNull(message="Work type is required.")
     */
    private $workType;

    /**
     * @Assert\NotBlank(message="Please enter a quantity.")
     * @Assert\Type(type="numeric",
     *   message="Quantity must be an integer.")
     * @Assert\Range(min=1,
     *   minMessage="Quantity must be greater than zero.")
     */
    private $quantity = 0;

    /**
     * @var string[]
     * @Assert\Count(max=10000)
     */
    private $designators = [];

    public function __construct(PhysicalStockItem $component)
    {
        $this->component = $component;
    }

    /** @return ItemVersion */
    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(ItemVersion $parent)
    {
        $this->parent = $parent;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Makes a copy of this BOM item and attaches it to $newParent.
     *
     * @return BomItem The new copy
     */
    public function copyTo(ItemVersion $newParent)
    {
        $new = $newParent->addComponent($this->component, $this->quantity);
        $new->version = $this->version;
        $new->customization = $this->customization;
        $new->quantity = $this->quantity;
        $new->designators = $this->designators;
        $new->workType = $this->workType;
        return $new;
    }

    public function getAutoBuildVersion(): Version
    {
        return $this->component->getAutoBuildVersion();
    }

    /** @return bool */
    public function isCategory($category)
    {
        return $this->component->isCategory($category);
    }

    /** @return bool */
    public function isPurchased()
    {
        return $this->component->isPurchased();
    }

    public function hasSubcomponents()
    {
        return $this->component->hasSubcomponents();
    }

    /**
     * The BOM of this component, if it has one.
     * @throws IllegalStateException
     *  If this component does not have subcomponents.
     */
    public function getComponentBom(): Bom
    {
        if (!$this->hasSubcomponents()) {
            throw new IllegalStateException(sprintf(
                'Stock item %s has no bill of materials',
                $this->getSku()
            ));
        }
        try {
            return $this->component->getBom($this->getVersion());
        } catch (VersionException $ex) {
            // Provide a more specific exception.
            throw new ItemVersionException($this->parent, $ex->getMessage());
        }
    }

    /**
     * Alias for getComponentBom().
     * @see getComponentBom()
     * @throws IllegalStateException
     *  If this component does not have subcomponents.
     */
    public function getBom(): Bom
    {
        return $this->getComponentBom();
    }

    /**
     * Returns the stock item to which this component corresponds.
     * @return PhysicalStockItem
     */
    public function getComponent()
    {
        return $this->component;
    }

    /** @return StockCategory */
    public function getCategory()
    {
        return $this->component->getCategory();
    }

    /**
     * Returns the version of this component that is required by the BOM.
     * @return Version
     */
    public function getComponentVersion()
    {
        return $this->getVersion();
    }

    public function getComponentDescription()
    {
        return $this->component->getName();
    }

    /** @return string */
    public function getDescription()
    {
        return sprintf("BOM component %s for %s",
            $this->getFullSku(),
            $this->parent->getFullSku()
        );
    }

    public function __toString()
    {
        return $this->getDescription();
    }

    /**
     * Returns the list of reference designators as an array of strings.
     *
     * @return string[]
     */
    public function getDesignators()
    {
        return $this->designators;
    }

    /**
     * @param string[] $desig
     */
    public function setDesignators(array $desig)
    {
        $this->designators = Designators::normalize($desig);
    }

    /**
     * Returns the physical footprint of the part.
     *
     * @return string
     */
    public function getPackage()
    {
        return $this->component->getPackage();
    }

    /**
     * @return PurchasingData
     */
    public function getManufacturingData()
    {
        $dbm = ErpDbManager::getInstance();
        $mapper = $dbm->getRepository(PurchasingData::class);
        return $mapper->findPreferredForManufacturing($this);
    }

    public function getManufacturerCode()
    {
        $purchData = $this->getManufacturingData();
        return $purchData ? $purchData->getManufacturerCode() : '';
    }

    public function getPartValue()
    {
        return $this->component->getPartValue();
    }

    /** @return TemperatureRange */
    public function getTemperatureRange()
    {
        return $this->component->getTemperatureRange();
    }

    /**
     * Returns the number of units of this component that is required by the
     * BOM.
     * @return int
     */
    public function getQuantity()
    {
        return (int) $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function addQuantity($diff)
    {
        $this->quantity += $diff;
    }

    public function getUnitQty()
    {
        return $this->getQuantity();
    }

    public function getSku()
    {
        return $this->component->getSku();
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getIndexKey()
    {
        return $this->getSku();
    }

    public function getStockItem()
    {
        return $this->component;
    }

    /**
     * @return double
     *  The cost of one unit of this component.
     */
    public function getUnitCost()
    {
        return $this->component->getStandardCost();
    }

    /**
     * @return double
     *  The cost per component times the number of components per unit
     *  of the parent.
     */
    public function getExtendedCost()
    {
        return $this->getQuantity() * $this->getUnitCost();
    }

    /**
     * @deprecated use getExtendedCost() instead.
     */
    public function getTotalCost()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getExtendedCost();
    }

    public function isVersioned()
    {
        return $this->component->isVersioned();
    }

    /** @return Version */
    public function getVersion()
    {
        return $this->isVersioned() ?
            new Version($this->version) :
            Version::none();
    }

    public function setVersion(Version $version = null)
    {
        $this->version = $this->prepVersion($version);
    }

    /** @return string */
    private function prepVersion(Version $version = null)
    {
        if (!$this->isVersioned()) {
            return Version::NONE;
        }
        if (!$version) {
            return Version::ANY;
        }
        return (string) $version;
    }

    /**
     * @Assert\Callback
     */
    public function validateVersion(ExecutionContextInterface $context)
    {
        if (!$this->component->hasVersion($this->version)) {
            $context->buildViolation("{{ sku }} has no such version '{{ version }}'.")
                ->setParameter('{{ sku }}', $this->getSku())
                ->setParameter('{{ version }}', $this->version)
                ->atPath('version')
                ->addViolation();
        }
    }

    public function getFullSku()
    {
        return $this->getSku()
            . $this->getVersion()->getStockCodeSuffix()
            . Customization::getStockCodeSuffix($this->customization);
    }

    /** @deprecated */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    /** @return Customization|null */
    public function getCustomization()
    {
        return $this->customization;
    }

    public function setCustomization(Customization $customization = null)
    {
        $this->customization = $customization;
    }

    public function getUnitWeight()
    {
        return $this->component->getWeight();
    }

    public function getExtendedWeight()
    {
        return $this->getUnitWeight() * $this->quantity;
    }

    /**
     * @return WorkType
     */
    public function getWorkType()
    {
        return $this->workType;
    }

    public function setWorkType(WorkType $workType): self
    {
        $this->workType = $workType;
        return $this;
    }

    public function isEsdSensitive(): bool
    {
        return $this->component->isEsdSensitive();
    }

    public function isPrimary(): bool
    {
        return $this->primary;
    }

    public function setPrimary(bool $value)
    {
        $this->primary = $value;
    }
}

