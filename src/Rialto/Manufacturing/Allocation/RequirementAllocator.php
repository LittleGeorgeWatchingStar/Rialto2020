<?php

namespace Rialto\Manufacturing\Allocation;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Allocation\InvalidAllocationException;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Requirement\ConsolidatedRequirement;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Allocation\Source\SourceCollection;
use Rialto\Allocation\Status\AllocationStatus;
use Rialto\Allocation\Status\RequirementStatus;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Requirement\Validator\VersionIsActive;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Producer\StockProducerFactory;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Controls stock allocation for a work order requirement.
 *
 * @VersionIsActive
 */
class RequirementAllocator extends ConsolidatedRequirement
{
    const ON_ORDER = 'order';

    const ALLOCATED = 'allocated';

    /** @var AllocationStatus */
    private $status = null;

    /**
     * @var PurchasingData
     * @Assert\NotNull(
     *   message="No purchasing data matches the requirements.",
     *   groups="purchasing")
     * @Assert\Valid
     */
    private $purchData;

    /** @var AllocationConfiguration[] */
    private $allocationConfigurations = [];
    private $total = [];
    private $available = [];
    private $toAllocate = [];
    private $toOrder = 0;
    /** @var StockAllocation[] */
    private $stockAllocations = [];
    private $errors = [];

    /** @var SourceCollection[] */
    private $sources = [];
    /** @var SourceCollection[] */
    private $candidateSources = [];

    /** @var int[]|null */
    private $userSelectionSourcesIds;

    /**
     * The manufacturing location. Null if this is an order for purchased
     * parts rather than manufactured items.
     *
     * @var Facility|null
     */
    private $buildLocation = null;
    /** @var int */
    private $highestCandidatePriority = 0;
    
    private $qtyStillNeedAllocatedByCandidates = 0;
    /** @var Facility[] */
    private $workOrderLocations = [];
    /** @var DbManager */
    private $dbm;

    /** @param AllocationConfiguration[] $allocationConfigurations */
    public function setAllocationConfigurations(array $allocationConfigurations)
    {
        $this->allocationConfigurations = $allocationConfigurations;
    }

    /** @return AllocationConfiguration[]|null */
    public function getAllocationConfigurations()
    {
        return $this->allocationConfigurations;
    }

    public function setUserSelectionSourcesIds($newArray)
    {
        $this->userSelectionSourcesIds = $newArray;
    }

    public function getUserSelectionSourcesIds()
    {
        return $this->userSelectionSourcesIds;
    }

    /** @param Facility $location*/
    public function setBuildLocation(Facility $location)
    {
        $this->buildLocation = $location;
    }

    /** @return Facility|null */
    public function getBuildLocation()
    {
        return $this->buildLocation;
    }

    /** @param Facility[] $workOrderLocations */
    public function setWorkOrderLocations(array $workOrderLocations)
    {
        $this->workOrderLocations = $workOrderLocations;
    }

    /** @return Facility[] */
    public function getWorkOrderLocations()
    {
        return $this->workOrderLocations;
    }

    /** @param DbManager $dbm*/
    public function setDbm(DbManager $dbm)
    {
        $this->dbm = $dbm;
    }

    /** @return DbManager */
    public function getDbm()
    {
        return $this->dbm;
    }

    /**
     * @param DbManager $dbm
     */
    public function setCandidateSources(DbManager $dbm)
    {
        $this->iniQtyStillNeedAllocatedByCandidates();
        if ($this->allocationConfigurations !== null) {
            /** @var AllocationConfiguration[] $sortedAllocationConfigurations*/
            $sortedAllocationConfigurations = $this->allocationConfigurations;
            usort($sortedAllocationConfigurations, function(AllocationConfiguration $a, AllocationConfiguration $b) {
                return $a->getPriority() - $b->getPriority();
            });
            foreach ($sortedAllocationConfigurations as $allocationConfiguration) {
                if (!$allocationConfiguration->isDisabled()) {
                    if ($allocationConfiguration->getType() === AllocationConfiguration::TYPE_WAREHOUSE_STOCK) {
                        $this->useWarehouseAsAllocationSource($allocationConfiguration, $dbm);
                    } elseif ($allocationConfiguration->getType() === AllocationConfiguration::TYPE_PO_ITEMS) {
                        $this->usePOAsAllocationSource($allocationConfiguration, $dbm);
                    } elseif ($allocationConfiguration->getType() === AllocationConfiguration::TYPE_CONTRACT_MANUFACTURER_STOCK) {
                        $this->useCMAsAllocationSource($allocationConfiguration, $dbm);
                    }
                }
            }
        }
    }

