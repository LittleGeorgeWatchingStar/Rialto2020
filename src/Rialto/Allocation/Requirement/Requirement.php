<?php

namespace Rialto\Allocation\Requirement;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Allocation\Status\RequirementStatus;
use Rialto\Entity\RialtoEntity;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\Version\Version;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An item that is required for a StockConsumer.
 *
 * @see StockConsumer
 */
abstract class Requirement implements RialtoEntity, RequirementInterface
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var PhysicalStockItem
     * @Assert\NotNull
     */
    protected $stockItem;

    /** @var string */
    protected $version = Version::NONE;

    /** @var Customization */
    private $customization = null;

    /**
     * @var int
     * @Assert\Type(type="numeric")
     */
    private $unitQtyNeeded;

    /**
     * @var StockAllocation[]
     */
    private $allocations;


    protected function __construct(PhysicalStockItem $item)
    {
        $this->stockItem = $item;
        $this->allocations = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return StockConsumer
     */
    public abstract function getConsumer();

    public abstract function getConsumerType();

    public function isConsumerType($type)
    {
        return $this->getConsumerType() == $type;
    }

    public abstract function getConsumerDescription();

    public function __toString()
    {
        return sprintf('%s x %s for %s',
            number_format($this->unitQtyNeeded),
            $this->getSku(),
            $this->getConsumerDescription());
    }

    /** @return StockAllocation[] */
    public function getAllocations()
    {
        return $this->allocations->toArray();
    }

    public function getAllocation(BasicStockSource $source)
    {
        foreach ( $this->allocations as $alloc ) {
            if ( $source->equals($alloc->getSource()) ) {
                return $alloc;
            }
        }
        return null;
    }

    /** @return RequirementStatus */
    public function getAllocationStatus()
    {
        $status = new RequirementStatus($this->getFacility());
        $status->addRequirement($this);
        return $status;
    }

    public function isCompatibleWith(BasicStockSource $source)
    {
        return
            ($source->getSku() == $this->getSku()) &&
            ($source->getVersion()->matches($this->getVersion()));
    }

    /**
     * @return StockAllocation
     */
    public function createAllocation(BasicStockSource $source)
    {
        $alloc = $this->getAllocation($source);
        if (! $alloc ) {
            $alloc = $source->createAllocation($this);
            $this->allocations[] = $alloc;
        }
        return $alloc;
    }

    public function removeAllocation(StockAllocation $alloc)
    {
        $this->allocations->removeElement($alloc);
    }

    public function closeAllocations()
    {
        foreach ( $this->allocations as $alloc ) {
            $alloc->close();
        }
    }

    /**
     * StockAllocation calls this method whenever the allocations for this
     * requirement change.
     *
     * Subclasses can override to implement specific logic.
     */
    public function setUpdated()
    {
        /* override */
    }

    /** @return PhysicalStockItem */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function isCategory($category): bool
    {
        return $this->stockItem->isCategory($category);
    }

    /**
     * @return string The stock code of the required component.
     */
    public function getSku()
    {
        return $this->stockItem->getSku();
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @return Version */
    public function getVersion()
    {
        return new Version($this->version);
    }

    public function getAutoBuildVersion()
    {
        return new Version($this->stockItem->getAutoBuildVersion());
    }

    /**
     * @return Customization|null
     */
    public function getCustomization()
    {
        return $this->customization;
    }

    public function setCustomization(Customization $customization = null)
    {
        $this->customization = $customization;
    }

    public function isManufactured()
    {
        return $this->stockItem->isManufactured();
    }

    /** @deprecated use getFullSku() instead. */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    public function getFullSku()
    {
        $code = $this->getSku();
        $version = $this->getVersion();
        $code .= $version->getStockCodeSuffix();
        $code .= Customization::getStockCodeSuffix($this->customization);
        return $code;
    }

    public function getIndexKey()
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $this->getFullSku());
    }

    /**
     * The number of units of this item needed per unit of the
     * consumer.
     */
    public function getUnitQtyNeeded()
    {
        return (int) $this->unitQtyNeeded;
    }

    public function setUnitQtyNeeded($quantity)
    {
        $this->unitQtyNeeded = $quantity;
    }

    /**
     * Returns the total number of units required to fulfill the parent order's
     * need for this stock item.
     *
     * @return integer
     * @Assert\Range(min=1, minMessage="Quantity must be at least {{ limit }}.")
     */
    public abstract function getTotalQtyOrdered();

    /**
     * Returns the total number of units required to fill what is left of
     * the parent order's need for this item.  This quantity is the total
     * amount required minus the total amount delivered.
     *
     * @return integer
     */
    public abstract function getTotalQtyUndelivered();

    public function getTotalQtyDelivered()
    {
        return $this->getTotalQtyOrdered() - $this->getTotalQtyUndelivered();
    }

    public function getTotalQtyUnallocated()
    {
        return $this->getTotalQtyUndelivered() - $this->getTotalQtyAllocated();
    }

    public function getTotalQtyAllocated()
    {
        $total = 0;
        foreach ( $this->allocations as $alloc ) {
            $total += $alloc->getQtyAllocated();
        }
        return $total;
    }

    /**
     * The facility at which the stock is needed.
     *
     * @return Facility
     */
    public function getFacility()
    {
        return $this->getConsumer()->getLocation();
    }

    /**
     * @deprecated use getFacility() instead
     */
    public function getLocation()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFacility();
    }

    public function isNeededAt(Facility $location)
    {
        return $location->equals($this->getFacility());
    }

    /**
     * @param StockAllocation $alloc
     * @return boolean True if $alloc is for the same order as this
     *   requirement.
     */
    public function isForSameOrder(StockAllocation $alloc)
    {
        $other = $alloc->getRequirement();
        return $this->getConsumer()->isForSameOrder($other->getConsumer());
    }
}
