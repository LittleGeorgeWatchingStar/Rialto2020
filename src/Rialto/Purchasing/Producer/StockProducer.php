<?php

namespace Rialto\Purchasing\Producer;

use DateTime;
use LogicException;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Allocation\Allocation\ProducerAllocation;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\IllegalStateException;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Catalog\CostBreak;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\User\User;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A purchase order item or work order.
 *
 * Called a "stock producer" because PO items and work orders "create"
 * new stock when they are received.
 */
abstract class StockProducer extends BasicStockSource
{
    const SOURCE_TYPE = 'StockProducer';

    const MINIMUM_COST = 0.0001;
    const UNIT_COST_PRECISION = 4;

    const FLAG_AUTO_RECEIVE = 'auto_receive';
    const FLAG_USE_TAX_NEEDED = 'use_tax_needed';
    const FLAG_ZERO_COST = 'zero_cost';

    private $id;

    /** @var PurchaseOrder */
    protected $purchaseOrder;

    /** @var PurchasingData|null */
    protected $purchasingData = null;

    /** @var string */
    protected $version = '';

    /** @Assert\NotBlank(message="Description should not be blank.") */
    protected $description = '';

    /**
     * @var GLAccount
     * @Assert\NotNull(message="GLAccount is required.")
     */
    private $glAccount;

    private $dateCreated;
    private $dateUpdated;
    private $dateClosed = null;

    /**
     * @var DateTime|null
     * @Assert\Date(groups={"Default", "requestedDate"})
     */
    private $requestedDate = null;
    private $commitmentDate = null;

    /**
     * @Assert\Type(type="numeric", message="Quantity ordered must be a number.")
     * @Assert\Range(min=1, minMessage="Quantity ordered must be at least one.")
     */
    protected $qtyOrdered;
    private $qtyReceived = 0;
    private $qtyInvoiced = 0;

    /**
     * @Assert\Type(type="numeric", message="Unit cost must be a number.")
     * @Assert\Range(min=0, minMessage="Unit cost cannot be negative.")
     */
    private $expectedUnitCost = 0.0;

    private $actualUnitCost = 0.0;

    private $openForAllocation = true;
    private $flags = '';
    /**
     * @var int
     * @Assert\Range(min=1, max=100000)
     */
    protected $boardsPerPanel = 1;

    protected function __construct(PurchaseOrder $po)
    {
        parent::__construct();
        $this->purchaseOrder = $po;
        $this->dateCreated = new DateTime();
        $this->dateUpdated = new DateTime();
    }

    /**
     * Called when this object is constructed.
     */
    protected function initializePurchasingData(PurchasingData $purchData)
    {
        $this->setQtyOrdered($purchData->getDefaultOrderQuantity());
        $this->setPurchasingData($purchData);
    }

    /**
     * Called when this object is constructed or when the user changes the
     * purchasing data.
     *
     * @see initializePurchasingData()
     */
    public function setPurchasingData(PurchasingData $purchData)
    {
        $this->validateSupplier($purchData->getSupplier());
        $this->purchasingData = $purchData;

        $item = $purchData->getStockItem();
        $this->validateStockItem($item);

        $this->setGLAccount($item->getStockAccount());
        $this->initializeUnitCost($purchData->getCost($this->qtyOrdered));
    }

    private function validateSupplier(Supplier $supplier = null)
    {
        if ($this->hasSupplier()) {
            assertion($this->getSupplier()->equals($supplier));
        }
    }

    protected abstract function validateStockItem(StockItem $item);

    /** @return PurchasingData|null */
    public function getPurchasingData()
    {
        return $this->purchasingData;
    }

    /**
     * Returns the purchase order for this purchase order detail.
     */
    public function getPurchaseOrder(): PurchaseOrder
    {
        return $this->purchaseOrder;
    }

    /** @return User */
    public function getOwner()
    {
        return $this->purchaseOrder->getOwner();
    }