    private function useWarehouseAsAllocationSource(AllocationConfiguration $allocationConfiguration, DbManager $dbm): void
    {
        $warehouse = new Facility(Facility::HEADQUARTERS_ID);
        $locId = $warehouse->getId();
        $source = SourceCollection::fromAvailableBins($this, $warehouse, $dbm);
        $sourcesAvailableInSourceCollection = $sourcesAvailableInSourceCollection = $this->numberOfBinsAvailableInSourceCollection($allocationConfiguration, $source, $warehouse);;

        if ($sourcesAvailableInSourceCollection > 0) {
            $this->candidateSources[$locId] = $source;
        }
    }

    private function useCMAsAllocationSource(AllocationConfiguration $allocationConfiguration, DbManager $dbm): void
    {
        foreach ($this->workOrderLocations as $location) {
            if ($location->getId() != Facility::HEADQUARTERS_ID) {
                $locId = $location->getId();
                $warehouse = new Facility(Facility::HEADQUARTERS_ID);
                $source = SourceCollection::fromAvailableBins($this, $location, $dbm);
                $sourcesAvailableInSourceCollection = $this->numberOfBinsAvailableInSourceCollection($allocationConfiguration, $source, $warehouse);

                if ($sourcesAvailableInSourceCollection > 0) {
                    $this->candidateSources[$locId] = $source;
                }
            }
        }
    }

    private function usePOAsAllocationSource(AllocationConfiguration $allocationConfiguration, DbManager $dbm): void
    {
        $source = SourceCollection::fromOpenOrders($this, $dbm);
        $sourcesAvailableInSourceCollection = 0;
        foreach ($source->getSources() as $basicStockSource){
            if ($basicStockSource instanceof StockProducer) {
                $canAllocate = true;
                foreach ($basicStockSource->getAllocations() as $stockAllocation) {
                    if ($stockAllocation->getLocationWhereNeeded() !== $this->getBuildLocation()) {
                        $canAllocate = false;
                        break;
                    }
                }
                $basicStockSource->setCanBeAllocated($canAllocate);
                $basicStockSource->setUseThis($canAllocate);
                if ($canAllocate) {
                    $sourcesAvailableInSourceCollection++;
                    if ($this->getQtyStillNeedAllocatedByCandidates() > 0) {
                        $this->updateQtyStillNeedAllocatedByCandidates($basicStockSource->getQtyRemaining());
                        if ($allocationConfiguration->getPriority() > $this->highestCandidatePriority) {
                            $this->highestCandidatePriority = $allocationConfiguration->getPriority();
                        }
                    }
                } else {
                    $source->removeSourcesById($basicStockSource->getId());
                }
            }
        }
        if ($sourcesAvailableInSourceCollection > 0) {
            $this->candidateSources[self::ON_ORDER] = $source;
        }
    }

    private function numberOfBinsAvailableInSourceCollection(AllocationConfiguration $allocationConfiguration, SourceCollection $source, Facility $warehouse): int
    {
        $sourcesAvailableInSourceCollection = 0;
        foreach ($source->getSources() as $basicStockSource){
            /** @var StockBin $basicStockSource */
            if ($basicStockSource instanceof StockBin) {
                $canAllocate = true;
                if (sizeof($basicStockSource->getAllocations()) > 0) {
                    $canAllocate = false;
                } else {
                    $basicStockSource->setCanBeAllocated($canAllocate);
                    $basicStockSource->setUseThis($canAllocate);
                }

                if ($canAllocate) {
                    $sourcesAvailableInSourceCollection++;
                    if ($this->getQtyStillNeedAllocatedByCandidates() > 0) {
                        $this->updateQtyStillNeedAllocatedByCandidates($basicStockSource->getQtyRemaining());
                        if ($allocationConfiguration->getPriority() > $this->highestCandidatePriority) {
                            $this->highestCandidatePriority = $allocationConfiguration->getPriority();
                        }
                    }
                } else {
                    $source->removeSourcesById($basicStockSource->getId());
                }
            }
        }
        return $sourcesAvailableInSourceCollection;
    }

    /** @return SourceCollection[] */
    public function getSources(): array
    {
        return $this->sources;
    }

    /** @return SourceCollection[] */
    public function getCandidateSources(): array
    {
        return $this->candidateSources;
    }

