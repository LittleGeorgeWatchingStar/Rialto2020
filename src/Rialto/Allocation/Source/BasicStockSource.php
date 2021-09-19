<?php

namespace Rialto\Allocation\Source;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Manufacturer\Manufacturer;

/**
 * A stock source is a bin or order from which stock can be allocated.
 */
abstract class BasicStockSource implements StockSource, RialtoEntity
{
    /** @var StockAllocation[] */
    protected $allocations;

    /** @var bool */
    private $canBeAllocated = false;

    /** @var bool */
    private $useThis = true;

    protected function __construct()
    {
        $this->allocations = new ArrayCollection();
    }

    public function __clone()
    {
        $this->allocations = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function getCanBeAllocated()
    {
        return $this->canBeAllocated;
    }

    /**
     * @param bool $newCanBeAllocated
     */
    public function setCanBeAllocated(bool $newCanBeAllocated)
    {
        $this->canBeAllocated = $newCanBeAllocated;
    }

    /**
     * @return bool
     */
    public function getUseThis()
    {
        return $this->useThis;
    }

    /**
     * @param bool $newUseThis
     */
    public function setUseThis(bool $newUseThis)
    {
        $this->useThis = $newUseThis;
    }


    /** @return StockAllocation[] */
    public function getAllocations()
    {
        return $this->allocations->toArray();
    }

    public function getAllocationsActiveAtFacility()
    {
        return $this->allocations->filter(function (StockAllocation $alloc) {
            return !$alloc->isForMissingStock() && $alloc->getConsumer();
        })->toArray();
    }

    /**
     * This method should only be called by Requirement.
     *
     * @return StockAllocation
     */
    public function createAllocation(Requirement $request)
    {
        $alloc = $this->instantiateAllocation($request);
        $this->allocations[] = $alloc;

        return $alloc;
    }

    protected abstract function instantiateAllocation(Requirement $requirement);

    public function removeAllocation(StockAllocation $alloc)
    {
        $this->allocations->removeElement($alloc);
    }

    /**
     * If the amount of stock at this source is lowered, we might end up
     * with a situation where more are allocated than are remaining. This
     * method corrects that situation.
     */
    public function adjustAllocationsToMatchQtyRemaining()
    {
        $toAdjust = $this->getQtyUnallocated();
        foreach ( $this->allocations as $alloc ) {
            if ( $toAdjust >= 0 ) {
                return;
            }
            $toAdjust -= $alloc->adjustQuantity($toAdjust);
        }
        assertion($toAdjust >= 0, "alloc qty left to adjust: $toAdjust");
    }

    /**
     * Cancels any allocations for which this is the source.
     */
    protected function cancelAllocations()
    {
        foreach ( $this->allocations as $alloc ) {
            $alloc->close();
        }
    }

    public function getQtyUnallocated()
    {
        return $this->getQtyRemaining() - $this->getNetQtyAllocated();
    }

    public function getNetQtyAllocated()
    {
        $total = 0;
        foreach ( $this->allocations as $alloc ) {
            $total += $alloc->getNetQtyAllocated();
        }
        return $total;
    }

    public abstract function getSourceNumber();

    public abstract function getSourceType();

    public function equals(BasicStockSource $other = null)
    {
        if (null === $other) {
            return false;
        }
        assertion($this->getSourceNumber());
        return ( $this->getSourceType() == $other->getSourceType() ) &&
            ( $this->getSourceNumber() == $other->getSourceNumber() ) &&
            ( $this->getSku() == $other->getSku() );
    }

    public abstract function getSourceDescription();

    public function isCompatibleWith(RequirementCollection $collection)
    {
        $checker = new CompatibilityChecker();
        return $checker->areCompatible($this, $collection);
    }

    /** @return Manufacturer|null */
    public abstract function getManufacturer();

    /** @return string */
    public abstract function getManufacturerCode();
}
