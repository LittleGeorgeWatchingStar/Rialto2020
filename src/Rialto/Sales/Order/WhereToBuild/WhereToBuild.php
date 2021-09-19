<?php

namespace Rialto\Sales\Order\WhereToBuild;

use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Manufacturing\Requirement\ScrapCalculator;
use Rialto\Manufacturing\WorkOrder\Orm\WorkOrderRepository;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Panelization\Panelizer;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;

/**
 * This class helps determine which manufacturing {@see Facility} has the most parts
 * that can be allocated to a {@see SalesOrderDetail}.
 */
class WhereToBuild
{
    /** @var StockItemRepository */
    private $stockItemRepository;

    /** @var StockBinRepository */
    private $stockBinRepository;

    /** @var WorkOrderRepository */
    private $workOrderRepository;

    /** @var PurchasingDataRepository */
    private $purchasingDataRepository;

    /** @var ScrapCalculator */
    private $scrapCalculator;


    /** @var SalesOrderDetail */
    private $lineItem;

    /** @var ManufacturedStockItem|null */
    private $parent = null;

    /** @var ManufacturedStockItem|null */
    private $child = null;

    /** @var Version|null */
    private $parentVersion = null;

    /** @var Version|null */
    private $childVersion = null;

    /** @var PurchasingData|null */
    private $preferredPurchasingData = null;


    /**
     * Work orders for {@see $itemToBuild}
     * @var WorkOrder[]
     */
    private $workOrders;

    /**
     * Indexed by {@see Facility::getId()}
     * @var Facility[]
     */
    private $facilities;

    /** @var BomItemAvailability[] */
    private $bomItemAvailabilities;

    /**
     * Indexed by {@see Facility::getId()}
     * @var ManufacturedStockItemFacilityAvailability[]
     */
    private $facilityAvailabilities;

    /** @var int|null */
    private $userInputQtyToOrder;

    public function __construct(SalesOrderDetail $lineItem, DbManager $dbm, int $userInputQtyToOrder = null)
    {
        $this->stockItemRepository = $dbm->getRepository(StockItem::class);
        $this->stockBinRepository = $dbm->getRepository(StockBin::class);
        $this->workOrderRepository = $dbm->getRepository(WorkOrder::class);
        $this->purchasingDataRepository = $dbm->getRepository(PurchasingData::class);
        $this->scrapCalculator = new ScrapCalculator($dbm);

        $this->lineItem = $lineItem;
        $this->userInputQtyToOrder = $userInputQtyToOrder;
        $this->initItemToBuild($lineItem);
        $this->initWorkOrders();
        $this->initPreferredPurchasingData();

        $this->initBomItemAvailabilities();
        $this->initFacilityAvailabilities();
    }

    private function initItemToBuild(SalesOrderDetail $lineItem)
    {
        if ($lineItem->getStockItem() instanceof ManufacturedStockItem) {
            $this->parent = $lineItem->getStockItem();
            $this->parentVersion = $this->parent->getSpecifiedVersionOrDefault($lineItem->getVersion());

            $child = $this->stockItemRepository->findComponentBoard(
                $this->parent,
                $this->parentVersion);

            if ($child instanceof ManufacturedStockItem) {
                $this->child = $child;
                $this->childVersion = $this->child->getSpecifiedVersionOrDefault(
                    $this->parent->getBom($this->parentVersion)->getItem($this->child)->getVersion());
            }
        }
    }

    /**
     * @param ManufacturedStockItem|null $itemToBuild
     */
    private function initWorkOrders()
    {
        $this->workOrders = [];
        if ($this->parent && $this->parentVersion) {
            $this->workOrders = $this->workOrderRepository->findByStockItem($this->parent);
        }
    }

    private function initPreferredPurchasingData()
    {
        if ($this->parent && $this->parentVersion) {
            $this->preferredPurchasingData = $this->purchasingDataRepository->findPreferredByVersion(
                $this->parent,
                $this->parentVersion,
                $this->getQtyOrdered());
        }
    }