    private function setAllocations()
    {
        $this->stockAllocations = $this->getAllocations();
    }

    public function getAllocations()
    {
        return parent::getAllocations();
    }

    public function addRequirement(Requirement $requirement)
    {
        parent::addRequirement($requirement);
        $this->refreshAllocationStatus();
    }

    private function refreshAllocationStatus()
    {
        $this->status = RequirementStatus::forRequirement($this);
    }

    public function setLocations(array $locations, DbManager $dbm)
    {
        $this->candidateSources = [];
        $this->sources = [];
        $this->toOrder = $this->getQtyStillNeeded();

        foreach ($locations as $loc) {
            $this->toOrder -= $this->addLocation($loc, $dbm);
        }
        $this->toOrder -= $this->addQuantitiesOnOrder($dbm);

        $this->toOrder = $this->toOrder > 0 ?
            max($this->toOrder, $this->getEconomicOrderQty()) :
            0;
    }

    protected function getEconomicOrderQty()
    {
        return $this->getStockItem()->getEconomicOrderQty();
    }

    private function addLocation(Facility $loc, DbManager $dbm)
    {
        $locId = $loc->getId();
        $source = SourceCollection::fromAvailableBins($this, $loc, $dbm);
        return $this->addSource($locId, $source);
    }

    private function addQuantitiesOnOrder(DbManager $dbm)
    {
        $source = SourceCollection::fromOpenOrders($this, $dbm);
        return $this->addSource(self::ON_ORDER, $source);
    }

    /**
     * @return int The quantity to allocate from the source.
     */
    private function addSource($indexKey, SourceCollection $source)
    {
        if ($this->allocationConfigurations == null) {
            $this->sources[$indexKey] = $source;
        }

        $this->total[$indexKey] = $source->getQtyRemaining();
        $qtyAvailable = $source->getQtyAvailableTo($this);
        $this->available[$indexKey] = $qtyAvailable;

        $qtyToAllocate = min($this->toOrder, $qtyAvailable);
        $this->toAllocate[$indexKey] = $qtyToAllocate;
        return $qtyToAllocate;
    }

    public function getHighestCandidatePriority()
    {
        return $this->highestCandidatePriority;
    }

    public function loadPurchasingData(ObjectManager $dbm)
    {
        /** @var $repo PurchasingDataRepository */
        $repo = $dbm->getRepository(PurchasingData::class);
        $this->purchData = $repo->findPreferredForRequirement($this);
    }

    /**
     * @return bool
     */
    public function hasPurchasingData()
    {
        return $this->purchData !== null;
    }

    public function getEoq()
    {
        return $this->purchData ? $this->purchData->getEconomicOrderQty() : 0;
    }

    public function getSupplier()
    {
        return $this->purchData ? $this->purchData->getSupplier() : null;
    }

    public function getDescription()
    {
        return $this->getStockItem()->getName();
    }

    public function getQtyNeeded()
    {
        return $this->status->getQtyNeeded();
    }

    public function getQtyAllocated()
    {
        return $this->status->getQtyAllocated();
    }

    public function getQtyStillNeeded()
    {
        return $this->getQtyNeeded() - $this->getQtyAllocated();
    }

    public function iniQtyStillNeedAllocatedByCandidates()
    {
        $this->qtyStillNeedAllocatedByCandidates = $this->status->getQtyNeeded() - $this->status->getQtyAllocated();
    }

    public function updateQtyStillNeedAllocatedByCandidates(int $qty)
    {
        $oldVal = $this->qtyStillNeedAllocatedByCandidates;
        $this->qtyStillNeedAllocatedByCandidates = $oldVal - $qty;
    }

    public function getQtyStillNeedAllocatedByCandidates()
    {
        return $this->qtyStillNeedAllocatedByCandidates;
    }

    public function isAllocatedFromManufacturer()
    {
        return $this->status->isKitComplete();
    }

    public function isAllocatedFromStock()
    {
        return $this->status->isFullyStocked();
    }

    public function isFullyAllocated()
    {
        return $this->status->isFullyAllocated();
    }

    public function getTotalQtyAt(Facility $loc)
    {
        return $this->getQty($this->total, $loc->getId());
    }

    public function getQtyAvailableAt(Facility $loc)
    {
        return $this->getQty($this->available, $loc->getId());
    }

    public function getQtyToAllocateFrom(Facility $loc)
    {
        return $this->getQty($this->toAllocate, $loc->getId());
    }

