<?php

namespace Rialto\Manufacturing\Allocation;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Allocation\InvalidAllocationException;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Requirement\ConsolidatedRequirement;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Source\SourceCollection;
use Rialto\Allocation\Status\AllocationStatus;
use Rialto\Allocation\Status\RequirementStatus;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Requirement\Validator\VersionIsActive;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Producer\StockProducerFactory;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Controls stock allocation for a work order requirement.
 *
 * @VersionIsActive
 */
class RequirementAllocatorNormal extends ConsolidatedRequirement
{
    const ON_ORDER = 'order';

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

    /** @var SourceCollection[] */
    private $sources = [];
    private $total = [];
    private $available = [];
    private $toAllocate = [];
    private $toOrder = 0;
    /** @var StockAllocation[] */
    private $stockAllocations = [];
    private $errors = [];

    /** @var AllocationConfiguration[] */
    private $allocationConfigurations = null;

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
    private function addSource($indexKey, SourceCollection $source): int
    {
        $this->sources[$indexKey] = $source;

        $this->total[$indexKey] = $source->getQtyRemaining();
        $qtyAvailable = $source->getQtyAvailableTo($this);
        $this->available[$indexKey] = $qtyAvailable;

        $qtyToAllocate = min($this->toOrder, $qtyAvailable);
        $this->toAllocate[$indexKey] = $qtyToAllocate;
        return $qtyToAllocate;
    }

    public function loadPurchasingData(ObjectManager $dbm)
    {
        /** @var $repo PurchasingDataRepository */
        $repo = $dbm->getRepository(PurchasingData::class);
        $this->purchData = $repo->findPreferredForRequirement($this);
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
        foreach ($this->sources as $source) {
            $factory->allocate($this, $source->toArray());
        }
        $this->refreshAllocationStatus();
        return $this->getQtyAllocated() - $qtyBefore;
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
