<?php

namespace Rialto\Purchasing\Catalog;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\RoHS;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A purchasing data record maps a stock item to a supplier, and contains
 * information about that product such as cost, etc.
 *
 * @UniqueEntity(
 *   fields={"supplier", "buildLocation", "catalogNumber", "quotationNumber"},
 *   ignoreNull=false,
 *   message="purchasing.purch_data.unique",
 *   groups={"edit", "create"})
 */
class PurchasingData implements RialtoEntity, Item, CatalogItem
{
    /**
     * Unit cost amounts are rounded to this many decimal places.
     */
    const UNIT_COST_SCALE = 6;

    private $id;

    /** @var StockItem */
    private $stockItem;

    /** @var Supplier|null */
    private $supplier;

    /** @var Facility|null */
    private $buildLocation;

    /**
     * @var string
     * @Assert\NotBlank(message="Catalog number cannot be blank.")
     * @Assert\Length(max=50)
     */
    private $catalogNumber;

    /**
     * @var string
     * @Assert\Length(max=50)
     */
    private $quotationNumber = '';

    /** @var string */
    private $version = Version::ANY;

    /**
     * The manufacturer of this part, if known.
     * @var Manufacturer
     */
    private $manufacturer = null;

    /**
     * @var string
     * @Assert\Length(max=50,
     *   maxMessage="Manufacturer code cannot be longer than {{ limit }} characters.")
     */
    private $manufacturerCode = '';

    /**
     * @var string
     * @Assert\Length(max=50)
     */
    private $suppliersUOM = '';

    /** @var int */
    private $conversionFactor = 1.0;

    /**
     * @var BinStyle
     * @Assert\NotNull(message="Please select a bin style.")
     */
    private $binStyle;

    /**
     * @var string
     * @Assert\Length(max=50)
     */
    private $supplierDescription = '';

    /**
     * @var bool
     */
    private $preferred = false;

    /**
     * @var string
     * @Assert\Choice(
     *     callback={"Rialto\Stock\Item\RoHS", "getValid"},
     *     message="Please provide a valid RoHS status.",
     *     strict=true)
     */
    private $rohs = RoHS::COMPLIANT;

    /**
     * @var string
     * @Assert\Length(max=20)
     */
    private $temperature = '';

    /**
     * @var bool
     */
    private $turnkey = false;

    /**
     * @Assert\NotBlank(message="Increment qty is required.")
     * @Assert\Type(type="integer",
     *   message="Increment qty must be an integer.")
     * @Assert\Range(min="1",
     *   minMessage="Increment quantity must be at least {{ limit }}.")
     */
    private $incrementQty;

    /**
     * @Assert\NotBlank(message="Bin size is required.")
     * @Assert\Type(type="integer",
     *   message="Bin size must be an integer.")
     * @Assert\Range(min=0,
     *   minMessage="Bin size cannot be negative.")
     * @Assert\Range(min=1,
     *   minMessage="Bin size must be greater than 0 in purchasing data.",
     *   groups={"strictBins"})
     */
    private $binSize = 0;


    /**
     * @var CostBreak[]
     * @Assert\Valid(traverse="true")
     * @Assert\Count(min=1, minMessage="Please enter at least one cost level.")
     */
    private $costBreaks;

    /**
     * The quantity in stock at the supplier as of the last update.
     *
     * @var int|null
     * @see $lastSync
     */
    private $stockLevel = null;

    /**
     * When the stock level was last updated.
     *
     * @var DateTime
     * @see $stockLevel
     */
    private $lastSync = null;

    /**
     * The URL of the product page on the supplier's website.
     *
     * @var string
     * @Assert\Url
     * @Assert\Length(max=255)
     */
    private $productUrl = '';

    /**
     * @var DateTime
     */
    private $endOfLife;

    public function __construct(StockItem $item)
    {
        $this->costBreaks = new ArrayCollection();
        $this->setStockItem($item);
    }

    private function setStockItem(StockItem $item)
    {
        assertion(!$this->stockItem, "Stock item {$this->stockItem} is already set");
        $this->stockItem = $item;
        if ($item->isManufactured() || $item->isPCB()) {
            // Provide a convenient default.
            $this->catalogNumber = $item->getSku();
        }
    }

    public function __toString()
    {
        return $this->getLabel();
    }

    public function getId()
    {
        return $this->id;
    }

    public function isNew()
    {
        return !$this->id;
    }

    public function getLabel()
    {
        $label = $this->getFullSku();
        if (stripos($label, $this->catalogNumber) === false) {
            $label .= " - " . $this->catalogNumber;
        }
        if ($this->quotationNumber) {
            $label .= " ({$this->quotationNumber})";
        }
        return $label;
    }