    public function getUserInput(){
        return $this->userInputQtyToOrder;
    }

    public function getVersion(): Version
    {
        return $this->parentVersion;
    }

    public function hasChild(): bool
    {
        return $this->child !== null;
    }

    /**
     * @return PurchasingData|null
     */
    public function getPreferredPurchasingData()
    {
        return $this->preferredPurchasingData;
    }

    /**
     * @return ManufacturedStockItem|null
     */
    private function getItemToBuild()
    {
        return $this->child ?: $this->parent;
    }

    /**
     * @return BomItem[]
     */
    private function getBomItems()
    {
        if (!$this->getItemToBuild()) {
            return [];
        }
        $version = $this->getItemToBuild()->getSpecifiedVersionOrDefault();
        return $this->getItemToBuild()->getBom($version)->getItems();
    }

    public function getQtyOrdered(): int
    {
        return $this->userInputQtyToOrder === null ? $this->lineItem->getTotalQtyOrdered() : $this->userInputQtyToOrder;
    }

    private function initBomItemAvailabilities()
    {
        $this->facilities = [];
        foreach ($this->workOrders as $workOrder) {
            $this->addFacility($workOrder->getLocation());
        }

        $bomItemAvailabilities = [];
        foreach($this->getBomItems() as $bomItem) {

            $bomItemAvailability = new BomItemAvailability($bomItem);
            $bomItemAvailability->addQtyOrdered($this->getQtyOrdered() * $bomItem->getUnitQty());
            $bomItemAvailability->addQtyScrap($this->scrapCalculator->getPackageScrapCount($bomItem->getPackage()));

            $stockItem = $bomItem->getStockItem();
            $stockItemVersion = $stockItem->getSpecifiedVersionOrDefault();
            $stockBins = $this->stockBinRepository->findByItem($stockItem, $stockItemVersion);
            foreach ($stockBins as $stockBin) {
                if ($stockBin->isInTransit()) {
                    continue;
                }
                $facility = $stockBin->getFacility();
                $this->addFacility($facility);
                $bomItemAvailability->addUnallocatedToFacilityAvailability($facility, $stockBin->getQtyUnallocated());
            }

            $bomItemAvailabilities[] = $bomItemAvailability;
        }

        $this->bomItemAvailabilities = $bomItemAvailabilities;

        /**
         * Fill in the missing {@see BomItemFacilityAvailability}.
         */
        foreach ($this->bomItemAvailabilities as $bomItemAvailability) {
            foreach ($this->facilities as $facility) {
                $bomItemAvailability->addUnallocatedToFacilityAvailability($facility);
            }
        }
    }

    private function initFacilityAvailabilities()
    {
        $this->facilityAvailabilities = [];

        foreach ($this->facilities as $facility) {
            $facilityAvailability = new ManufacturedStockItemFacilityAvailability($facility);

            $preferredPurchasingData = $this->purchasingDataRepository->findPreferredByLocationAndVersion(
                $facility,
                $this->parent,
                $this->parentVersion,
                $this->getQtyOrdered());
            $facilityAvailability->setPreferredPurchasingData($preferredPurchasingData);

            if ($this->child) {
                $preferredChildPurchasingData = $this->purchasingDataRepository->findPreferredByLocationAndVersion(
                    $facility,
                    $this->child,
                    $this->childVersion,
                    $this->getQtyOrdered());
                $facilityAvailability->setPreferredChildPurchasingData($preferredChildPurchasingData);
            }

            $this->facilityAvailabilities[$facility->getId()] = $facilityAvailability;
        }


        foreach ($this->bomItemAvailabilities as $stockItemAvailability) {
            foreach ($this->facilityAvailabilities as $facilityAvailability) {
                $facility = $facilityAvailability->getFacility();

                $facilityAvailability->addQtyTotalStockItemsNeeded($stockItemAvailability->getQtyNeeded());
                $facilityAvailability->addQtyUniqueStockItems(1);

                $stockItemFacilityAvailability = $stockItemAvailability->getFacilityAvailability($facility);
                if (!$stockItemFacilityAvailability) {
                   continue;
                }
                $facilityAvailability->addQtyTotalStockItemsCanBeAllocated($stockItemFacilityAvailability->getQtyCanBeAllocated());
                if ($stockItemFacilityAvailability->isFullyAllocatable()) {
                    $facilityAvailability->addQtyFullyAllocatableUniqueStockItems(1);
                }
                if ($stockItemFacilityAvailability->isFullyAllocatableWithHeadquarters()) {
                    $facilityAvailability->addQtyFullyAllocatableUniqueStockItemsWithHeadquarters(1);
                }
            }
        }


        foreach ($this->workOrders as $workOrder) {
            $facility = $workOrder->getLocation();
            $isPanelized = $workOrder->getPurchaseOrder()->isInitiatedBy(Panelizer::INITIATOR_CODE);

            if (array_key_exists($facility->getId(), $this->facilityAvailabilities) &&
                !$isPanelized &&
                $workOrder->getQtyReceived() > 0) {
                $facilityAvailability = $this->facilityAvailabilities[$facility->getId()];
                $facilityAvailability->addBuiltVersion($workOrder->getVersion());
            }
        }
    }

