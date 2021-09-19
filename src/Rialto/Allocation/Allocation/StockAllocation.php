<?php

namespace Rialto\Allocation\Allocation;

use InvalidArgumentException;
use Rialto\Allocation\AllocationEvents;
use Rialto\Allocation\AllocationInterface;
use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Allocation\Source\CompatibilityChecker;
use Rialto\Allocation\Source\StockSource;
use Rialto\Entity\HasDomainEvents;
use Rialto\Entity\RialtoEntity;
use Rialto\Manufacturing\Requirement\MissingStockRequirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Transfer\Transfer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A stock allocation reserves stock from a StockSource (such
 * as a StockBin or location) for a Requirement.
 *
 * @see StockSource
 * @see Requirement
 */
abstract class StockAllocation implements
    RialtoEntity,
    HasDomainEvents,
    AllocationInterface
{
    private $id;

    /**
     * @var Requirement
     * @Assert\NotNull
     */
    private $requirement;

    /** @var StockItem */
    private $stockItem;

    /**
     * @Assert\Range(min = 0)
     */
    private $qtyAllocated;

    /** @var StockAllocationEvent[] */
    private $events = [];

    protected $frozen = false;

    protected function __construct(Requirement $requirement, StockItem $item)
    {
        $this->requirement = $requirement;
        $this->stockItem = $item;
    }

    public function isFrozen()
    {
        return $this->frozen;
    }

    public function isNotFrozen()
    {
        return !$this->frozen;
    }

    public function setFrozen(bool $frozen)
    {
        $this->frozen = $frozen;
    }

    /**
     * This method should only be called by the StockConsumption class.
     */
    public function addQtyDelivered($qty)
    {
        $this->addQuantity(-$qty);
    }

    /**
     * @return int
     *  The quantity closed.
     */
    public function close()
    {
        $diff = $this->qtyAllocated;
        assertion($diff >= 0, "diff is $diff");
        $this->addQuantity(-$diff);
        return $diff;
    }

    public function getRequirement()
    {
        return $this->requirement;
    }

    /**
     * Fetches the consumer which requested this allocation.
     *
     * @return StockConsumer
     */
    public function getConsumer()
    {
        return $this->requirement->getConsumer();
    }

    /**
     * A human-readable description of the consumer of this allocation.
     * @return string
     */
    public function getConsumerDescription()
    {
        return $this->requirement->getConsumerDescription();
    }

    /**
     * The net quantity allocated and the order to which it
     * is allocated.
     *
     * @return string eg: "1,234 for purchase order 7890"
     */
    public function getOrderDescriptionWithQuantity()
    {
        return sprintf('%s for %s',
            number_format($this->getQtyAllocated()),
            $this->getConsumerDescription());
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     * A string that uniquely identified this allocation, regardless of whether
     * it has a database ID or not.
     */
    public function getIndexKey()
    {
        return join('_', [
            $this->requirement->getId(),
            $this->requirement->getConsumerType(),
            $this->getSource()->getSourceNumber(),
            $this->getSource()->getSourceType(),
        ]);
    }

    public function getQtyRequired()
    {
        return $this->requirement->getTotalQtyOrdered();
    }

    public function getQtyAllocated()
    {
        return (int) $this->qtyAllocated;
    }

    /**
     * Returns the total quantity allocated, including the quantity delivered.
     *
     * @deprecated
     * @return int
     */
    public function getGrossQtyAllocated()
    {
        return $this->getQtyAllocated() + $this->getQtyDelivered();
    }


    /**
     * @deprecated
     * @return int
     */
    public function getQtyDelivered()
    {
        return $this->requirement->getTotalQtyDelivered();
    }

    /**
     * Returns the undelivered quantity on this allocation.
     *
     * @deprecated
     * @return int
     */
    public function getNetQtyAllocated()
    {
        return $this->getQtyAllocated();
    }

    public abstract function getSource(): BasicStockSource;

    public function getSku()
    {
        return $this->stockItem->getSku();
    }

    /**
     * @deprecated use getSku() instead.
     */
    public function getStockCode()
    {
        return $this->getSku();
    }

    public function getFullSku(): string
    {
        return $this->getSource()->getFullSku();
    }

    public function isCompatibleWith(RequirementCollection $requirements): bool
    {
        return $this->getSource()->isCompatibleWith($requirements);
    }

    /**
     * @return StockItem
     */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function isVersioned()
    {
        return $this->getStockItem()->isVersioned();
    }

    /**
     * Returns true if this allocation is at the given stock facility.
     * Contrast with @see isNeededAtLocation().
     *
     * @return bool
     */
    public function isAtLocation(Facility $facility)
    {
        return $facility->equals($this->getFacility());
    }

    /**
     * @return bool True if this allocation is from stock either at $loc
     *   or at a sublocation thereof.
     */
    public function isAtSublocationOf(Facility $loc)
    {
        $fac = $this->getFacility();
        return $fac && $fac->isSublocationOf($loc);
    }

    /**
     * @return Facility|null
     */
    private function getFacility()
    {
        if ($this->isFromStock()) {
            $bin = $this->getSource();
            /* @var $bin StockBin */
            return $bin->isInTransit() ? null : $bin->getFacility();
        }
        return null;
    }


    /** @return boolean */
    public function isInTransit()
    {
        if ($this->isFromStock()) {
            /** @var $bin StockBin */
            $bin = $this->getSource();
            return $bin->isInTransit();
        }
        return false;
    }

    /** @return boolean */
    public function isInTransitTo(Facility $facility)
    {
        if ($this->isInTransit()) {
            /** @var $bin StockBin */
            $bin = $this->getSource();

            /** @var $transfer Transfer */
            $transfer = $bin->getLocation();
            return $transfer->isDestinedFor($facility);
        }
        return false;
    }

    /**
     * @return Facility the location where the stock is needed
     */
    public function getLocationWhereNeeded(): Facility
    {
        return $this->getConsumer()->getLocation();
    }

    /**
     * Returns true if this allocation is needed at the given location.
     * Contrast with @see isAtLocation().
     *
     * @param Facility $facility
     * @return boolean
     */
    public function isNeededAtLocation(Facility $facility)
    {
        $consumer = $this->getConsumer();
        return $facility->equals($consumer->getLocation());
    }

    /**
     * Returns true if this allocation is from the location
     * where the consumer needs it to be.
     */
    public function isWhereNeeded(): bool
    {
        $fac = $this->getFacility();
        return $fac && $this->isNeededAtLocation($fac);
    }

    public function getShelfPosition()
    {
        $src = $this->getSource();
        return ($src instanceof StockBin)
            ? $src->getShelfPosition()
            : '';
    }

    public function isDelivered()
    {
        return $this->qtyAllocated == 0;
    }

    public function isForMissingStock()
    {
        return $this->requirement->isConsumerType(
            MissingStockRequirement::CONSUMER_TYPE);
    }

    public function isMissingFrom(Facility $location)
    {
        return $this->isForMissingStock()
            && $this->requirement->isNeededAt($location);
    }

    /**
     *  If this allocation is from the given stock source.
     */
    public function isFromSource(BasicStockSource $source): bool
    {
        return $source->equals($this->getSource());
    }

    public function isFromStock(): bool
    {
        return $this instanceof BinAllocation;
    }

    /**
     * @deprecated Use isFromStock instead.
     */
    public function isFromStockBin()
    {
        return $this->isFromStock();
    }

    public function isFromPurchaseOrder()
    {
        return $this->isFromProducer();
    }

    public function isFromProducer()
    {
        return $this instanceof ProducerAllocation;
    }

    /**
     * True if this item is being shipped directly to $location.
     * @return bool
     */
    public function isOnOrderTo(Facility $location)
    {
        if ($this->isFromProducer()) {
            $source = $this->getSource();
            /* @var $source StockProducer */
            $po = $source->getPurchaseOrder();
            return $po && $location->equals($po->getDeliveryLocation());
        }
        return false;
    }

    public function isFromWorkOrder()
    {
        return $this->getSource() instanceof WorkOrder;
    }

    /** @return bool */
    public function isFromWorkOrderAtLocation(Facility $location)
    {
        return $this->isFromWorkOrder() &&
            $this->getSource()->isLocation($location);
    }

    /**
     * Changes the amount allocated by up to $target. The adjustment may be
     * of a smaller magnitude if $target is outside the bounds of what is
     * valid.
     *
     * Contrast this with @see addQuantity(), which throws an exception
     * if the adjustment amount is not valid.
     *
     * @param int $target The target adjustment we'd like to make, either
     *   positive or negative.
     *
     * @return int The actual adjustment amount.
     */
    public function adjustQuantity($target)
    {
        /* Can't allocate more than the source has available. */
        $avail = $this->getSource()->getQtyUnallocated();

        /* Can't go below zero. */
        $min = -$this->qtyAllocated; // <= 0
        $actual = min($target, $avail);
        $actual = max($actual, $min);
        assertion($actual >= $min);
        if ($actual == 0) {
            return 0;
        }
        $this->addQuantity($actual);
        return $actual;
    }

    /**
     * Adjusts the amount allocated by $diff; $diff can be positive or negative.
     *
     * @param integer $diff
     * @throws InvalidArgumentException
     *  If $diff is outside the bounds of a valid allocation.
     */
    public function addQuantity($diff): self
    {
        $error = $this->validateQtyAdjustment($diff);
        if ($error) {
            throw new InvalidArgumentException($error);
        }

        $this->qtyAllocated += $diff;
        assertion($this->qtyAllocated >= 0);
        $this->setUpdated();
        $this->events[] = new StockAllocationEvent(
            $this,
            AllocationEvents::ALLOCATION_CHANGE);
        return $this;
    }

    public function setUpdated()
    {
        $this->requirement->setUpdated();
    }

    private function validateQtyAdjustment($diff)
    {
        $newQty = $this->qtyAllocated + $diff;
        if ($newQty < 0) {
            return $this->formatError($diff, sprintf(
                'only %s remain allocated',
                number_format($this->qtyAllocated)
            ));
        }

        $available = $this->getSource()->getQtyUnallocated();
        if (($diff > 0) && ($available - $diff < 0)) {
            return $this->formatError($diff, sprintf(
                'only %s remain at source',
                number_format($available)));
        }
        return null;
    }

    private function formatError($diff, $error)
    {
        return sprintf(
            'Cannot adjust allocation %s amount by %s: %s',
            $this->id,
            number_format($diff),
            $error
        );
    }

    public function getSourceDescription()
    {
        return $this->getSource()->getSourceDescription();
    }

    public function popEvents()
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }

    /**
     * @Assert\Callback
     */
    public function validateQuantity(ExecutionContextInterface $context)
    {
        if ($this->getSource()->getQtyRemaining() < $this->getQtyAllocated()) {
            $context->addViolation('Over-allocated, source does not have enough remaining');
        }

        if ($this->getSource()->getQtyUnallocated() < 0) {
            $context->addViolation('Stock source is over-allocated');
        }
    }

    /**
     * This validator is in the "thorough" group because we can't run it
     * when issuing a work order. Work order requirement allocations become
     * temporarily invalid during the issuing process.
     *
     * @Assert\Callback(groups={"thorough"})
     */
    public function validateRequirementQuantity(ExecutionContextInterface $context)
    {
        if ($this->requirement->getTotalQtyUndelivered() < $this->getQtyAllocated()) {
            $context->addViolation('Allocated more than required.');
        }
    }

    /**
     * @Assert\Callback
     */
    public function validateVersion(ExecutionContextInterface $context)
    {
        if ($this->isVersioned()) {
            $checker = new CompatibilityChecker();
            if (! $checker->areCompatible($this->getSource(), $this->requirement)) {
                $context->addViolation('Version or customization mismatch');
            }
        }
    }
}
