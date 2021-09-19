<?php

namespace Rialto\Sales\Order\Allocation;

use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Allocation\Source\SourceCollection;
use Rialto\Allocation\Validator\ManufacturingDataExists;
use Rialto\Allocation\Validator\PurchasingDataExists;
use Rialto\Database\Orm\DbManager;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Allocates stock and presents allocation statistics for a StockConsumer.
 *
 * @see StockConsumer
 */
class SalesOrderDetailAllocator implements RequirementCollection
{
    /**
     * @var Requirement
     * @PurchasingDataExists(groups={"purchasing"})
     * @ManufacturingDataExists(groups={"purchasing"})
     */
    private $requirement;

    /** @var StockAllocation[] */
    private $allocations = [];
    private $shareBins = false;

    /** @var StockItem */
    private $item;

    /** @var SourceCollection */
    private $inStock;

    /** @var SourceCollection */
    private $onOrder;

    private $selected = false;

    public static function create(Requirement $requirement, DbManager $dbm)
    {
        $item = $requirement->getStockItem();
        if ($item->isManufactured()) {
            return new SalesOrderDetailAllocatorManufactured($requirement, $dbm);
        } else {
            return new self($requirement, $dbm);
        }
    }

    protected function __construct(Requirement $requirement, DbManager $dbm)
    {
        $this->requirement = $requirement;
        $this->item = $requirement->getStockItem();
        $this->addAllocations($requirement->getAllocations());
        $this->inStock = SourceCollection::fromAvailableBins($requirement, $requirement->getFacility(), $dbm);
        $this->onOrder = SourceCollection::fromOpenOrders($requirement, $dbm);
    }

    /**
     * @param StockAllocation[] $newAllocs
     */
    private function addAllocations(array $newAllocs)
    {
        foreach ($newAllocs as $alloc) {
            $key = $alloc->getIndexKey();
            $this->allocations[$key] = $alloc;
        }
    }

    public function getAllocations()
    {
        return $this->allocations;
    }

    public function getRequirements()
    {
        return [$this->requirement];
    }

    public function getRequirement()
    {
        return $this->requirement;
    }

    public function isShareBins()
    {
        return $this->shareBins;
    }

    public function setShareBins($share)
    {
        $this->shareBins = $share;
    }

    public function getStockItem()
    {
        return $this->item;
    }

    public function getSku()
    {
        return $this->item->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /**
     * @deprecated use getFullSku() instead.
     */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    /**
     * @return string The full SKU, including revision and customization
     *   codes; eg "GS3503F-R1234-C10085"
     */
    public function getFullSku()
    {
        return $this->requirement->getFullSku();
    }


    public function isManufactured()
    {
        return $this->item->isManufactured();
    }

    public function getVersion()
    {
        return $this->requirement->getVersion();
    }

    public function getAutoBuildVersion()
    {
        return $this->requirement->getAutoBuildVersion();
    }

    /**
     * Prevent the user from ordering inactive versions.
     *
     * @Assert\Callback(groups={"purchasing"})
     */
    public function validateVersion(ExecutionContextInterface $context)
    {
        $v = $this->getVersion();
        if (!$v->isSpecified()) {
            return;
        }
        if (!$this->item->hasVersion($v)) {
            return;
        }
        $iv = $this->item->getVersion($v);
        if (!$iv->isActive()) {
            $context->buildViolation("Version $v is not active.")
                ->atPath('version')
                ->addViolation();
        }
    }

    public function getCustomization()
    {
        return $this->requirement->getCustomization();
    }

    public function getTotalQtyOrdered()
    {
        return $this->requirement->getTotalQtyOrdered();
    }

    public function getQtyAllocated()
    {
        $total = 0;
        foreach ($this->allocations as $alloc) {
            $total += $alloc->getQtyAllocated();
        }
        return $total;
    }

    public function getQtyDelivered()
    {
        return $this->requirement->getTotalQtyDelivered();
    }

    public function getQtyAvailableFromStock()
    {
        return $this->inStock->getQtyAvailableTo($this);
    }

    public function getQtyAvailableFromOrders()
    {
        return $this->onOrder->getQtyAvailableTo($this);
    }

    public function isSelected()
    {
        return $this->selected;
    }

    public function setSelected($selected)
    {
        $this->selected = $selected;
    }

    public function deleteAllocations()
    {
        $numClosed = 0;
        foreach ($this->allocations as $alloc) {
            $alloc->close();
            $numClosed++;
        }
        return $numClosed;
    }

    /**
     * @return int
     *  The number of units allocated.
     */
    public function allocateFromStock(AllocationFactory $factory)
    {
        return $this->allocateFrom($this->inStock, $factory);
    }

    /**
     * @return int The number of units allocated.
     */
    public function allocateFromOrders(AllocationFactory $factory)
    {
        return $this->allocateFrom($this->onOrder, $factory);
    }

    private function allocateFrom(SourceCollection $source, AllocationFactory $factory)
    {
        $qtyBefore = $this->getQtyAllocated();
        $newAllocs = $factory->allocate($this, $source->toArray());
        $this->addAllocations($newAllocs);
        return $this->getQtyAllocated() - $qtyBefore;
    }

    /**
     * @return int
     *  The number of units allocated.
     */
    public function allocateFromNewOrder(
        AllocationFactory $allocFactory,
        StockProducer $producer)
    {
        if ($this->getQtyNeeded() <= 0) {
            return 0;
        }
        $qtyBefore = $this->getQtyAllocated();
        $newAllocs = $allocFactory->allocate($this, [$producer]);
        $this->addAllocations($newAllocs);
        return $this->getQtyAllocated() - $qtyBefore;
    }

    private function getQtyNeeded()
    {
        return $this->requirement->getTotalQtyUndelivered() - $this->getQtyAllocated();
    }

    /**
     * @return Facility The location where stock is required.
     */
    public function getFacility()
    {
        return $this->requirement->getFacility();
    }

    /**
     * @deprecated use getFacility() instead
     */
    public function getLocation()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFacility();
    }
}
