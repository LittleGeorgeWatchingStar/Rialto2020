<?php

namespace Rialto\Stock\Item;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gumstix\GeographyBundle\Model\Country;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Measurement\Units;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\PurchaseInitiator;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Shipping\Export\HarmonizationCode;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Cost\StandardCost;
use Rialto\Stock\Item;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\UnspecifiedVersionException;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Item\Version\VersionException;
use Rialto\Stock\Sku;
use Rialto\Tax\Authority\TaxAuthority;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Any component or product that the company uses for sale or manufacturing.
 *
 * @UniqueEntity(fields={"stockCode"}, message="That SKU is already in use.")
 */
abstract class StockItem implements Item, RialtoEntity, PurchaseInitiator
{
    const INITIATOR_CODE = 'SIPO'; // Single-Item Purchase Order

    const ASSEMBLY = 'A';
    const PURCHASED = 'B';
    const DUMMY = 'D';
    const MANUFACTURED = 'M';

    const CURRENT = 0;
    const OBSOLETE = 1;
    const UNUSED = 2;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="SKU is required.")
     * @ValidSku(groups={"create"})
     */
    private $stockCode = null;

    /** @var StockCategory */
    private $category;

    /**
     * @Assert\NotBlank(message="Description cannot be blank.")
     * @Assert\Length(max=100,
     *   maxMessage="Description cannot be more than {{ limit }} characters long.")
     */
    private $description = '';

    /**
     * @Assert\NotBlank(
     *   message="Long description cannot be blank.",
     *   groups={"manual"})
     */
    private $longDescription = '';

    /**
     * @var DateTime The date this item was created.
     */
    private $dateCreated;

    private $flags = '';

    /**
     * @Assert\Length(max=50,
     *   maxMessage="Package cannot be more than {{ limit }} characters long.")
     */
    private $package = '';

    /**
     * @Assert\Length(max=50,
     *   maxMessage="Part value cannot be more than {{ limit }} characters long.")
     */
    private $partValue = '';

    /** @var WorkType|null */
    private $defaultWorkType;

    private $units = Units::EACH;

    /**
     * @var string
     * @Assert\NotBlank(message="Country of origin is required.", groups={"sellable"})
     * @Assert\Country(
     *   message="Country of origin is not a valid country code.",
     *   groups={"sellable"})
     */
    private $countryOfOrigin = '';

    /**
     * @Assert\Type(type="numeric", message="Actual cost must be numeric.")
     */
    private $actualCost = 0.0;

    /**
     * @Assert\Type(type="numeric", message="Last cost must be numeric.")
     */
    private $lastCost = 0.0;

    /**
     * @Assert\Type(type="numeric",
     *   message="Material cost must be numeric.",
     *   groups={"Default", "standardCost"})
     * @Assert\Range(min=0,
     *   minMessage="Material cost cannot be negative.",
     *   groups={"Default", "standardCost"})
     */
    private $materialCost = 0.0;

    /**
     * @var StandardCost
     */
    private $currentStandardCost = null;

    /**
     * @Assert\Type(type="numeric",
     *   message="Labour cost must be numeric.",
     *   groups={"Default", "standardCost"})
     * @Assert\Range(min=0,
     *   minMessage="Labour cost cannot be negative.",
     *   groups={"Default", "standardCost"})
     */
    private $labourCost = 0.0;

    /**
     * @Assert\Type(type="numeric",
     *   message="Overhead cost must be numeric.",
     *   groups={"Default", "standardCost"})
     * @Assert\Range(min=0,
     *   minMessage="Overhead cost cannot be negative.",
     *   groups={"Default", "standardCost"})
     */
    private $overheadCost = 0.0;

    /**
     * @Assert\Choice(callback="getValidDiscontinued",
     *     message="Invalid value for discontinued",
     *     strict=true)
     */
    private $discontinued = self::CURRENT;

    /**
     * @Assert\NotNull(message="Lot Control field is required.")
     */
    private $controlled = true;

    /**
     * @Assert\Type(type="numeric", message="EOQ must be a number.")
     * @Assert\Range(min=1,
     *   minMessage="EOQ must be at least {{ limit }}.",
     *   groups={"purchasing"})
     */
    private $orderQuantity = 0;

    private $discountCategory = '';

    /** @var TaxAuthority */
    private $taxAuthority;

    /** @var boolean */
    private $serialised = false; /* currently unused */

    /** @var int */
    private $decimalPlaces = 0;

    /** @var string */
    private $shippingVersion = '';

    /**
     * @var HarmonizationCode
     * @Assert\NotNull(message="Harmonization code is required.",
     *   groups={"sellable"})
     */
    private $harmonizationCode = null;

    /**
     * @Assert\NotBlank(message="ECCN code is required.",
     *   groups={"sellable"})
     * @Assert\Choice(
     *     callback={"Rialto\Stock\Item\Eccn", "getList"},
     *     message="Invalid or unacceptable ECCN code.",
     *     groups={"sellable"},
     *     strict=true)
     */
    private $eccnCode = '';

    /**
     * @var string
     * @Assert\Choice(callback={"Rialto\Stock\Item\RoHS", "getValid"},
     *     message="Invalid value for RoHS.",
     *     strict=true)
     */
    private $rohs = RoHS::COMPLIANT;

    private $phaseOutDate = null;
    private $autoBuildVersion = '';
    private $closeCount = false;

    /** @var ItemVersion[]|Collection */
    private $versions;

    /** @var StockItem[]|Collection */
    private $connectors;

    /** @var StockItem[]|Collection */
    private $connectsTo;

    /** @var StockFlag[]|Collection */
    private $stockFlags;

    public function __construct($sku = null)
    {
        if ($sku) {
            $this->setSku($sku);
        }
        $this->init();
    }

    public function setSku($sku)
    {
        assertion(!$this->stockCode, "SKU {$this->stockCode} is already set");
        $this->stockCode = strtoupper(trim($sku));
    }

    public function getSku()
    {
        return $this->stockCode;
    }

    /**
     * @deprecated
     */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** Used by both __construct() and __clone() */
    private function init()
    {
        $this->versions = new ArrayCollection();
        $this->connectors = new ArrayCollection();
        $this->connectsTo = new ArrayCollection();
        $this->stockFlags = new ArrayCollection();
        $this->dateCreated = new DateTime();
    }

    /** @return StockItem */
    public function copy($newStockCode)
    {
        $newItem = clone $this;
        $newItem->setSku($newStockCode);
        return $newItem;
    }

    public function __clone()
    {
        if (!$this->stockCode) return; // required by Doctrine

        $this->stockCode = null;
        $this->phaseOutDate = null;
        $this->init();
    }

    /**
     * Returns all suppliers that sell this item.
     *
     * @return Supplier[]
     */
    public function getSuppliers()
    {
        $dbm = ErpDbManager::getInstance();
        $mapper = $dbm->getRepository(Supplier::class);
        return $mapper->findByItem($this);
    }

    /**
     * Returns the preferred supplier of this stock item.
     *
     * @return Supplier|null
     */
    public function getPreferredSupplier()
    {
        $pData = $this->getPreferredPurchasingData();
        return $pData ? $pData->getSupplier() : null;
    }

    /** @return PurchasingData|null */
    public function getPreferredPurchasingData()
    {
        $dbm = ErpDbManager::getInstance();
        $mapper = $dbm->getRepository(PurchasingData::class);
        return $mapper->findPreferred($this);
    }

    /** @return PurchasingData[] */
    public function getAllPurchasingData()
    {
        $dbm = ErpDbManager::getInstance();
        $mapper = $dbm->getRepository(PurchasingData::class);
        return $mapper->findAllPurchasingDataBySku($this->getSku());
    }

    /**
     * @Assert\Range(min=0.0001,
     *   minMessage="Standard cost must be at least {{ limit }}.",
     *   groups={"standardCost","purchasing"})
     * @return float
     */
    public function getStandardCost()
    {
        return round($this->materialCost +
            $this->labourCost +
            $this->overheadCost,
            StandardCost::PRECISION);
    }

    public function setStandardCost(StandardCost $cost)
    {
        /* Maintain legacy fields */
        $this->lastCost = $this->getStandardCost();
        $this->materialCost = $cost->getMaterialCost();
        $this->labourCost = $cost->getLabourCost();
        $this->overheadCost = $cost->getOverheadCost();

        /* Update the new field */
        $this->currentStandardCost = $cost;
        return $this;
    }

    public function getLabourCost()
    {
        return $this->labourCost;
    }

    public function getMaterialCost()
    {
        return $this->materialCost;
    }

    public function getOverheadCost()
    {
        return $this->overheadCost;
    }

    /**
     * Returns the StockCategory to which this item belongs.
     *
     * @return StockCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(StockCategory $category)
    {
        $this->category = $category;
    }

    /** @deprecated Use getCategory() instead */
    public function getStockCategory()
    {
        return $this->getCategory();
    }

    /** @deprecated Use setCategory() instead */
    public function setStockCategory(StockCategory $category)
    {
        $this->setCategory($category);
    }

    /**
     * @return GLAccount
     *  The account which keeps track of the value of stock
     *  in this item.
     */
    public function getStockAccount()
    {
        return $this->category->getStockAccount();
    }

    /**
     * @return GLAccount
     *  The account which keeps track of changes to the value of stock
     *  in this item.
     */
    public function getAdjustmentAccount()
    {
        return $this->category->getAdjustmentAccount();
    }

    /**
     * Returns true if the given item or stock code matches this item.
     *
     * @param Item|string $item
     */
    public function isItem($item): bool
    {
        if ($item instanceof Item) {
            $item = $item->getSku();
        }
        if (null === $item) {
            return false;
        }
        if (!is_string($item)) {
            throw new \InvalidArgumentException(get_class($item) . ' is not an item');
        }
        return $this->stockCode === $item;
    }

    /**
     * @return string Two-character ISO country code (eg, US, CN)
     */
    public function getCountryOfOrigin(): string
    {
        return Country::resolveCountryCode($this->countryOfOrigin);
    }

    public function setCountryOfOrigin($value)
    {
        if ($value instanceof Country) $value = $value->getCode();
        $this->countryOfOrigin = (string) $value;
        return $this;
    }

    public function getDescription()
    {
        return $this->getName();
    }

    public function setDescription($value)
    {
        $this->setName($value);
    }

    /**
     * @return string The name of this item
     */
    public function getName()
    {
        return $this->description;
    }

    public function setName($name)
    {
        $this->description = trim($name);
    }

    /**
     * @return string The long description of this stock item.
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    public function setLongDescription($value)
    {
        $this->longDescription = trim($value);
    }

    /**
     * @return string The package/footprint of this item.
     */
    public function getPackage()
    {
        return $this->package;
    }

    public function setPackage($package)
    {
        $this->package = trim($package);
    }

    /**
     * @return string The part value of this stock item.
     */
    public function getPartValue()
    {
        return $this->partValue;
    }

    public function setPartValue($value)
    {
        $this->partValue = trim($value);
    }

    /**
     * @todo This probably should be a boolean field, but it isn't.
     * @return int
     */
    public function getDiscontinued()
    {
        return $this->discontinued;
    }

    public function isDiscontinued()
    {
        return $this->getDiscontinued();
    }

    public function setDiscontinued($value)
    {
        $this->discontinued = (int) $value;
    }

    public static function getDiscontinuedOptions()
    {
        return [
            'Current' => self::CURRENT,
            'Obsolete' => self::OBSOLETE,
            'Unused' => self::UNUSED,
        ];
    }

    public static function getValidDiscontinued()
    {
        return array_values(self::getDiscontinuedOptions());
    }

    /**
     * True if this item is not discontinued or obsolete.
     */
    public function isCurrent()
    {
        return self::CURRENT == $this->discontinued;
    }

    /**
     * Returns the economic order quantity (EOQ) -- the minimum number of
     * pieces to buy when ordering more of this item.
     *
     * @return int  The EOQ
     */
    public function getEconomicOrderQty()
    {
        return $this->orderQuantity;
    }

    public function setEconomicOrderQty($qty)
    {
        $this->orderQuantity = $qty ?: 0;
    }

    /** @deprecated use getEconomicOrderQty() instead */
    public function getEOQ()
    {
        return $this->getEconomicOrderQty();
    }

    /** @deprecated use getEconomicOrderQty() instead */
    public function getOrderQuantity()
    {
        return $this->getEconomicOrderQty();
    }

    /**
     * Returns the harmonization code of this item, which is required
     * for exports from the US.
     *
     * @return HarmonizationCode
     */
    public function getHarmonizationCode()
    {
        return $this->harmonizationCode;
    }

    public function setHarmonizationCode(HarmonizationCode $code = null)
    {
        $this->harmonizationCode = $code;
    }

    /**
     * Returns the StockID of this item.
     *
     * @return string
     */
    public function getId()
    {
        return $this->stockCode;
    }

    public function getDateCreated(): DateTime
    {
        return clone $this->dateCreated;
    }

    public function getInitiatorCode()
    {
        return self::INITIATOR_CODE;
    }

    /**
     * @return Units
     */
    public function getUnits()
    {
        return new Units($this->units);
    }

    public function setUnits(Units $units = null)
    {
        if (null === $units) return;
        $this->units = $units->getName();
        $this->decimalPlaces = $units->getPrecision();
    }

    /**
     *
     * @return double
     */
    public function getDecimalPlaces()
    {
        return $this->decimalPlaces;
    }

    public static function getStockTypeOptions()
    {
        return [
            PurchasedStockItem::STOCK_TYPE => self::PURCHASED,
            ManufacturedStockItem::STOCK_TYPE => self::MANUFACTURED,
            AssemblyStockItem::STOCK_TYPE => self::ASSEMBLY,
            DummyStockItem::STOCK_TYPE => self::DUMMY,
        ];
    }

    /** @return string[] */
    public static function getValidStockTypes()
    {
        return array_values(self::getStockTypeOptions());
    }

    /**
     * How this item is stocked: purchased, manufactured, assembly, or dummy.
     */
    public abstract function getStockType(): string;

    /** @return boolean */
    public function hasWeight()
    {
        return !$this->isDummy();
    }

    /**
     * @return double
     *  the weight of this product, in kilograms.
     */
    public abstract function getWeight();

    /**
     * @return double
     *  The physical volume of this stock item, in cubic centimeters.
     */
    public abstract function getVolume();

    /**
     * True if this item can be damaged by electro-static discharge (ESD).
     */
    public abstract function isEsdSensitive(): bool;

    /**
     * Returns the discount category
     * @return string
     */
    public function getDiscountCategory()
    {
        return $this->discountCategory;
    }

    /**
     * Returns the RoHS status
     * @return string
     */
    public function getRoHS()
    {
        return $this->rohs;
    }

    public function setRoHS($value)
    {
        $this->rohs = trim($value);
    }

    /**
     * Returns the discount category
     * @return string
     */
    public function getEccnCode()
    {
        return $this->eccnCode;
    }

    public function setEccnCode($value)
    {
        $this->eccnCode = strtoupper(trim($value));
        return $this;
    }

    /**
     * Returns the phase out date, formatted as
     * @return DateTime
     */
    public function getPhaseOut()
    {
        return $this->phaseOutDate ? clone $this->phaseOutDate : null;
    }

    public function getPhaseOutDate()
    {
        return $this->getPhaseOut();
    }

    /**
     * returns the tax level of this item
     * @return TaxAuthority
     */
    public function getTaxLevel()
    {
        return $this->taxAuthority;
    }

    public function setTaxAuthority(TaxAuthority $authority = null)
    {
        if (null === $authority) return;
        $this->taxAuthority = $authority;
    }

    public function setTaxLevel(TaxAuthority $authority = null)
    {
        $this->setTaxAuthority($authority);
    }

    public function isAssembly(): bool
    {
        return $this instanceof AssemblyStockItem;
    }

    public function isManufactured(): bool
    {
        return $this instanceof ManufacturedStockItem;
    }

    public function isDummy(): bool
    {
        return $this instanceof DummyStockItem;
    }

    public function isPurchased(): bool
    {
        return $this instanceof PurchasedStockItem;
    }

    public function isPhysicalPart(): bool
    {
        return $this instanceof PhysicalStockItem;
    }

    public function isRequestable(): bool
    {
        return !$this instanceof DummyStockItem;
    }

    public function hasSubcomponents(): bool
    {
        return $this instanceof CompositeStockItem;
    }

    /**
     * A "close count" item is one which is expensive enough to be worth
     * managing carefully; eg. only sending the amount needed when kitting
     * a work order, etc, so that it doesn't needlessly go to waste.
     */
    public function isCloseCount(): bool
    {
        return $this->closeCount;
    }

    public function isControlled(): bool
    {
        return $this->controlled;
    }

    /**
     * @param StockCategory|int $category
     */
    public function isCategory($category): bool
    {
        return $this->category->isCategory($category);
    }

    public function isBoard(): bool
    {
        return StockCategory::BOARD == $this->category->getId();
    }

    public function isPCB(): bool
    {
        return $this->category->isPCB();
    }

    public function isPrintedLabel(): bool
    {
        return Sku::PRINTED_LABEL === $this->stockCode;
    }

    public function isSellable(): bool
    {
        return $this->category->isSellable();
    }

    public function isProduct(): bool
    {
        return $this->category->isProduct();
    }

    /**
     * A versioned item can have multiple versions. Unversioned items
     * can only have one.
     */
    public function isVersioned(): bool
    {
        if ($this->isPCB()) return true;
        if ($this->hasSubcomponents()) return true;
        return false;
    }

    /**
     * @see isVersioned()
     * @return ItemVersion[]
     */
    public function getVersions()
    {
        return $this->versions->toArray();
    }

    /**
     * @see isVersioned()
     * @return ItemVersion[]
     */
    public function getActiveVersions()
    {
        return array_filter($this->getVersions(), function (ItemVersion $iv) {
            return $iv->isActive();
        });
    }

    /**
     * @param Version|string $version
     * @return ItemVersion
     * @throws VersionException If this item has no such version.
     */
    public function getVersion($version)
    {
        $iv = $this->getVersionOrNull($version);
        if ($iv) return $iv;
        throw new VersionException($this, "has no such version \"$version\"");
    }

    private function getVersionOrNull($version)
    {
        $fallback = null;
        foreach ($this->versions as $iv) {
            if ($iv->equals($version)) {
                return $iv;
            } elseif ($iv->matches($version)) {
                $fallback = $iv;
            }
        }
        return $fallback;
    }

    /**
     * "Auto-versioned" items such as printed labels do not have any
     * specified versions.
     *
     * @return bool
     */
    public function hasSpecifiedVersions()
    {
        foreach ($this->versions as $iv) {
            if ($iv->isSpecified()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Version The given version, if it is specified, or a default
     *   version which is guaranteed to be specified.
     * @throws UnspecifiedVersionException if a specified version cannot be
     *   found.
     */
    public function getSpecifiedVersionOrDefault(Version $version = null)
    {
        if (null === $version) {
            $version = $this->getAutoBuildVersion();
        } elseif (!$version->isSpecified()) {
            $version = $this->getAutoBuildVersion();
        }
        if (!$version->isSpecified()) {
            throw new UnspecifiedVersionException($this);
        }
        return $version;
    }

    /**
     * @param string|Version $version
     * @return ItemVersion
     */
    public function addVersion($version)
    {
        $this->validateNewVersion($version);

        $iv = $this->getVersionOrNull($version);
        if ($iv) {
            return $iv;
        }
        $version = new ItemVersion($this, $version);
        $this->versions[] = $version;
        return $version;
    }

    private function validateNewVersion($version)
    {
        $version = new Version($version);
        if ($this->isVersioned()) {
            if ($version->isNone()) {
                throw new \InvalidArgumentException("Blank version is not allowed");
            }
        } elseif (!$version->isNone()) {
            throw new \InvalidArgumentException("Blank version is required");
        }
    }

    /**
     * The version that is currently being manufactured or purchased.
     */
    public function getAutoBuildVersion(): ItemVersion
    {
        return $this->getVersion($this->autoBuildVersion);
    }

    public function setAutoBuildVersion(Version $version)
    {
        /* Make sure version is valid */
        $version = $this->getVersion($version);
        $this->autoBuildVersion = (string) $version;
    }

    /**
     * The version that is currently being sold.
     */
    public function getShippingVersion(): ItemVersion
    {
        return $this->getVersion($this->shippingVersion);
    }

    public function setShippingVersion(Version $version)
    {
        /* Make sure version is valid */
        $version = $this->getVersion($version);
        $this->shippingVersion = (string) $version;
    }

    /** @Assert\Callback */
    public function validateVersions(ExecutionContextInterface $context)
    {
        if (count($this->versions) == 0) return;
        $this->validateVersion($context, $this->autoBuildVersion);
        $this->validateVersion($context, $this->shippingVersion);
    }

    private function validateVersion(ExecutionContextInterface $context, $version)
    {
        if (!$this->hasVersion($version)) {
            $context->addViolation("_item has no such version _ver", [
                '_item' => $this->stockCode,
                '_ver' => $version,
            ]);
        }
    }

    public function hasVersion($version)
    {
        return null !== $this->getVersionOrNull($version);
    }

    /**
     * @see isVersioned()
     * @see getActiveVersions
     * @return Version[]
     */
    public function getValidVersions()
    {
        return $this->versions->filter(function (ItemVersion $version) {
            return $version->isValid();
        })->toArray();
    }

    /**
     * Returns true if this item supports Customizations.
     *
     * @see Customization
     */
    public function isCustomizable(): bool
    {
        return $this->isManufactured();
    }

    /**
     * @deprecated Use setEconomicOrderQty() instead
     */
    public function setOrderQuantity($value)
    {
        $this->setEconomicOrderQty((float) $value);
    }

    public function setControlled($value)
    {
        $this->controlled = (bool) $value;
    }

    public function setDiscountCategory($value)
    {
        $this->discountCategory = trim($value);
    }

    public function setPhaseOut(DateTime $date = null)
    {
        $this->phaseOutDate = $date ? clone $date : null;
    }

    public function setCloseCount($value)
    {
        $this->closeCount = (bool) $value;
    }

    /** @return StockFlags */
    public function getFlags()
    {
        return new StockFlags($this, $this->stockFlags);
    }

    public function addFlag(StockFlag $flag)
    {
        $this->stockFlags->add($flag);
    }

    /**
     * @return StockItem[] Any components that connect to this one.
     */
    public function getConnectingComponents()
    {
        return $this->connectsTo->toArray();
    }

    public function isConnector()
    {
        if ($this->category->getId() == StockCategory::PART) {
            return true;
        } else return false;
    }

    public function deleteConnector(StockItem $item)
    {
        $this->connectsTo->removeElement($item);
        $item->connectsTo->removeElement($this);
    }

    public function addConnector(StockItem $item)
    {
        $this->connectsTo->add($item);
        $item->connectsTo->add($this);
    }

    public function __toString()
    {
        return $this->stockCode;
    }

    /**
     * @return WorkType|null
     */
    public function getDefaultWorkType()
    {
        return $this->defaultWorkType;
    }

    /**
     * @param WorkType|null $defaultWorkType
     */
    public function setDefaultWorkType($defaultWorkType)
    {
        $this->defaultWorkType = $defaultWorkType;
    }

}
