<?php

namespace Rialto\Manufacturing\Requirement;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Allocation\Requirement\Requirement as RequirementAbstract;
use Rialto\IllegalStateException;
use Rialto\Manufacturing\Bom\Bom;
use Rialto\Manufacturing\Component\Component;
use Rialto\Manufacturing\Component\Designators;
use Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssueItem;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Measurement\Temperature\TemperatureRange;
use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\Version\Version;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * Represents a component that is required for a work order, and the
 * quantity required.
 */
class Requirement extends RequirementAbstract implements Component
{
    const CONSUMER_TYPE = 'WorkOrder';

    /**
     * @var WorkOrder
     */
    private $workOrder;

    /** @var WorkType */
    private $workType;

    /**
     * @var int
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    private $scrapCount = 0;

    /**
     * @var string[]
     * @Assert\Count(max=10000)
     */
    private $designators = [];

    public function __construct(
        WorkOrder $workOrder,
        PhysicalStockItem $component,
        $unitQty,
        WorkType $workType)
    {
        parent::__construct($component);
        $this->workOrder = $workOrder;
        $this->workType = $workType;
        $this->setUnitQty($unitQty);
        $this->setVersionOrDefault($component->getAutoBuildVersion());
    }

    public function getAutoBuildVersion(): Version
    {
        return $this->stockItem->getAutoBuildVersion();
    }

    public function getConsumer()
    {
        return $this->workOrder;
    }

    public function getConsumerDescription()
    {
        return sprintf('WO%s for PO%s',
            $this->workOrder->getId(),
            $this->workOrder->getOrderNumber());
    }

    /**
     * @return string
     */
    public function getConsumerType()
    {
        return self::CONSUMER_TYPE;
    }

    public function isControlled()
    {
        return $this->stockItem->isControlled();
    }

    public function isPCB()
    {
        return $this->stockItem->isPCB();
    }

    public function isBoard()
    {
        return $this->stockItem->isBoard();
    }

    public function isProduct()
    {
        return $this->stockItem->isProduct();
    }

    public function getBom(): Bom
    {
        if (!$this->stockItem->hasSubcomponents()) {
            throw new IllegalStateException("{$this->stockItem} has no BOM");
        }
        return $this->stockItem->getBom($this->getVersion());
    }

    public function getDescription()
    {
        return $this->stockItem->getName();
    }

    /** @return GLAccount */
    public function getStockAccount()
    {
        return $this->stockItem->getStockAccount();
    }

    /**
     * @return WorkOrder
     *         The work order to which this requirement belongs.
     */
    public function getWorkOrder()
    {
        return $this->workOrder;
    }

    public function isCustomizable()
    {
        return $this->stockItem->isCustomizable();
    }

    public function getIndexKey()
    {
        return $this->stockItem->getSku();
    }

    /**
     * @deprecated see getOrder()
     * @return int
     */
    public function getOrderNumber()
    {
        return $this->workOrder->getId();
    }

    public function getOrder()
    {
        return $this->getWorkOrder();
    }

    /**
     * @return string
     *  The package or footprint of this component.
     */
    public function getPackage()
    {
        return $this->stockItem->getPackage();
    }

    public function getPartValue()
    {
        return $this->stockItem->getPartValue();
    }

    /** @return TemperatureRange */
    public function getTemperatureRange()
    {
        return $this->stockItem->getTemperatureRange();
    }

    /**
     * @return string[]
     *  The reference designators for this component.
     */
    public function getDesignators()
    {
        return $this->designators;
    }

    public function setDesignators(array $designators)
    {
        $this->designators = Designators::normalize($designators);
    }

    /**
     * @Assert\Callback
     */
    public function validateDesignators(ExecutionContextInterface $context)
    {
        $error = $this->getDesignatorCountError();
        if ($error) {
            $context->buildViolation($error)
                ->atPath('designators')
                ->addViolation();
        }
    }

    private function getDesignatorCountError()
    {
        $numDes = count($this->getDesignators());
        $unitQty = $this->getUnitQtyNeeded();
        if ($numDes > 0 && ($numDes != $unitQty)) {
            $sku = $this->getFullSku();
            return "Number of designators ($numDes) for $sku does not match unit quantity ($unitQty).";
        }
        return null; // no error
    }

    /**
     * The manufacturer who made this required part, if known.
     * @return Manufacturer|null
     */
    public function getPartManufacturer()
    {
        foreach ($this->getAllocations() as $alloc) {
            $manufacturer = $alloc->getSource()->getManufacturer();
            if ($manufacturer) {
                return $manufacturer;
            }
        }
        return null;
    }

    /**
     * The manufacturer part number of this required part, if known.
     * @return string
     */
    public function getManufacturerCode()
    {
        foreach ($this->getAllocations() as $alloc) {
            $code = $alloc->getSource()->getManufacturerCode();
            if ($code) {
                return $code;
            }
        }
        $preferredPurchasingData = $this->getStockItem()->getPreferredPurchasingData();
        if ($preferredPurchasingData) {
            return $preferredPurchasingData->getManufacturerCode();
        }
        return '';
    }

    public function getTotalQtyIssued()
    {
        $total = 0;
        foreach ($this->getIssueItems() as $ii) {
            $total += $ii->getTotalQtyIssued();
        }
        return $total;
    }

