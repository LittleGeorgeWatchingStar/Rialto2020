<?php

namespace Rialto\Allocation\Requirement;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Stock\Bin\StockBin;

/**
 * For manually allocating to a requirement.
 */
class ManualAllocator extends SingleRequirementCollection
{
    /**
     * Bins that can potentially provide stock to this allocator.
     *
     * @var StockBin[]
     */
    private $bins;

    /**
     * StockProducers that can potentially provide stock to this allocator.
     *
     * @var StockProducer[]
     */
    private $producers;

    /**
     * @var bool True if we should actively steal allocations from other
     * requirements.
     */
    private $stealAllocations = false;

    public function __construct(Requirement $req)
    {
        parent::__construct($req);
        $this->bins = new ArrayCollection();
        $this->producers = new ArrayCollection();
        $this->populateSources();
    }

    private function populateSources()
    {
        foreach ($this->getAllocations() as $alloc) {
            if ($alloc->isFromStock()) {
                $this->bins[] = $alloc->getSource();
            } else {
                $this->producers[] = $alloc->getSource();
            }
        }
    }

    private function getAllocations()
    {
        return $this->getRequirement()->getAllocations();
    }

    /** @return string The class name of the type of stock producer required. */
    public function getProducerClass()
    {
        return $this->getRequirement()->isManufactured() ?
            WorkOrder::class :
            PurchaseOrderItem::class;
    }

    /**
     * @return StockBin[]
     */
    public function getBins()
    {
        return $this->bins;
    }

    public function addBin(StockBin $bin)
    {
        $this->bins[] = $bin;
    }

    public function removeBin(StockBin $bin)
    {
        $this->bins->removeElement($bin);
    }

    /**
     * @return StockProducer[]
     */
    public function getProducers()
    {
        return $this->producers;
    }

    public function addProducer(StockProducer $producer)
    {
        $this->producers[] = $producer;
    }

    public function removeProducer(StockProducer $producer)
    {
        $this->producers->removeElement($producer);
    }

    /** @return BasicStockSource[] */
    private function getSelectedSources()
    {
        return array_merge(
            $this->bins->getValues(),
            $this->producers->getValues());
    }

    /**
     * @return boolean
     */
    public function isStealAllocations()
    {
        return $this->stealAllocations;
    }

    /**
     * @param boolean $steal
     */
    public function setStealAllocations($steal)
    {
        $this->stealAllocations = $steal;
    }

    /**
     * @return StockAllocation[]
     */
    public function allocate(AllocationFactory $factory)
    {
        $selected = $this->getSelectedSources();
        foreach ($this->getAllocations() as $alloc) {
            if (! in_array($alloc->getSource(), $selected, true)) {
                $alloc->close();
            }
        }
        if ($this->stealAllocations) {
            $this->closeCompetitors();
        }
        return $factory->allocate($this, $selected);
    }

    private function closeCompetitors()
    {
        $remaining = $this->getTotalQtyUnallocated();
        foreach ($this->getSelectedSources() as $source) {
            foreach ($source->getAllocations() as $alloc) {
                if ($remaining <= 0) {
                    return;
                }
                if ($alloc->getRequirement() !== $this->getRequirement()) {
                    $remaining += $alloc->adjustQuantity(-$remaining);
                }
            }
        }
    }

    private function getTotalQtyUnallocated()
    {
        $total = 0;
        foreach ($this->getRequirements() as $req) {
            $total += $req->getTotalQtyUnallocated();
        }
        return $total;
    }

}
