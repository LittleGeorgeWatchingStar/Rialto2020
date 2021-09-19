<?php

namespace Rialto\Manufacturing\Allocation;

use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Allocation\InvalidAllocationException;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkOrder\WorkOrderCollection;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controls part allocation for a work order.
 */
class WorkOrderAllocator
{
    /** @var WorkOrderCollection */
    private $workOrders;

    /** @var RequirementAllocator[] */
    private $items = [];

    /** @var int[]|null */
    private $userSelectionSourcesIds;

    /**
     * The manufacturing location. Null if this is an order for purchased
     * parts rather than manufactured items.
     *
     * @var Facility|null
     */
    private $buildLocation = null;
    private $locations = [];
    private $shareBins = false;

    /** @var AllocationConfiguration[]*/
    private $allocationConfigurations = null;

    /** @var DbManager */
    private $dbm;

    public function __construct(WorkOrderCollection $list)
    {
        $this->workOrders = $list;
    }

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

    public function addLocation(Facility $location)
    {
        $this->locations[] = $location;
    }

    public function getLocations()
    {
        return $this->locations;
    }

    public function setShareBins($share)
    {
        $this->shareBins = $share;
    }

    public function createItems(DbManager $dbm)
    {
        $this->setDbm($dbm);
        $this->items = [];
        foreach ($this->workOrders as $wo) {
            $this->createItemsForOrder($wo, $dbm);
        }
    }

    private function createItemsForOrder(WorkOrder $wo, DbManager $dbm)
    {
        foreach ($wo->getRequirements() as $woReq) {
            if ($woReq->isProvidedByChild()) {
                continue;
            }
            $sku = $woReq->getFullSku();
            if (empty($this->items[$sku])) {
                $a = new RequirementAllocator();
                if ($this->getUserSelectionSourcesIds() !== null) {
                    $a->setUserSelectionSourcesIds($this->getUserSelectionSourcesIds());
                }
                $this->items[$sku] = $a;
            }
            $allocator = $this->items[$sku];
            $allocator->addRequirement($woReq);
        }
        foreach ($this->items as $allocator) {
            try {
                $allocator->setWorkOrderLocations($this->locations);
                $allocator->setLocations($this->locations, $dbm);
                $allocator->setDbm($dbm);
                if ($this->getBuildLocation() !== null) {
                    $allocator->setBuildLocation($this->getBuildLocation());
                }
                if ($this->allocationConfigurations !== null) {
                    $allocator->setAllocationConfigurations($this->allocationConfigurations);
                    $allocator->setCandidateSources($dbm);
                }
                if ($this->getUserSelectionSourcesIds() !== null) {
                        $this->setShareBins(true);
                }
                $allocator->setShareBins($this->shareBins);
            } catch (InvalidAllocationException $ex) {
                $allocator->setInvalidAllocationException($ex);
            }
        }
    }

    /** @return WorkOrderCollection */
    public function getWorkOrders()
    {
        return $this->workOrders;
    }

    /** @return RequirementAllocator[] */
    public function getItems()
    {
        foreach ($this->items as $requirementAllocator) {
            if ($this->allocationConfigurations !== null) {
            $requirementAllocator->setAllocationConfigurations($this->allocationConfigurations);
            $requirementAllocator->setCandidateSources($this->getDbm());
            }
        }
        return $this->items;
    }

    public function loadPurchasingData(DbManager $dbm)
    {
        foreach ($this->items as $allocator) {
            $allocator->loadPurchasingData($dbm);
            $allocator->setQtyToOrder($allocator->getQtyStillNeeded());
        }
    }

    public function validate(ValidatorInterface $validator)
    {
        foreach ($this->items as $allocator) {
            $groups = ['Default'];
            if (! $allocator->canBeAllocated()) {
                $groups[] = 'strictBins';
            }
            $errors = $validator->validate($allocator, null, $groups);
            $allocator->appendErrors($errors);
        }
    }

    /**
     * @return int
     *  The total number of pieces allocated.
     */
    public function allocate(AllocationFactory $factory)
    {
        $qtyAllocated = 0;
        foreach ($this->items as $allocator) {
            $qtyAllocated += $allocator->allocate($factory);
        }
        return $qtyAllocated;
    }
}