    /** @return WorkOrderIssueItem[] */
    private function getIssueItems()
    {
        $items = [];
        foreach ($this->workOrder->getIssues() as $issue) {
            $ii = $issue->getIssueItem($this);
            if ($ii) {
                $items[] = $ii;
            }
        }
        return $items;
    }

    public function getTotalQtyUndelivered()
    {
        if ($this->workOrder->isClosed()) {
            return 0;
        }
        return $this->getTotalQtyOrdered() - $this->getTotalQtyIssued();
    }

    /**
     * @return int
     */
    public function getTotalQtyOrdered()
    {
        return ($this->getUnitQty() * $this->workOrder->getQtyOrdered())
        + $this->scrapCount;
    }

    /**
     * @return int
     *  The number of pieces of this component required to build
     *  one unit of the parent work order's item.
     */
    public function getUnitQty()
    {
        return $this->getUnitQtyNeeded();
    }

    public function setUnitQty($quantity)
    {
        $this->setUnitQtyNeeded($quantity);
    }

    public function setUnitQtyNeeded($quantity)
    {
        parent::setUnitQtyNeeded($quantity);
        $this->setDirty(WorkOrder::DIRTY_ALLOCATIONS);
    }

    public function addUnitQty($diff)
    {
        $this->setUnitQtyNeeded($this->getUnitQty() + $diff);
    }

    private function setDirty(int $dirty)
    {
        if ($this->workOrder) {
            $this->workOrder->setDirty($dirty);
        }
    }

    public function setUpdated()
    {
        $this->workOrder->setUpdated();
    }

    /**
     * @return double
     *  The standard cost for one unit of this component.
     * @see getExtendedStandardCost()
     */
    public function getUnitStandardCost()
    {
        return $this->stockItem->getStandardCost();
    }

    /**
     * @Assert\Callback(groups={"purchasing"})
     */
    public function validateStandardCost(ExecutionContextInterface $context)
    {
        if ($this->getUnitStandardCost() <= 0) {
            $item = $this->stockItem;
            $context->addViolation("Standard cost of $item is not set.");
        }
    }

    /**
     * @return double
     *  The unit standard cost times the unit qty ordered.
     * @see getUnitStandardCost()
     * @see getUnitQty()
     */
    public function getExtendedStandardCost()
    {
        return $this->getUnitStandardCost() * $this->getUnitQty();
    }

    /**
     * @return int
     *  The additional quantity required to account for wastage during the
     *  manufacturing process.
     */
    public function getScrapCount()
    {
        return $this->scrapCount;
    }

    public function setScrapCount($scrapCount)
    {
        $this->scrapCount = (float) $scrapCount;
    }

    public function addScrapCount($diff)
    {
        $this->scrapCount += $diff;
    }

    /**
     * Of the total scrap needed, how much remains to be issued?
     *
     * @return int|float
     */
    public function getScrapUnissued()
    {
        return $this->scrapCount - $this->getScrapIssued();
    }

    private function getScrapIssued()
    {
        $total = 0;
        foreach ($this->getIssueItems() as $ii) {
            $total += $ii->getScrapIssued();
        }
        return $total;
    }

    /** @return bool */
    public function isVersioned()
    {
        return $this->stockItem->isVersioned();
    }

    public function setVersion(Version $version)
    {
        if ($version->equals($this->version)) {
            return;
        }
        if (!$version->isSpecified()) {
            $msg = "Version for requirement {$this->stockItem} must be specified";
            throw new \InvalidArgumentException($msg);
        }
        $this->version = (string) $version;
        $this->setDirty(WorkOrder::DIRTY_ALLOCATIONS);

        /* Changing the version will render any existing allocations invalid. */
        $this->closeAllocations();
    }

    public function setVersionOrDefault(Version $version)
    {
        if ($version->isAuto()) {
            $version = new Version($this->workOrder->getId());
        }
        $version = $this->stockItem->getSpecifiedVersionOrDefault($version);
        $this->setVersion($version);
    }

    /**
     * Returns the child of this work order that fills this requirement.
     *
     * @return WorkOrder|null
     *  Null if no child work order fills this requirement.
     */
    public function getSourceWorkOrder()
    {
        if (!$this->stockItem->isManufactured()) return null;

        if (!$this->workOrder->hasChild()) return null;

        $child = $this->workOrder->getChild();
        if ($child->getSku() == $this->getSku()) {
            return $child;
        }

        return null;
    }

    /**
     * Returns true if this requirement is provided by the child of its work
     * order.
     *
     * @return boolean
     */
    public function isProvidedByChild()
    {
        return (bool) $this->getSourceWorkOrder();
    }

    /**
     * @return WorkType
     */
    public function getWorkType()
    {
        return $this->workType;
    }

    /**
     * @param WorkType $workType
     */
    public function setWorkType(WorkType $workType)
    {
        $this->workType = $workType;
    }

    /**
     * @return WorkOrder[] Orders at $location from which this requirement
     *   is allocated.
     */
    public function getPrepWorkAtLocation(Facility $location)
    {
        $prep = [];
        foreach ($this->getAllocations() as $alloc) {
            $source = $alloc->getSource();
            if ($alloc->isFromWorkOrderAtLocation($location)) {
                $prep[] = $source;
            }
        }
        return $prep;
    }
}