    public function getTotalQtyOnOrder()
    {
        return $this->getQty($this->total, self::ON_ORDER);
    }

    public function getQtyAvailableOnOrder()
    {
        return $this->getQty($this->available, self::ON_ORDER);
    }

    public function getQtyToAllocateFromOrders()
    {
        return $this->getQty($this->toAllocate, self::ON_ORDER);
    }

    private function getQty(array &$array, $idx)
    {
        return isset($array[$idx]) ? $array[$idx] : 0;
    }

    public function getQtyToOrder()
    {
        return $this->toOrder;
    }

    public function setQtyToOrder($toOrder)
    {
        $this->toOrder = $toOrder;
    }

    /**
     * We don't want Rialto's auto-order system to create new work orders to
     * fulfill other work orders.
     */
    public function shouldAutoOrder()
    {
        $stockItem = $this->getStockItem();
        $skipThisOne = $stockItem->isManufactured() && $stockItem->isSellable();
        return !$skipThisOne;
    }

    /**
     * @return int The number of units allocated.
     */
    public function allocate(AllocationFactory $factory)
    {
        $qtyBefore = $this->getQtyAllocated();
        if ($this->allocationConfigurations == null) {
            foreach ($this->sources as $source) {
                $factory->allocate($this, $source->toArray());
            }
        }
        foreach ($this->candidateSources as $source) {
            foreach ($source->getSources() as $basicStockSource){
                $this->allocateForSource($factory, $source, $basicStockSource);
            }
        }
        $this->refreshAllocationStatus();
        return $this->getQtyAllocated() - $qtyBefore;
    }

    private function allocateForSource(AllocationFactory $factory, SourceCollection $source, BasicStockSource $basicStockSource): void
    {
        if ($basicStockSource instanceof StockBin) {
            $this->allocateForStockBin($factory, $source, $basicStockSource);
        } elseif ($basicStockSource instanceof StockProducer) {
            $this->allocateForStockProducer($factory, $source, $basicStockSource);
        }
    }

    private function allocateForStockBin(AllocationFactory $factory, SourceCollection $source, StockBin $basicStockSource): void
    {
        // if there is user input, use user input
        if ($this->getUserSelectionSourcesIds() !== null) {
            if (in_array($basicStockSource->getId(), $this->getUserSelectionSourcesIds())) {
                $factory->allocate($this, $source->getSourcesById($basicStockSource->getId()), false);
            }
        } else {
            $factory->allocate($this, $source->toArray());
        }
    }

    private function allocateForStockProducer(AllocationFactory $factory, SourceCollection $source, StockProducer $basicStockSource): void
    {
        // if there is user input, use user input
        if ($this->getUserSelectionSourcesIds() !== null) {
            if (in_array($basicStockSource->getId(), $this->getUserSelectionSourcesIds())) {
                $factory->allocate($this, $source->getSourcesById($basicStockSource->getId()), false);
            }
        } else {
            $factory->allocate($this, $source->toArray());
        }
    }

    /** @return StockProducer|null */
    public function orderStock(StockProducerFactory $factory)
    {
        if ($this->toOrder <= 0) {
            return null;
        }
        return $factory->create($this, $this->toOrder);
    }

    public function canBeAllocated()
    {
        return $this->toOrder <= 0;
    }

    public function requiresOrder()
    {
        return $this->toOrder > 0;
    }

    public function getStockAllocationsCanBeStolen()
    {
        $this->setAllocations();
        $canBeStolen = [];
        foreach ($this->stockAllocations as $stockAllocation) {
            if ($stockAllocation->isNotFrozen())
                array_merge($canBeStolen, $stockAllocation);
        }
        return $canBeStolen;
    }

    public function setInvalidAllocationException(InvalidAllocationException $ex)
    {
        $this->errors[] = $ex->getMessage();
    }

    public function hasErrors()
    {
        return ! empty($this->errors);
    }

    public function setErrors($errors)
    {
        if ($errors instanceof ConstraintViolationListInterface) {
            $this->errors = [];
            foreach ($errors as $violation) {
                $this->errors[] = $violation->getMessage();
            }
        } else {
            $this->errors = $errors;
        }
    }

    public function appendErrors(ConstraintViolationListInterface $errors = null)
    {
        if (! $errors) {
            return;
        }
        foreach ($errors as $error) {
            $this->errors[] = $error->getMessage();
        }
    }

    /**
     * @return string[]
     *  The reasons why this part cannot be ordered; empty if this part can
     *  be ordered.
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