    /**
     * @return Facility[]
     */
    private function getFacilities()
    {
        return array_values($this->facilities);
    }

    public function getBuildFacilities()
    {
        return array_map(
            function (ManufacturedStockItemFacilityAvailability $facilityAvailability) {
                return $facilityAvailability->getFacility();
            },
            $this->getBuildFacilityAvailabilities());
    }

    private function addFacility(Facility $facility)
    {
        $this->facilities[$facility->getId()] = $facility;
    }

    /**
     * @return BomItemAvailability[]
     */
    public function getBomItemAvailabilities()
    {
        return $this->bomItemAvailabilities;
    }

    /**
     * @return ManufacturedStockItemFacilityAvailability[]
     */
    public function getFacilityAvailabilities()
    {
        return $this->facilityAvailabilities;
    }

    /**
     * @return ManufacturedStockItemFacilityAvailability[]
     */
    public function getBuildFacilityAvailabilities()
    {
        $buildFacilityAvailabilities = array_filter(
            $this->facilityAvailabilities,
            function (ManufacturedStockItemFacilityAvailability $facilityAvailability) {
                $facility = $facilityAvailability->getFacility();
                $isManufacturingFacility = $facility->isActive() && $facility->hasSupplier();
                return $isManufacturingFacility;
            });
        usort($buildFacilityAvailabilities,
            function (ManufacturedStockItemFacilityAvailability $a, ManufacturedStockItemFacilityAvailability $b) {
                return $b->getQtyTotalStockItemsCanBeAllocated() - $a->getQtyTotalStockItemsCanBeAllocated();
            });
        return $buildFacilityAvailabilities;
    }
}

/**
 * This class keeps track of which {@see Facility} has allocatable stock for a
 * {@see BomItem}
 */
class BomItemAvailability
{
    /** @var BomItem */
    private $bomItem;

    /**
     * indexed by {@see Facility::getId()}.
     * @var BomItemFacilityAvailability[]
     */
    private $facilityAvailabilities = [];

    /** @var int */
    private $qtyOrdered = 0;

    /** @var int */
    private $qtyScrap = 0;

    /**
     * @param StockBin[] $stockBins
     */
    public function __construct(BomItem $bomItem)
    {
        $this->bomItem = $bomItem;
    }

    public function getQtyOrdered(): int
    {
        return $this->qtyOrdered;
    }

    public function addQtyOrdered(int $qty)
    {
        $this->qtyOrdered += $qty;
    }

    public function getQtyScrap(): int
    {
        return $this->qtyScrap;
    }

    public function addQtyScrap(int $qty)
    {
        $this->qtyScrap += $qty;
    }

    public function getQtyNeeded(): int
    {
        return $this->qtyOrdered + $this->qtyScrap;
    }