    public function getCatalogNumber()
    {
        return $this->catalogNumber;
    }

    public function setCatalogNumber($catalogNo)
    {
        $this->catalogNumber = $catalogNo;
        return $this;
    }

    public function getQuotationNumber()
    {
        return $this->quotationNumber;
    }

    public function setQuotationNumber($quoteNo)
    {
        $this->quotationNumber = trim($quoteNo);
    }

    /** @return StockItem */
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

    /** @return Version */
    public function getVersion()
    {
        return new Version($this->version);
    }

    public function setVersion(Version $version)
    {
        $this->version = (string) $version;
    }

    private function getFullSku()
    {
        return $this->getSku() . $this->getVersion()->getStockCodeSuffix();
    }

    /**
     * @return Version Guaranteed to be both specified and allowed by this
     *   purch data record.
     */
    public function getSpecifiedVersion(Version $version = null)
    {
        if (!$version) {
            $version = $this->getVersion();
        }
        if (!$version->isSpecified()) {
            $version = $this->stockItem->getSpecifiedVersionOrDefault($version);
        }
        if (!$version->matches($this->getVersion())) {
            $version = $this->getVersion();
        }
        assertion($version->isSpecified());
        return $version;
    }

    /**
     * @return bool True if you can order $version using this purch data.
     */
    public function supportsVersion(Version $version)
    {
        return $this->getVersion()->matches($version);
    }


