<?php

namespace Rialto\Stock\Bin;

use Rialto\Accounting\Transaction\Transaction;
use Rialto\Allocation\Allocation\BinAllocation;
use Rialto\Allocation\Requirement\ConflictDetector;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Entity\DomainEvent;
use Rialto\Entity\HasDomainEvents;
use Rialto\IllegalStateException;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Stock\Bin\Event\BinQuantityChanged;
use Rialto\Stock\Cost\HasStandardCost;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Location;
use Rialto\Stock\Move\StockMove;
use Rialto\Stock\Shelf\ShelfPosition;
use Rialto\Stock\Transfer\Transfer;
use Rialto\UnexpectedClassException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A stock bin is any kind of container that stock items can be stored in.
 * Each bin contains only one type of stock item, all of the same version
 * and customization.
 */
class StockBin extends BasicStockSource
    implements HasStandardCost, HasDomainEvents
{
    const SOURCE_TYPE = 'StockBin';

    /** @var DomainEvent[] */
    private $events = [];

    /**
     * @param StockBin[] $bins
     * @return \SplObjectStorage
     *   Location => StockBin[]
     */
    public static function indexByLocation($bins)
    {
        $index = new \SplObjectStorage();
        foreach ($bins as $bin) {
            $loc = $bin->getLocation(); // TODO: bin location
            $bins = isset($index[$loc]) ? $index[$loc] : [];
            $bins[] = $bin;
            $index[$loc] = $bins;
        }
        return $index;
    }

    /**
     * @param StockBin[] $bins
     * @return StockBin[] indexed by stock code.
     */
    public static function indexByStockCode($bins)
    {
        $byStock = [];
        foreach ($bins as $bin) {
            $stockCode = $bin->getSku();
            if (! isset($byStock[$stockCode])) {
                $byStock[$stockCode] = [];
            }
            $byStock[$stockCode][] = $bin;
        }
        return $byStock;
    }


    /** @var int */
    private $id;

    /** @var StockItem */
    private $stockItem;

    /** @var Facility|null */
    private $facility = null;

    /** @var Transfer|null */
    private $transfer = null;

    /** @var int|float */
    private $quantity = 0;

    /**
     * The quantity of a bin cannot be changed directly: it can only be
     * changed in the context of a stock transaction. We use
     * setNewQty() and applyNewQty() to achieve this.
     *
     * @var int|float
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0, minMessage="New quantity cannot be negative.")
     */
    private $newQty = null;

    /**
     * @var BinStyle
     * @Assert\NotNull
     */
    private $binStyle = null;

    /** @var string */
    private $version;

    /** @var Customization */
    private $customization = null;

    /**
     * The manufacturer who made the items on this bin.
     * @var Manufacturer|null
     */
    private $manufacturer = null;

    /**
     * The manufacturer's part number for the items on this bin.
     * @var string
     */
    private $manufacturerCode = '';

    /**
     * The unit cost of each item in this bin that we paid to the supplier.
     * This plus $materialCost gives the total unit cost.
     *
     * @var float
     * @Assert\Range(min=0)
     * @see $materialCost
     */
    private $purchaseCost = 0.0;

    /**
     * If manufactured, the value of the subcomponents per unit of the
     * manufactured item. This plus $purchaseCost gives the total unit cost.
     *
     * @var float
     * @Assert\Range(min=0)
     * @see $purchaseCost
     */
    private $materialCost = 0.0;

    /**
     * Exactly where on the shelf this bin is located.
     *
     * @var ShelfPosition|null
     */
    private $shelfPosition = null;

    private $allocatable = true;

    /** @var string[][] $allocatableUpdates */
    private $allocatableUpdates = [];

    const DATE_FORMAT = 'Y-m-d';

    /**
     * @param StockItem $item The item in the bin
     * @param Facility $facility The location of the bin
     * @param Version $version (optional)
     */
    public function __construct(
        StockItem $item,
        Facility $facility,
        Version $version = null)
    {
        parent::__construct();
        $this->stockItem = $item;
        $this->facility = $facility;
        $this->setVersion($item->getSpecifiedVersionOrDefault($version));
    }

    public function getAllocatable()
    {
        return $this->allocatable;
    }

    public function setAllocatable(bool $boolean)
    {
        $this->allocatable = $boolean;
        $this->setAllocatableUpdates("auto", "received from RMA");
    }

    public function setAllocatableManual(bool $boolean, string $user, string $reason)
    {
        $this->allocatable = $boolean;
        //TODO add updatedBy method
        $this->setAllocatableUpdates($user, $reason);
    }

    /**
     * @return string[]
     */
    public function getAllocatableUpdates()
    {
        return $this->allocatableUpdates;
    }

    private function setAllocatableUpdates(string $updateBy, string $reason)
    {
        $updateOn = new \DateTime();
        $updateOn = $updateOn->format(self::DATE_FORMAT);

        array_push($this->allocatableUpdates, [
            'user' => $updateBy,
            'date' => $updateOn,
            "note" => $reason,
        ]);
    }

    public function setNewQty($newQty)
    {
        $this->newQty = $newQty;
    }

    public function setQtyDiff($diff)
    {
        $this->newQty = $this->quantity + $diff;
    }

    public function getQtyDiff()
    {
        return $this->hasNewQty() ?
            $this->newQty - $this->quantity : 0;
    }

    public function getNewQty()
    {
        return $this->newQty;
    }

    public function hasNewQty()
    {
        if (null === $this->newQty) {
            return false;
        }
        return $this->newQty != $this->quantity;
    }

    /** @return StockMove */
    public function applyNewQty(Transaction $trans, $memo = null)
    {
        if (! $this->hasNewQty()) {
            throw new IllegalStateException("Quantity of $this has not changed");
        }
        $move = StockMove::fromBin($this);
        $move->setQuantity($this->newQty - $this->quantity);
        $this->quantity = $this->newQty;
        $trans->addStockMove($move);

        /* The default transaction memo can be overridden on a per-StockMove basis */
        if ($memo) {
            $move->setMemo($memo);
        }

        $this->adjustAllocationsToMatchQtyRemaining();

        $this->events[] = new BinQuantityChanged($this, $this->quantity);

        return $move;
    }

    /**
     * Returns the stock location where this bin is located.
     *
     * @return Location
     */
    public function getLocation()
    {
        return $this->facility ?: $this->transfer;
    }

    /**
     * The facility where this bin is located.
     *
     * @return Facility
     * @throws IllegalStateException if this bin is in transit instead of at a facility
     */
    public function getFacility()
    {
        if ($this->facility) {
            return $this->facility;
        }
        throw new IllegalStateException("$this in in transit, not at a facility");
    }

    /**
     * @return bool True if this bin is at $location.
     */
    public function isAtLocation(Location $location)
    {
        return $this->getLocation()->equals($location);
    }

    /**
     * @return bool True if this bin is at $facility or a sublocation thereof.
     */
    public function isAtSublocationOf(Facility $facility)
    {
        return $this->facility && $this->facility->isSublocationOf($facility);
    }

    /**
     * Changes the location of this bin.
     *
     * @return StockBin
     *  Fluent interface
     */
    public function setLocation(Location $location)
    {
        if ($location instanceof Facility) {
            $this->facility = $location;
            $this->transfer = null;
            $this->clearShelfPositionIfNeeded($location);
        } elseif ($location instanceof Transfer) {
            $this->facility = null;
            $this->transfer = $location;
            $this->shelfPosition = null;
        } else {
            throw new UnexpectedClassException($location, "Unexpected location type");
        }

        return $this;
    }

    private function clearShelfPositionIfNeeded(Facility $facility)
    {
        if (!$this->shelfPositionMatches($facility)) {
            $this->clearShelfPosition();
        }
    }

    public function clearShelfPosition()
    {
        if ($this->shelfPosition) {
            $this->shelfPosition->clearBin();
            $this->shelfPosition = null;
        }
    }

    private function shelfPositionMatches(Facility $facility)
    {
        return $this->shelfPosition && $this->shelfPosition->isAtFacility($facility);
    }

    public function setShelfPosition(ShelfPosition $pos)
    {
        assertion($pos->isAtFacility($this->facility));
        $this->shelfPosition = $pos;
        $pos->setBin($this);
    }

    public function hasShelfPosition()
    {
        return null !== $this->shelfPosition;
    }

    /**
     * @return string
     */
    public function getShelfPosition()
    {
        return $this->shelfPosition
            ? $this->shelfPosition->getShortLabel()
            : '';
    }

    /**
     * @return boolean
     */
    public function isInTransit()
    {
        return null !== $this->transfer;
    }

    /**
     * @return BinStyle
     */
    public function getBinStyle()
    {
        return $this->binStyle;
    }

    /**
     * @param BinStyle $style
     * @return StockBin
     *  Fluent interface
     */
    public function setBinStyle(BinStyle $style)
    {
        $this->binStyle = $style;
        return $this;
    }

    /**
     * @param BinStyle|string $style
     * @return bool
     */
    public function isBinStyle($style)
    {
        return $this->binStyle->equals($style);
    }

    /**
     * When printing labels for this bin, how many copies should be printed?
     *
     * For example, we often need two: one for the bin itself and one for
     * the shelf position it occupies.
     */
    public function getNumLabels()
    {
        return $this->binStyle->getNumLabels();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /** @return boolean */
    public function isNew()
    {
        return ! $this->id;
    }

    protected function instantiateAllocation(Requirement $requirement)
    {
        return new BinAllocation($requirement, $this);
    }

    public function getQtyAvailableTo(RequirementCollection $requirements)
    {
        if (! $this->isCompatibleWith($requirements)) {
            return 0;
        }
        $detector = new ConflictDetector();
        $qtyAllocated = 0;
        foreach ($this->getAllocations() as $alloc) {
            if ($detector->isConflict($alloc, $requirements)) {
                return 0;
            }
            $qtyAllocated += $alloc->getQtyAllocated();
        }
        return $this->getQtyRemaining() - $qtyAllocated;
    }


    public function getQtyAllocated()
    {
        $qtyAllocated = 0;
        foreach ($this->getAllocations() as $alloc) {
            $qtyAllocated += $alloc->getQtyAllocated();
        }
        return $qtyAllocated;
    }

    public function getQtyRemaining()
    {
        return $this->getQuantity();
    }

    /**
     * @return double
     */
    public function getQuantity()
    {
        return (float) $this->quantity;
    }

    public function isEmpty()
    {
        return $this->getQtyRemaining() <= 0;
    }

    public function getSourceNumber()
    {
        return $this->getId();
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

    /**
     * The name of the item on this bin.
     * @return string
     */
    public function getItemName()
    {
        return $this->stockItem->getName();
    }

    /**
     *
     * @return StockItem
     */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function containsItem(Item $item)
    {
        return $this->stockItem->getSku() == $item->getSku();
    }

    /**
     * @return Version
     */
    public function getVersion()
    {
        return new Version($this->version);
    }

    public function setVersion(Version $version)
    {
        if (! $version->isSpecified()) {
            throw new \InvalidArgumentException('Version for stock bin must be specified');
        }
        $this->version = (string) $version;
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

    public function getFullSku()
    {
        $code = $this->getSku();
        $version = $this->getVersion();
        $code .= $version->getStockCodeSuffix();
        $code .= Customization::getStockCodeSuffix($this->customization);
        return $code;
    }

    /** @deprecated */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    public function getItemVersion(): ItemVersion
    {
        return $this->stockItem->getVersion($this->getVersion());
    }

    public function getCountryOfOrigin(): string
    {
        return $this->stockItem->getCountryOfOrigin();
    }

    public function getRohsStatus(): string
    {
        return $this->stockItem->getRoHS();
    }

    /**
     * @return bool True if this bin can be split into two.
     */
    public function canBeSplit()
    {
        if ($this->quantity <= 1) {
            return false;
        }
        return true;
    }

    /**
     * This method should only be called by Rialto\Stock\Bin\StockBinSplitter
     * @return StockBin
     */
    public function split($qtyToSplit)
    {
        assertion($this->canBeSplit(), "Cannot split $this");
        $this->setQtyDiff(-$qtyToSplit);
        $newBin = clone $this;
        $newBin->setNewQty($qtyToSplit);
        return $newBin;
    }

    /**
     * @return bool True if there are outstanding allocations against
     *   this bin from its current location.
     */
    public function isNeededAtCurrentLocation()
    {
        if ($this->isInTransit()) {
            return false;
        }

        $allocs = $this->getAllocations();
        foreach ($allocs as $alloc) {
            if ($alloc->isDelivered()) {
                continue;
            }
            if ($alloc->isForMissingStock()) {
                continue;
            }
            if ($alloc->isNeededAtLocation($this->getLocation())) {
                return true;
            }
        }
        return false;
    }

    public function __clone()
    {
        if (! $this->id) {
            return; // Required by Doctrine
        }
        parent::__clone();
        $this->id = null;
        $this->quantity = 0;
        $this->newQty = null;
    }

    public function getSourceDescription()
    {
        return sprintf('%s at %s',
            $this->getLabelWithQuantity(),
            $this->getLocation()->getName()
        );
    }

    public function getSourceType()
    {
        return self::SOURCE_TYPE;
    }

    public function __toString()
    {
        return sprintf("%s %s",
            $this->binStyle->getCategory(),
            $this->id);
    }

    public function getLabelWithQuantity()
    {
        return sprintf('%s (%s pcs)',
            $this,
            number_format($this->getQtyRemaining()));
    }

    public function getLabelWithQtyAndVersion()
    {
        return sprintf('%s - %s (%s pcs)',
            $this,
            $this->getFullSku(),
            number_format($this->getQtyRemaining()));
    }

    public function getLabelWithLocation()
    {
        $at = $this->isInTransit() ? 'in' : 'at';
        $location = $this->getLocation()->getName();
        return "$this $at $location";
    }

    public function getLongDescription()
    {
        return sprintf('%s on %s',
            $this->getSku(),
            $this->getSourceDescription());
    }

    /**
     * @return Manufacturer|null
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    public function setManufacturer(Manufacturer $manufacturer = null)
    {
        $this->manufacturer = $manufacturer;
    }

    public function getManufacturerCode()
    {
        return $this->manufacturerCode;
    }

    public function setManufacturerCode($code)
    {
        $this->manufacturerCode = trim($code);
    }

    /**
     * The unit cost of one item on this bin.
     * @return float
     */
    public function getUnitCost()
    {
        return $this->purchaseCost + $this->materialCost;
    }

    public function setPurchaseCost($cost)
    {
        $this->purchaseCost = $cost;
    }

    public function getPurchaseCost()
    {
        return $this->purchaseCost;
    }

    public function setMaterialCost($cost)
    {
        $this->materialCost = $cost;
    }

    public function getMaterialCost()
    {
        return $this->materialCost;
    }

    public function getUnitStandardCost()
    {
        return $this->stockItem->getStandardCost();
    }

    public function getExtendedStandardCost()
    {
        return $this->quantity * $this->getUnitStandardCost();
    }

    public function popEvents()
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}