    public function getBomItem(): BomItem {
        return $this->bomItem;
    }

    public function addUnallocatedToFacilityAvailability(Facility $facility, int $qtyUnallocated = 0)
    {
        $facilityAvailability = $this->getFacilityAvailability($facility);
        if ($facilityAvailability === null) {
            $facilityAvailability = new BomItemFacilityAvailability($this, $facility);
            $this->facilityAvailabilities[$facility->getId()] = $facilityAvailability;
        }

        if ($qtyUnallocated) {
            $facilityAvailability->addQtyBinsUnallocated($qtyUnallocated);
        }
    }

    /**
     * @return BomItemFacilityAvailability[]
     */
    public function getFacilityAvailabilities()
    {
        return array_values($this->facilityAvailabilities);
    }

    /**
     * @return BomItemFacilityAvailability|null
     */
    public function getFacilityAvailability(Facility $facility)
    {
        if (array_key_exists($facility->getId(), $this->facilityAvailabilities)) {
            return $this->facilityAvailabilities[$facility->getId()];
        }
        return null;
    }

    /**
     * @return BomItemFacilityAvailability|null
     */
    public function getHeadquartersAvailability()
    {
        foreach ($this->facilityAvailabilities as $facilityAvailability) {
            if ($facilityAvailability->getFacility()->isHeadquarters()) {
                return $facilityAvailability;
            }
        }
        return null;
    }
}

class BomItemFacilityAvailability
{
    /** @var BomItemAvailability  */
    private $stockItemAvailability;

    /** @var Facility */
    private $facility;

    /** @var int */
    private $qtyBinsUnallocated = 0;

    public function __construct(BomItemAvailability $stockItemAvailability, Facility $facility)
    {
        $this->stockItemAvailability = $stockItemAvailability;
        $this->facility = $facility;
    }

    public function getQtyBinsUnallocated(): int
    {
        return $this->qtyBinsUnallocated;
    }

    public function addQtyBinsUnallocated(int $qty)
    {
        $this->qtyBinsUnallocated += $qty;
    }

    public function getQtyCanBeAllocated(): int
    {
        return min($this->getQtyBinsUnallocated(), $this->stockItemAvailability->getQtyNeeded());
    }

    /**
     * Quantity needed to be order after allocating from the {@see Facility}
     * and the headquarters ({@see Facility::isHeadquarters()}).
     */
    public function getQtyToOrder(): int
    {
        $headquartersAvailability = $this->stockItemAvailability->getHeadquartersAvailability();
        $qtyBinsUnallocatedAtHq = $headquartersAvailability ? $headquartersAvailability->getQtyBinsUnallocated() : 0;
        return max($this->stockItemAvailability->getQtyNeeded() - $this->getQtyBinsUnallocated() - $qtyBinsUnallocatedAtHq, 0);
    }

    public function isFullyAllocatable(): bool {
        return $this->getQtyBinsUnallocated() >= $this->stockItemAvailability->getQtyNeeded();
    }

    /**
     * Can be fully allocated by {@see StockBin} at the {@see Facility} plus
     * the {@see StockBin} at headquarters ({@see Facility::isHeadquarters()}).
     */
    public function isFullyAllocatableWithHeadquarters(): bool
    {
        $headquartersAvailability = $this->stockItemAvailability->getHeadquartersAvailability();
        $qtyBinsUnallocatedAtHq = $headquartersAvailability ? $headquartersAvailability->getQtyBinsUnallocated() : 0;
        return $this->getQtyBinsUnallocated() + $qtyBinsUnallocatedAtHq >= $this->stockItemAvailability->getQtyNeeded();
    }

    public function getFacility(): Facility
    {
        return $this->facility;
    }
}

/**
 * Keeps tracks of how many {@see BomItem} out of the aggregate of all the
 * {@see BomItem} in a {@see ManufacturedStockItem} for a {@see Facility}.
 */
class ManufacturedStockItemFacilityAvailability
{
    /** @var Facility */
    private $facility;