    /** @return Supplier|null */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /** @return bool */
    public function hasSupplier()
    {
        return null !== $this->supplier;
    }

    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;
        $this->buildLocation = $supplier->getFacility();
        /* If the supplier IS a manufacturer, it overrides any existing one. */
        $this->manufacturer = $supplier->getManufacturer() ?: $this->manufacturer;
    }


    /** @return Facility|null */
    public function getBuildLocation()
    {
        return $this->buildLocation;
    }

    /** @deprecated use getBuildLocation() instead */
    public function getLocation()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getBuildLocation();
    }

    /** @Assert\Callback */
    public function validateSupplier(ExecutionContextInterface $context)
    {
        if ($this->isManufactured()) {
            if (!$this->buildLocation) {
                $context->addViolation('Manufacturing location is required.');
            }
        } elseif (!$this->supplier) {
            $context->addViolation('Supplier is required.');
        }
    }

    /**
     * @deprecated
     */
    public function setLocation(Facility $buildLocation)
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->setBuildLocation($buildLocation);
    }

    public function setBuildLocation(Facility $buildLocation)
    {
        $this->buildLocation = $buildLocation;
        $this->supplier = $buildLocation->getSupplier();
        return $this;
    }

    public function isActive()
    {
        return $this->buildLocation ? $this->buildLocation->isActive() : true;
    }

    public function getSupplierName()
    {
        if ($this->supplier) {
            return $this->supplier->getName();
        } elseif ($this->buildLocation) {
            return $this->buildLocation->getName();
        }
        return '';
    }

    public function getSupplierSummary()
    {
        return sprintf('%s - %s', $this->getSupplierName(),
            $this->catalogNumber);
    }

    public function getSupplierDomainName()
    {
        return $this->supplier->getDomainName();
    }

    public function getSupplierSearchUrl()
    {
        return ($this->supplier && $this->manufacturerCode)
            ? $this->supplier->getSearchUrl($this->manufacturerCode)
            : null;
    }

    /** @return CostBreak[] */
    public function getCostBreaks()
    {
        return $this->costBreaks->toArray();
    }

    /**
     * Creates a new cost break and adds it to $this.
     *
     * @return CostBreak
     */
    public function createCostBreak($orderQty, $unitCost, $manLeadTime)
    {
        $cost = new CostBreak();
        $cost->setMinimumOrderQty($orderQty);
        $cost->setUnitCost($unitCost);
        $cost->setManufacturerLeadTime($manLeadTime);
        $this->addCostBreak($cost);
        return $cost;
    }

    public function addCostBreak(CostBreak $cost)
    {
        $cost->setPurchasingData($this);
        $this->costBreaks[] = $cost;
    }

    public function removeCostBreak(CostBreak $cost)
    {
        $this->costBreaks->removeElement($cost);
    }

    public function setCostBreaks(array $breaks)
    {
        $this->clearCostBreaks();
        foreach ($breaks as $break) {
            $this->addCostBreak($break);
        }
    }

    /**
     * Removes all cost break records. Useful when syncing.
     */
    public function clearCostBreaks()
    {
        $this->costBreaks->clear();
    }

    /**
     * Symfony's built-in UniqueEntity validator won't work if two
     * cost breaks are "swapped" at the same time, because it queries
     * the database rather than looking at the updated, in-memory objects.
     *
     * @Assert\Callback
     */
    public function validateCostBreaks(ExecutionContextInterface $context)
    {
        $isSeen = [];
        foreach ($this->costBreaks as $break) {
            $qty = $break->getMinimumOrderQty();
            $mlt = $break->getManufacturerLeadTime();
            if (isset($isSeen[$qty][$mlt])) {
                $context->buildViolation("purchasing.cost_break.no_dups")
                    ->atPath('costBreaks')
                    ->addViolation();
                return;
            } else {
                $isSeen[$qty][$mlt] = true;
            }
        }
    }

    /** @deprecated Use getCostBreaks() */
    public function getCostLevels()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getCostBreaks();
    }

    /** @deprecated Use addCostBreak() */
    public function addCostLevel(CostBreak $cost)
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        $this->addCostBreak($cost);
    }

    /** @deprecated Use removeCostBreak() */
    public function removeCostLevel(CostBreak $cost)
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        $this->removeCostBreak($cost);
    }

    /**
     * @param int $orderQty
     *  The quantity to be ordered.
     * @return CostBreak
     *  Returns the CostBreak record with the lowest cost for
     *  the given order quantity.
     */
    public function getLowestCostBreak($orderQty)
    {
        if (count($this->costBreaks) == 0) {
            throw new PurchasingDataException($this, sprintf(
                "Purchasing data %s for %s has no cost breaks",
                $this->id,
                $this->stockItem
            ));
        }

        /* @var $lowestCost CostBreak|null
         * The cost level with the lowest cost for $orderQty */
        $lowestCost = null;

        /* @var $lowestQty CostBreak|null
         * If $orderQty is less than the minimum order qty, we return the
         * cost level with the lowest min order qty. */
        $lowestQty = null;

        foreach ($this->costBreaks as $costBreak) {
            if (!$lowestQty) {
                $lowestQty = $costBreak;
            } elseif ($costBreak->getMinimumOrderQty() < $lowestQty->getMinimumOrderQty()) {
                $lowestQty = $costBreak;
            }

            if ($orderQty < $costBreak->getMinimumOrderQty()) {
                continue;
            }
            if (!$lowestCost) {
                $lowestCost = $costBreak;
            } elseif ($costBreak->getUnitCost() < $lowestCost->getUnitCost()) {
                $lowestCost = $costBreak;
            }
        }
        return $lowestCost ?: $lowestQty;
    }

    /**
     * The cost for the given order quantity.
     *
     * @param int $orderQty (default = 1)
     * @return double|null
     *  Null if there are no cost level records.
     */
    public function getCost($orderQty = 1)
    {
        $costBreak = $this->getLowestCostBreak($orderQty);
        return $costBreak ? $costBreak->getUnitCost() : null;
    }

    /** @deprecated use getEconomicOrderQty() instead */
    public function getEoq()
    {
        return $this->getEconomicOrderQty();
    }

    /**
     * The economic order quantity (EOQ) for this item.
     */
    public function getEconomicOrderQty()
    {
        return $this->stockItem->getEconomicOrderQty();
    }

    public function getMinimumOrderQty()
    {
        if (count($this->costBreaks) == 0) {
            return null;
        }
        $min = PHP_INT_MAX;
        foreach ($this->costBreaks as $break) {
            $moq = $break->getMinimumOrderQty();
            if ($moq < $min) {
                $min = $moq;
            }
        }
        return $min;
    }

    public function getDefaultOrderQuantity()
    {
        return max($this->getEconomicOrderQty(), $this->getMinimumOrderQty());
    }

    /**
     * The unit cost if we order a quantity equal to the EOQ.
     *
     * @return float|null
     */
    public function getCostAtEoq()
    {
        return $this->getCost($this->getEconomicOrderQty());
    }

    /**
     * The unit cost if we order a quantity equal to the MOQ.
     */
    public function getCostAtMoq(): ?float
    {
        return $this->getCost($this->getMinimumOrderQty());
    }

    /**
     * Get the lowest possible extended cost of ordering with this purchasing data
     * (unit cost at MOQ * MOQ)
     */
    public function getMinimumOrderExtendedCost(): ?float
    {
        $unitCost = $this->getCostAtMoq();
        if ($unitCost) {
            return $unitCost * $this->getMinimumOrderQty();
        }

        return null;
    }

    /**
     * The unit value of this item that is used for stock accounting.
     *
     * @return float|null
     */
    public function getStandardCost()
    {
        return $this->stockItem->getStandardCost();
    }

    /**
     * The lead time for the given order quantity.
     *
     * @param int $orderQty (default = 1)
     * @return int
     */
    public function getLeadTime($orderQty = 1)
    {
        $orderQty = max($orderQty, 1);
        $costBreak = $this->getLowestCostBreak($orderQty);
        if ($this->stockLevel < $orderQty) {
            return $costBreak->getManufacturerLeadTime();
        } else {
            return $costBreak->hasSupplierLeadTime() ?
                $costBreak->getSupplierLeadTime() :
                $costBreak->getManufacturerLeadTime();
        }
    }

    /**
     * The lead time if we order a quantity equal to the EOQ.
     *
     * @return int|null
     */
    public function getLeadTimeAtEoq()
    {
        return $this->getLeadTime($this->getEconomicOrderQty());
    }

    public function getManufacturerLeadTime()
    {
        $break = $this->getLowestCostBreak(1);
        return $break->getManufacturerLeadTime();
    }

    /**
     * Overwrites the manuf. lead time for all cost breaks.
     *
     * @param int $days
     */
    public function setManufacturerLeadTime($days)
    {
        foreach ($this->costBreaks as $break) {
            $break->setManufacturerLeadTime($days);
        }
    }

    /**
     * The style of bin in which the supplier ships this item.
     * @return BinStyle
     */
    public function getBinStyle()
    {
        return $this->binStyle;
    }

    public function setBinStyle(BinStyle $binStyle)
    {
        $this->binStyle = $binStyle;
        return $this;
    }

    /** @return Manufacturer|null */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    public function setManufacturer(Manufacturer $manufacturer = null)
    {
        $this->manufacturer = $manufacturer;
    }

    public function initManufacturer(Manufacturer $manufacturer = null)
    {
        if (!$this->manufacturer) {
            $manufacturer = $manufacturer ?: $this->getManufacturerFromSupplier();
            $this->setManufacturer($manufacturer);
        }
    }

    private function getManufacturerFromSupplier()
    {
        return $this->supplier ? $this->supplier->getManufacturer() : null;
    }

    /**
     * @Assert\Callback
     */
    public function validateManufacturer(ExecutionContextInterface $context)
    {
        if (!($this->manufacturer && $this->supplier)) {
            return;
        }
        $sm = $this->supplier->getManufacturer();
        if (!$sm) {
            return;
        }
        if (!$sm->equals($this->manufacturer)) {
            $msg = 'Supplier {{ supplier }} does not match manufacturer {{ manufacturer }}.';
            $context->buildViolation($msg)
                ->setParameter('{{ supplier }}', $this->getSupplierName())
                ->setParameter('{{ manufacturer }}', $this->manufacturer->getName())
                ->addViolation();
        }
    }

    /**
     * @return string
     */
    public function getManufacturerCode()
    {
        return $this->manufacturerCode;
    }

    /**
     * @return int
     *  The number of units that come on a single reel or bin.
     */
    public function getBinSize()
    {
        return $this->binSize;
    }

    public function setBinSize($binSize)
    {
        $this->binSize = (int) $binSize;
    }

    /**
     * @return int
     *  Parts must be ordered in multiples of this quantity.
     */
    public function getIncrementQty()
    {
        return $this->incrementQty;
    }

    public function setIncrementQty($qty)
    {
        $this->incrementQty = $qty;
    }

    public function roundOrderQty($orderQty)
    {
        $units = $orderQty / $this->incrementQty;
        $units = (int) ceil($units);
        return $units * $this->incrementQty;
    }

    public function getQtyAvailable()
    {
        return $this->stockLevel;
    }

    public function setQtyAvailable($qty)
    {
        $this->stockLevel = $qty;
        $this->lastSync = new DateTime();
    }

    /** @deprecated use getQtyAvailable() instead */
    public function getStockLevel()
    {
        return $this->getQtyAvailable();
    }

    /** @deprecated use setQtyAvailable() instead */
    public function setStockLevel($stockLevel)
    {
        $this->setQtyAvailable($stockLevel);
    }

    /** @return DateTime|null */
    public function getLastSync()
    {
        return $this->lastSync ? clone $this->lastSync : null;
    }

    /**
     * True if the stock level has been updated within the given time interval
     *
     * @param string $interval eg, "-24 hours"
     * @return bool
     */
    public function isUpdatedSince($interval)
    {
        if (null === $this->lastSync) {
            return false;
        }
        $cutoff = new DateTime($interval);
        return $this->lastSync > $cutoff;
    }

    /**
     * @return string
     */
    public function getProductUrl()
    {
        return $this->productUrl;
    }

    /**
     * @param string $productUrl
     */
    public function setProductUrl($productUrl)
    {
        $this->productUrl = trim($productUrl);
    }

    /**
     * Set the product URL only if it has not already been set.
     *
     * @param string $productUrl
     */
    public function initProductUrl($productUrl)
    {
        if (!$this->productUrl) {
            $this->setProductUrl($productUrl);
        }
    }

    /**
     * @return string
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * @return string
     */
    public function getRoHS()
    {
        return $this->rohs;
    }

    public function setRoHS($RoHS)
    {
        $this->rohs = RoHS::normalize($RoHS);
    }

    /**
     * @return string
     */
    public function getSuppliersUOM()
    {
        return $this->suppliersUOM;
    }

    /**
     * @return string
     */
    public function getSupplierDescription()
    {
        return $this->supplierDescription;
    }

    /** @return string */
    public function getItemName()
    {
        return $this->stockItem->getName();
    }

    /**
     * @return string
     */
    public function getConversionFactor()
    {
        return null === $this->conversionFactor ? 1.0 : $this->conversionFactor;
    }

    /**
     * @return bool
     */
    public function isPreferred()
    {
        return (bool) $this->preferred;
    }

    /**
     * Purchasing Data that has already been persisted to the database should
     * be set as preferred with the {@see PurchasingDataRepo::setPreferred} method.
     */
    public function setPreferred(): void
    {
        if ($this->id) {
            throw new \LogicException(
                "Cannot set purchasing data {$this->id} as preferred since it has already been saved.");
        }
        $this->preferred = true;
    }

    /**
     * @return bool
     */
    public function isTurnkey()
    {
        return (bool) $this->turnkey;
    }

    /**
     * @return bool
     */
    public function isManufactured()
    {
        return $this->stockItem->isManufactured();
    }

    /**
     * Code that the manufacturer uses to describe the product
     */
    public function setManufacturerCode($manufacturerCode)
    {
        $this->manufacturerCode = trim($manufacturerCode);
        return $this;
    }

    public function setSuppliersUOM($suppliersUOM)
    {
        $this->suppliersUOM = $suppliersUOM;
        return $this;
    }

    public function setConversionFactor($conversionFactor)
    {
        $this->conversionFactor = $conversionFactor;
        return $this;
    }

    public function setSupplierDescription($desc)
    {
        $this->supplierDescription = trim($desc);
        return $this;
    }

    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function setTurnkey($turnkey)
    {
        $this->turnkey = $turnkey;
        return $this;
    }

    public function setEndOfLife(DateTime $endOfLife = null)
    {
        $this->endOfLife = $endOfLife ? clone $endOfLife : null;
    }

    /**
     * @return DateTime|null
     */
    public function getEndOfLife()
    {
        return $this->endOfLife ? clone $this->endOfLife : null;
    }

    public function isEndOfLife()
    {
        if (null === $this->endOfLife) {
            return false;
        }
        $today = new DateTime();
        return $this->endOfLife < $today;
    }

    public function __clone()
    {
        if (!$this->id) {
            return; // Required by Doctrine
        }

        $this->id = null;
        $this->supplier = null;
        $this->buildLocation = null;
        $this->preferred = false;

        $oldCostBreaks = $this->costBreaks->toArray();
        $this->costBreaks = new ArrayCollection();
        foreach ($oldCostBreaks as $oldCost) {
            $newCost = clone $oldCost;
            $this->addCostBreak($newCost);
        }
    }

    public function importStockLevel(CatalogItem $item)
    {
        $this->setQtyAvailable($item->getQtyAvailable());
        $this->initProductUrl($item->getProductUrl());
    }

    public function importAllFields(CatalogItem $entry)
    {
        $this->importStockLevel($entry);
        $this->setIncrementQty($entry->getIncrementQty() ?: 1);
        $this->setRoHS($entry->getRoHS() ?: $this->rohs);
        $this->setBinStyle($entry->getBinStyle() ?: $this->binStyle);
        $this->setBinSize($entry->getBinSize() ?: $this->binSize);
        $this->initManufacturer($entry->getManufacturer());

        /* If it is a custom quote, then we should not replace the quoted
         * pricing. */
        if (!$this->quotationNumber) {
            $this->clearCostBreaks();
            $manLeadTime = $entry->getLeadTime() ?: 0;
            foreach ($entry->getCostBreaks() as $cb) {
                $this->createCostBreak($cb->getMinimumOrderQty(), $cb->getUnitCost(), $manLeadTime);
            }
        }
    }
}