    public function getBinSize()
    {
        return $this->purchasingData ? $this->purchasingData->getBinSize() : 0;
    }

    public function getBinStyle()
    {
        return $this->purchasingData ? $this->purchasingData->getBinStyle() : null;
    }

    public function getCatalogNumber()
    {
        return $this->purchasingData ? $this->purchasingData->getCatalogNumber() : '';
    }

    public function getQuotationNumber()
    {
        return $this->purchasingData ? $this->purchasingData->getQuotationNumber() : '';
    }

    public function getManufacturerCode()
    {
        return $this->purchasingData ? $this->purchasingData->getManufacturerCode() : '';
    }

    /** @return Manufacturer|null */
    public function getManufacturer()
    {
        return $this->purchasingData ? $this->purchasingData->getManufacturer() : null;
    }

    /**
     * @return string[]
     */
    public function getFlags(): array
    {
        return explode(' ', $this->flags);
    }

    /**
     * Returns true if the given flag is set.
     */
    protected function hasFlag(string $flag): bool
    {
        return in_array($flag, $this->getFlags(), true);
    }

    /**
     * @param string[] $flags
     */
    public function setFlags(array $flags): self
    {
        $this->flags = implode(' ', array_unique($flags));
        return $this;
    }

    public function addFlag(string $flag): self
    {
        $flags = $this->getFlags();
        $flags[] = $flag;
        return $this->setFlags($flags);
    }

    public function removeFlag(string $flag): self
    {
        $flags = $this->getFlags();
        $flags = array_diff($flags, [$flag]);
        return $this->setFlags($flags);
    }

    public function getFlagOptions(): array
    {
        return [
            // label => value
            'auto-receive' => self::FLAG_AUTO_RECEIVE,
            'use tax needed' => self::FLAG_USE_TAX_NEEDED,
            'allow zero cost' => self::FLAG_ZERO_COST,
        ];
    }