    /**
     * Indexed by version code.
     * @var Version[]
     */
    private $builtVersions = [];

    /**
     * @var PurchasingData|null
     */
    private $preferredPurchasingData = null;

    /**
     * @var PurchasingData|null
     */
    private $preferredChildPurchasingData = null;

    /**
     * Total number of individual {@see StockItems} needed
     * @var int
     */
    private $qtyTotalStockItemsNeeded = 0;

    /**
     * Total number of individual {@see StockItems} that can be allocated
     * @var int
     */
    private $qtyTotalStockItemsCanBeAllocated = 0;

    /**
     * Total number of unique {@see stockItems} (unique SKU) that is fully
     * allocated by the {@see Facility} .
     * @var int
     */
    private $qtyFullyAllocatableUniqueStockItems = 0;

    /**
     * Total number of unique {@see stockItems} (unique SKU) that is fully
     * allocated by the {@see Facility} and headquarters
     * ({@see Facility::isHeadquarters()}).
     * @var int
     */
    private $qtyFullyAllocatableUniqueStockItemsWithHeadquarters = 0;

    /**
     * Total number of unique {@see stockItems} (unique SKU).
     * @var int
     */
    private $qtyUniqueStockItems = 0;

    public function __construct(Facility $facility)
    {
        $this->facility = $facility;
    }

    /**
     * @return Version[]
     */
    public function getBuiltVersions(): array
    {
        return array_values($this->builtVersions);
    }

    public function addBuiltVersion(Version $version)
    {
        $this->builtVersions[$version->getVersionCode()] = $version;
    }

    /**
     * @return PurchasingData|null
     */
    public function getPreferredPurchasingData()
    {
        return $this->preferredPurchasingData;
    }

    /**
     * @param PurchasingData|null $preferredPurchasingData
     */
    public function setPreferredPurchasingData($preferredPurchasingData)
    {
        $this->preferredPurchasingData = $preferredPurchasingData;
    }

    /**
     * @return PurchasingData|null
     */
    public function getPreferredChildPurchasingData()
    {
        return $this->preferredChildPurchasingData;
    }

    /**
     * @param PurchasingData|null $preferredPurchasingData
     */
    public function setPreferredChildPurchasingData($preferredPurchasingData)
    {
        $this->preferredChildPurchasingData = $preferredPurchasingData;
    }

    public function getQtyTotalStockItemsNeeded(): int
    {
        return $this->qtyTotalStockItemsNeeded;
    }

    public function addQtyTotalStockItemsNeeded(int $qty)
    {
        $this->qtyTotalStockItemsNeeded += $qty;
    }

    public function getQtyTotalStockItemsCanBeAllocated(): int
    {
        return $this->qtyTotalStockItemsCanBeAllocated;
    }

    public function addQtyTotalStockItemsCanBeAllocated(int $qty)
    {
        $this->qtyTotalStockItemsCanBeAllocated += $qty;
    }


    public function getQtyFullyAllocatableUniqueStockItems(): int
    {
        return $this->qtyFullyAllocatableUniqueStockItems;
    }

    public function addQtyFullyAllocatableUniqueStockItems(int $qty)
    {
        $this->qtyFullyAllocatableUniqueStockItems += $qty;
    }

    public function getQtyFullyAllocatableUniqueStockItemsWithHeadquarters(): int
    {
        return $this->qtyFullyAllocatableUniqueStockItemsWithHeadquarters;
    }

    public function addQtyFullyAllocatableUniqueStockItemsWithHeadquarters(int $qty)
    {
        $this->qtyFullyAllocatableUniqueStockItemsWithHeadquarters += $qty;
    }


    public function getQtyUniqueStockItems(): int
    {
        return $this->qtyUniqueStockItems;
    }

    public function addQtyUniqueStockItems(int $qty)
    {
        $this->qtyUniqueStockItems += $qty;
    }

    public function getFacility(): Facility
    {
        return $this->facility;
    }
}