<?php

namespace Rialto\Manufacturing\WorkOrder;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Allocation\InvalidAllocationException;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Allocation\RequirementAllocator;
use Rialto\Manufacturing\Allocation\RequirementAllocatorNormal;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * For manually allocating to a requirement.
 */
class WorkOrderAllocatorForChooseStock
{
    /** @var WorkOrderCollection */
    private $workOrders;

    /** @var RequirementAllocator[] */
    private $items = [];
    private $locations = [];
    private $shareBins = false;

    public function __construct(WorkOrderCollection $list)
    {
        $this->workOrders = $list;
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
                $a->setShareBins($this->shareBins);
                $this->items[$sku] = $a;
            }
            $allocator = $this->items[$sku];
            $allocator->addRequirement($woReq);
        }
        foreach ($this->items as $allocator) {
            try {
                $allocator->setLocations($this->locations, $dbm);
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
        return $this->items;
    }

    public function loadPurchasingData(DbManager $dbm)
    {
        foreach ($this->items as $allocator) {
            $allocator->loadPurchasingData($dbm);
        }
    }

    public function validate(ValidatorInterface $validator)
    {
        foreach ($this->items as $allocator) {
            $groups = ['Default'];
            if (! $allocator->canBeAllocated()) {
                $groups[] = 'purchasing';
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