    /**
     * @return GLAccount
     */
    public function getPurchasePriceVarianceAccount()
    {
        if (! $this->isStockItem()) {
            throw new IllegalStateException("No a stock item");
        }
        $category = $this->getStockItem()->getCategory();
        return $category->getPurchasePriceVarianceAccount();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getOrderNumber()
    {
        return $this->purchaseOrder->getId();
    }

    public function getSku()
    {
        return $this->purchasingData ? $this->purchasingData->getSku() : null;
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /**
     * @see isStockItem()
     * @return PhysicalStockItem|null
     */
    public function getStockItem()
    {
        return $this->purchasingData ? $this->purchasingData->getStockItem() : null;
    }

    /**
     * Returns true if this purchse order detail is for a stock item; false
     * if it is for something else (like labour).
     *
     * @see isLabour()
     * @see getStockItem()
     * @return bool
     */
    public function isStockItem()
    {
        return null !== $this->purchasingData;
    }

    public function isVersioned()
    {
        if ($this->isStockItem()) {
            return $this->getStockItem()->isVersioned();
        }
        return (bool) $this->version;
    }

    public abstract function setVersion(Version $version);

    /**
     * @Assert\Callback
     */
    public function validateVersion(ExecutionContextInterface $context)
    {
        if (! $this->purchasingData) {
            return;
        }
        if (! $this->purchasingData->supportsVersion($this->getVersion())) {
            $context->buildViolation("%pd% does not support version %version%")
                ->setParameter('%pd%', $this->purchasingData)
                ->setParameter('%version%', $this->getVersion())
                ->atPath('version')
                ->addViolation();
        }
    }

    public function getQtyOrdered()
    {
        return $this->qtyOrdered;
    }

    public function getQtyReceived()
    {
        return $this->qtyReceived;
    }

    /**
     * Increments the quantity received by the given amount.  If the new
     * quantity is equal to or greater than the quantity ordered, then this
     * item will be marked as completed.
     *
     * @param int|double $qty
     */
    public function addQtyReceived($qty)
    {
        $this->qtyReceived += $qty;
        if ($this->qtyReceived >= $this->qtyOrdered) {
            $this->close();
        } elseif ($qty < 0 && $this->qtyReceived < $this->qtyOrdered) {
            /* The admin can reverse a work order receipt. */
            $this->reopen();
        } elseif ($qty != 0) {
            $this->setUpdated();
        }
    }

    /**
     * @Assert\Range(min=0, minMessage="Qty ordered cannot be less that qty received.")
     */
    public function getQtyRemaining()
    {
        return $this->getQtyOrdered() - $this->getQtyReceived();
    }

    public function getQtyInvoiced()
    {
        return $this->qtyInvoiced;
    }

    public function addQtyInvoiced($qtyInvoiced, $actualCost = null)
    {
        $this->qtyInvoiced += $qtyInvoiced;
        if (null !== $actualCost) {
            $this->actualUnitCost = $actualCost;
        }
    }

    /**
     * @Assert\Range(min=0,
     *   minMessage="Qty ordered cannot be less that qty invoiced.")
     */
    public function getQtyUninvoiced()
    {
        return $this->qtyOrdered - $this->qtyInvoiced;
    }

    public function canBeSent()
    {
        return true;
    }

    public function canBeReceived()
    {
        return $this->isOrderSent() && $this->getQtyRemaining() > 0;
    }

    /**
     * Once an item is "in process", some modifications can no longer
     * be made.
     *
     * @return bool
     */
    public function isInProcess()
    {
        return $this->isClosed()
        || ($this->qtyInvoiced > 0)
        || ($this->qtyReceived > 0);
    }

    public function getSourceNumber()
    {
        return $this->getId();
    }

    /** @deprecated use getStandardUnitCost() instead */
    public function getUnitStandardCost()
    {
        return $this->getStandardUnitCost();
    }

    public function getStandardUnitCost()
    {
        return $this->isStockItem() ? $this->getStockItem()->getStandardCost() : $this->expectedUnitCost;
    }

    /** @Assert\Callback */
    public function validateStandardCost(ExecutionContextInterface $context)
    {
        if ($this->allowsZeroCost() && (! $this->isStockItem())) {
            return;
        }
        if ($this->getStandardUnitCost() < self::MINIMUM_COST) {
            $context->buildViolation(
                "Current standard cost must be at least _limit.", [
                '_limit' => self::MINIMUM_COST
            ])
                ->atPath('purchasingData')
                ->addViolation();
        }
    }

    public function getUnitCost()
    {
        return $this->expectedUnitCost;
    }

    public function getExpectedUnitCost()
    {
        return $this->expectedUnitCost;
    }

    public function setUnitCost($cost)
    {
        $this->expectedUnitCost = $cost;
    }

    public function initializeUnitCost($cost)
    {
        $this->expectedUnitCost = $cost;
        if (bceq($cost, 0, self::UNIT_COST_PRECISION)) {
            $this->addFlag(StockProducer::FLAG_ZERO_COST);
        } else {
            $this->removeFlag(StockProducer::FLAG_ZERO_COST);
        }
    }

    /** @Assert\Callback */
    public function validateUnitCost(ExecutionContextInterface $context)
    {
        if ($this->allowsZeroCost()) {
            return;
        }
        if ($this->expectedUnitCost < self::MINIMUM_COST) {
            $context->buildViolation("Unit cost must be at least %limit%.")
                ->setParameter('%limit%', self::MINIMUM_COST)
                ->atPath('unitCost')
                ->addViolation();
        }
    }

    public function getExtendedCost()
    {
        // TODO: rounding?
        return $this->getQtyOrdered() * $this->getUnitCost();
    }

    public function getActualCost()
    {
        return $this->actualUnitCost;
    }

    /** @return DateTime */
    public function getDateCreated()
    {
        return clone $this->dateCreated;
    }

    /** @return DateTime */
    public function getDateUpdated()
    {
        return clone $this->dateUpdated;
    }

    /** @return DateTime|null */
    public function getDateClosed()
    {
        return $this->dateClosed ? clone $this->dateClosed : null;
    }

    /**
     * @return DateTime|null
     *  The date by which the purchaser would like to have the item delivered.
     */
    public function getRequestedDate()
    {
        return $this->requestedDate ? clone $this->requestedDate : null;
    }

    /**
     * The date by which the purchaser would like to have the item delivered.
     */
    public function setRequestedDate(DateTime $date = null)
    {
        $this->requestedDate = $date ? clone $date : null;
    }

    public function getDueDate()
    {
        return $this->getRequestedDate();
    }

    /**
     * @return DateTime|null
     *  The date when the vendor committed to delivering this product.
     */
    public function getCommitmentDate()
    {
        return $this->commitmentDate ? clone $this->commitmentDate : null;
    }

    /**
     * @param DateTime $date
     *   The date when the vendor committed to delivering this product.
     */
    public function setCommitmentDate(DateTime $date = null)
    {
        $this->commitmentDate = $date ? clone $date : null;
        $this->setUpdated();
    }

    /**
     * Sets the commitment date to a default date relative to today.
     */
    public function initializeCommitmentDate()
    {
        if (null === $this->commitmentDate) {
            $this->setCommitmentDate($this->getDefaultCommitmentDate());
        }
    }

    protected function getDefaultCommitmentDate()
    {
        return new \DateTime('+1 week');
    }

    /**
     * True if this producer has missed its commitment date.
     *
     * @param \DateTimeInterface $asOf For unit testing.
     * @return bool
     */
    public function isOverdue(\DateTimeInterface $asOf = null)
    {
        if ($this->isClosed()) {
            return false;
        }
        if (null === $this->commitmentDate) {
            return false;
        }
        if (null === $asOf) {
            $asOf = new DateTime(); // now
        }
        return $this->commitmentDate < $asOf;
    }

    /**
     * Returns true if this item should be automatically received when
     * its parent PO comes in.
     *
     * @return bool
     */
    public function isAutoReceive()
    {
        return $this->hasFlag(self::FLAG_AUTO_RECEIVE);
    }

    public function allowsZeroCost()
    {
        return $this->hasFlag(self::FLAG_ZERO_COST);
    }

    public function isZeroCost()
    {
        return $this->allowsZeroCost() && ($this->expectedUnitCost == 0);
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->dateClosed != null;
    }

    /**
     * Marks this producer as closed.
     */
    protected function close()
    {
        if (! $this->dateClosed) {
            $this->dateClosed = new DateTime();
            $this->setUpdated();
        }
    }

    /**
     * Closes this producer and cancels any outstanding allocations.
     */
    public function cancel()
    {
        $this->close();
        $this->cancelAllocations();
    }

    /**
     * @deprecated use isClosed() instead
     */
    public function isCompleted()
    {
        return $this->isClosed();
    }

    public function reopen()
    {
        if ($this->qtyReceived < $this->qtyOrdered) {
            $this->dateClosed = null;
            $this->setUpdated();
        }
    }

    public function setUpdated()
    {
        $this->dateUpdated = new DateTime();
        $this->purchaseOrder->setUpdated();
        foreach ($this->getAllocations() as $alloc) {
            $alloc->setUpdated();
        }
    }


    public function isOrderSent()
    {
        $po = $this->getPurchaseOrder();
        return $po ? $po->isSent() : false;
    }

    /**
     * Returns true if this line item is for a work order.
     *
     * @see isStockItem()
     * @see getWorkOrder()
     * @return bool
     */
    public function isWorkOrder()
    {
        return $this instanceof WorkOrder;
    }

    /**
     * @deprecated  Use isWorkOrder() instead.
     */
    public function isLabour()
    {
        return $this->isWorkOrder();
    }

    public function isLabourForItem(Item $item)
    {
        return $this->isWorkOrder() &&
        $item->getSku() == $this->getSku();
    }

    public function isNew()
    {
        return ! $this->getId();
    }

    public function isPCB()
    {
        return $this->isCategory(StockCategory::PCB);
    }

    public function isBoard()
    {
        return $this->isCategory(StockCategory::BOARD);
    }

    public function isProduct()
    {
        return $this->isCategory(StockCategory::PRODUCT);
    }

    public function isCategory($category)
    {
        return $this->isStockItem() ? $this->getStockItem()->isCategory($category) : false;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int|float $qty
     * @return StockProducer fluent interface
     */
    public function setQtyOrdered($qty)
    {
        $this->qtyOrdered = $qty;
        return $this;
    }

    public function getGLAccount()
    {
        return $this->glAccount;
    }

    public function setGLAccount(GLAccount $account)
    {
        $this->glAccount = $account;
    }

    public function hasSupplier()
    {
        return $this->purchaseOrder->hasSupplier();
    }

    /**
     * @return Supplier
     * @throws LogicException if this PO has no supplier; use hasSupplier()
     *   to check first.
     */
    public function getSupplier()
    {
        return $this->purchaseOrder->getSupplier();
    }

    /** @return string */
    public function getSupplierName()
    {
        return $this->purchaseOrder->getSupplierName();
    }

    /**
     * Resets the unit cost based on the purchasing data and order quantity.
     */
    public function resetUnitCost()
    {
        $costBreak = $this->getCostBreak();
        if ($costBreak) {
            $this->setUnitCost($costBreak->getUnitCost());
        }
    }

    /** @return CostBreak */
    private function getCostBreak()
    {
        if (! $this->purchasingData) {
            return null;
        }

        $costBreak = $this->purchasingData->getLowestCostBreak($this->qtyOrdered);
        assertion(null != $costBreak);
        return $costBreak;
    }

    public function roundQtyOrdered()
    {
        if ($this->purchasingData) {
            $this->qtyOrdered = $this->purchasingData->roundOrderQty($this->qtyOrdered);
        }
    }

    public function getSourceType()
    {
        return self::SOURCE_TYPE;
    }

    public function getSourceDescription()
    {
        return 'PO ' . $this->getOrderNumber();
    }

    protected function instantiateAllocation(Requirement $requirement)
    {
        assertion($requirement->getConsumer() !== $this);
        return new ProducerAllocation($requirement, $this);
    }

    public function isOpenForAllocation()
    {
        return $this->openForAllocation;
    }

    public function setOpenForAllocation($open)
    {
        $this->openForAllocation = $open;
    }

    public function getQtyAvailableTo(RequirementCollection $requirements)
    {
        if (! $this->isCompatibleWith($requirements)) {
            return 0;
        }
        $calc = new ProducerAvailabilityCalculator($this);
        return $calc->getQtyAvailableTo($requirements);
    }

    public function deleteAllocationsForOtherLocations()
    {
        $deliveryLocation = $this->purchaseOrder->getDeliveryLocation();
        foreach ($this->getAllocations() as $alloc) {
            $neededAt = $alloc->getLocationWhereNeeded();
            if (! $deliveryLocation->canSupply($neededAt)) {
                $alloc->close();
            }
        }
    }

    /*
     * TODO: We eventually want to ensure that this method can only be called
     * inside an explicit transactional boundary that is aware of invariants
     * that must be kept in place (i.e. a command handler). This is best
     * accomplished by ensuring that only command handlers and purchase orders
     * are able to access direct references to StockProducers.
     */
    public function setPurchaseOrder(PurchaseOrder $po): void
    {
        $this->purchaseOrder = $po;
    }

    /**
     * @return int
     */
    public function getBoardsPerPanel()
    {
        return $this->boardsPerPanel;
    }

    /**
     * @param int $boardsPerPanel
     */
    public function setBoardsPerPanel($boardsPerPanel)
    {
        $this->boardsPerPanel = $boardsPerPanel;
    }
}
