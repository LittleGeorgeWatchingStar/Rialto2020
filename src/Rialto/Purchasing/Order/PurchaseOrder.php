<?php

namespace Rialto\Purchasing\Order;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Gumstix\GeographyBundle\Model\PostalAddress;
use InvalidArgumentException;
use LogicException;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Allocation\Status\AllocationStatus;
use Rialto\Allocation\Status\RequirementStatus;
use Rialto\Entity\DomainEvent;
use Rialto\Entity\HasDomainEvents;
use Rialto\Entity\LockingEntity;
use Rialto\Entity\RialtoEntity;
use Rialto\Geography\Address\Address;
use Rialto\IllegalStateException;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\Event\PurchaseOrderApproved;
use Rialto\Purchasing\Order\Event\PurchaseOrderCreated;
use Rialto\Purchasing\Order\Event\PurchaseOrderRejected;
use Rialto\Purchasing\Order\Event\PurchaseOrderSent;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Receiving\GoodsReceivedItem;
use Rialto\Purchasing\Receiving\GoodsReceivedNotice;
use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Security\User\User;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Shipper\Shipper;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Move\StockMove;
use Rialto\Task\Task;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * A purchase order is an order from the company to a supplier.
 */
class PurchaseOrder implements
    RialtoEntity,
    PostalAddress,
    LockingEntity,
    HasDomainEvents
{
    const APPROVAL_PENDING = 'pending';
    const APPROVAL_APPROVED = 'approved';
    const APPROVAL_REJECTED = 'rejected';

    /**
     * The maximum number of line items a PO should typically allow.
     */
    const MAX_LINE_ITEMS = 20;

    private $id;

    /**
     * For concurrency control.
     * @var int
     */
    private $editNo;

    private $comments = '';

    /**
     * @var string|null
     */
    private $productionNotes = '';

    /** @var DateTime The date when this order was created. */
    private $orderDate;

    /** @var DateTime When this PO was last updated. */
    private $dateUpdated;

    /** @var DateTime The date when this order was sent to the supplier. */
    private $datePrinted = null;

    /** @var string */
    private $initiator;

    private $exchangeRate = 1.0;

    private $shippingMethod = null;
    private $approvalStatus = self::APPROVAL_PENDING;

    /**
     * @var string
     * @Assert\Length(max=255, maxMessage="Reason cannot exceed 255 characters.")
     */
    private $approvalReason = '';

    /**
     * The supplier from whom we are ordering. Null if this is an in-house
     * assembly order.
     *
     * @var Supplier|null
     */
    private $supplier = null;

    /**
     * The manufacturing location. Null if this is an order for purchased
     * parts rather than manufactured items.
     *
     * @var Facility|null
     */
    private $buildLocation = null;

    /** @var User */
    private $owner;

    /** @var Shipper */
    private $shipper;

    /**
     * The stock location to which this PO should be delivered.
     *
     * @var Facility
     * @Assert\NotNull
     */
    private $deliveryLocation = null;

    /**
     * The address of the delivery location at the time of the order.
     *
     * This way, if the address of the delivery location changes, we have a
     * record of the original.
     *
     * @var Address
     */
    private $deliveryAddress = null;

    /**
     * @var StockProducer[]
     * @Assert\Valid(traverse="true")
     */
    private $items;

    /** @var PurchasingData|null */
    private $newItem = null;

    /** @var GoodsReceivedNotice[] */
    private $receipts;

    /**
     * @Assert\Length(max=50,
     *  maxMessage="Supplier reference cannot exceed {{ limit }} characters.")
     */
    private $supplierReference = '';

    /**
     * A transient field used by the supplier dashboard.
     * @var int|null
     */
    private $priority = null;

    /** @var Task[] */
    private $tasks;

    /** @var OrderSent[] */
    private $sendHistory;

    /**
     * True if this order can be modified by the auto-allocate/auto-order
     * system in order to supply stock to other orders (eg sales order).
     *
     * @var bool
     */
    private $autoAddItems = true;

    /**
     * True if the auto-allocator should attempt to allocate stock to this
     * order.
     *
     * @var bool
     */
    private $autoAllocateTo = true;

    /** @var DomainEvent[] */
    private $events = [];

    /**
     * Factory method for creating work order POs.
     *
     * For in-house assembly orders, the supplier will be null.
     *
     * @return PurchaseOrder
     */
    public static function fromLocation(Facility $buildLocation,
                                        PurchaseInitiator $initiator,
                                        User $owner)
    {
        $po = new self($initiator->getInitiatorCode(), $owner);
        $po->buildLocation = $buildLocation;
        $po->supplier = $buildLocation->getSupplier();
        return $po;
    }

    public function __construct(string $initiatorCode, User $owner)
    {
        $this->initiator = $initiatorCode;
        $this->owner = $owner;
        $this->orderDate = new DateTime();
        $this->dateUpdated = new DateTime();
        $this->items = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->sendHistory = new ArrayCollection();

        $this->events[] = new PurchaseOrderCreated($this);
    }

    /**
     * @return int
     */
    public function getEditNo()
    {
        return $this->editNo;
    }

    /** @return StockProducer[] */
    public function getItems()
    {
        return $this->items->toArray();
    }

    /** @return PurchaseOrderItem */
    public function addNonStockItem(GLAccount $account)
    {
        $poItem = PurchaseOrderItem::fromGLAccount($account, $this);
        return $this->addItem($poItem);
    }

    /** @return StockProducer */
    public function addItemFromPurchasingData(PurchasingData $purchData,
                                              Version $version = null)
    {
        if ($purchData->isManufactured()) {
            $poItem = new WorkOrder($this, $purchData, $version);
        } else {
            $poItem = PurchaseOrderItem::fromPurchasingData($purchData, $this);
        }
        return $this->addItem($poItem);
    }

    public function addItem(StockProducer $poItem)
    {
        $this->items[] = $poItem;
        $poItem->setPurchaseOrder($this);
        $this->setUpdated();
        return $poItem;
    }

    public function removeLineItem(StockProducer $poItem)
    {
        $this->items->removeElement($poItem);
        $this->setUpdated();
    }

    public function removeItemById($itemId)
    {
        foreach ($this->items as $item) {
            if ($item->getId() == $itemId) {
                $this->removeLineItem($item);
                return;
            }
        }
    }

    /**
     * @return PurchaseOrderItem
     * @throws InvalidArgumentException
     *  If the requested item does not exist.
     */
    public function getLineItem(Item $item, Version $version = null)
    {
        $poItem = $this->getLineItemIfExists($item, $version);
        if ($poItem) {
            return $poItem;
        }
        throw new InvalidArgumentException(
            "$this does not contain " . $item->getSku());
    }

    /**
     * @return PurchaseOrderItem|null
     *  Null if there is no matching line item in this PO.
     */
    public function getLineItemIfExists(Item $item, Version $version = null)
    {
        if (! $version) $version = Version::any();
        foreach ($this->items as $lineItem) {
            if ($lineItem->getSku() == $item->getSku()) {
                if ($version->matches($lineItem->getVersion())) {
                    return $lineItem;
                }
            }
        }
        return null;
    }

    /** @return bool */
    public function hasLineItem(Item $item)
    {
        return null !== $this->getLineItemIfExists($item);
    }

    /**
     * @return PurchaseOrderItem[]
     * @Assert\Valid(traverse=true)
     */
    public function getLineItems()
    {
        return $this->items->toArray();
    }

    public function hasItems()
    {
        return count($this->items) > 0;
    }

    /**
     * @return bool True if this PO has the max number of allowed line items.
     */
    public function isFull()
    {
        return count($this->items) >= self::MAX_LINE_ITEMS;
    }

    public function hasNewItem()
    {
        return null !== $this->newItem;
    }

    /** @return PurchasingData|null */
    public function getNewItem()
    {
        return $this->newItem;
    }

    public function setNewItem(PurchasingData $newItem = null)
    {
        $this->newItem = $newItem;
    }

    public function canBeSent()
    {
        if ($this->isCompleted()) {
            return false;
        }
        foreach ($this->items as $item) {
            if (! $item->canBeSent()) {
                return false;
            }
        }
        return true;
    }

    /** @return double */
    public function getTotalCost()
    {
        $total = 0;
        foreach ($this->items as $poItem) {
            $total += $poItem->getExtendedCost();
        }
        return $total;
    }

    /**
     * Returns the stock location to which this purchase order will be delivered.
     *
     * @return Facility|null
     */
    public function getDeliveryLocation()
    {
        return $this->deliveryLocation;
    }

    public function setDeliveryLocation(Facility $loc)
    {
        $this->deliveryLocation = $loc;
        $this->deliveryAddress = $loc->getAddress();
        assertion(null != $this->deliveryAddress);
    }

    /**
     * @Assert\Callback(groups={"allocationLocations"})
     */
    public function validateDeliveryAddress(ExecutionContextInterface $context)
    {
        foreach ($this->items as $item) {
            foreach ($item->getAllocations() as $alloc) {
                $location = $alloc->getLocationWhereNeeded();
                if (! $this->deliveryLocation->canSupply($location)) {
                    $context->buildViolation('purchasing.po.delivery_location.conflict')
                        ->setParameter('%item%', $item->getSku())
                        ->setParameter('%location%', $location)
                        ->setParameter('%consumer%', $alloc->getConsumerDescription())
                        ->atPath('deliveryLocation')
                        ->addViolation();
                }
            }
        }
    }

    /** @return PostalAddress */
    public function getDeliveryAddress()
    {
        if (null == $this->deliveryAddress) {
            $this->setDeliveryLocation($this->deliveryLocation);
        }
        return $this;
    }

    /**
     * In-house assembly orders don't have a supplier, hence the need for
     * this method.
     *
     * @return bool
     */
    public function hasSupplier()
    {
        return null != $this->supplier;
    }

    /**
     * Returns the supplier for this purchase order.
     *
     * @return Supplier
     * @throws LogicException if this PO has no supplier; use hasSupplier()
     *   to check first.
     */
    public function getSupplier()
    {
        if ($this->supplier) {
            return $this->supplier;
        }
        throw new LogicException("$this has no supplier");
    }

    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;
        $this->buildLocation = $supplier->getFacility();
        $currency = $supplier->getCurrency();
        $this->exchangeRate = $currency->getRate();
        return $this;
    }

    /** @return string */
    public function getSupplierName()
    {
        return $this->supplier
            ? $this->supplier->getName()
            : $this->buildLocation->getName();
    }

    /** @return Facility|null */
    public function getBuildLocationOrNull()
    {
        return $this->buildLocation;
    }

    /**
     * @deprecated use getBuildLocationOrNull() instead
     */
    public function getLocation()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getBuildLocationOrNull();
    }

    /**
     * @throws IllegalStateException if this PO is for parts instead of a build
     */
    public function getBuildLocation(): Facility
    {
        $location = $this->getBuildLocationOrNull();
        if ($location) {
            return $location;
        }
        throw new IllegalStateException("$this has no build location");
    }

    public function isAllocateFromCM()
    {
        return $this->buildLocation ? $this->buildLocation->isAllocateFromCM() : false;
    }

    public function getSupplierContacts()
    {
        return $this->supplier
            ? $this->supplier->getActiveContacts()
            : [];
    }

    public function getOrderContacts()
    {
        return $this->supplier
            ? $this->supplier->getOrderContacts()
            : [];
    }

    /** @return SupplierContact[] */
    public function getKitContacts()
    {
        return $this->supplier
            ? $this->supplier->getKitContacts()
            : [];
    }

    /** @return bool */
    public function hasWorkOrders()
    {
        return count($this->getWorkOrders()) > 0;
    }

    /**
     * All work orders in the PO.
     *
     * @return WorkOrder[]
     */
    public function getWorkOrders()
    {
        return $this->items->filter(function (StockProducer $p) {
            return $p instanceof WorkOrder;
        })->toArray();
    }

    /** @return bool */
    public function allWorkOrdersHaveRequestedDate(){
        foreach ($this->getWorkOrders() as $workOrder) {
            if ($workOrder->getRequestedDate() === null) {
                return false;
            }
        }
        return true;
    }

    /** @return bool */
    public function hasReworkOrder()
    {
        foreach ($this->getWorkOrders() as $wo) {
            if ($wo->isRework()) {
                return true;
            }
        }
        return false;
    }

    /** @return AllocationStatus */
    public function getAllocationStatus()
    {
        $status = new RequirementStatus($this->getBuildLocation());
        foreach ($this->getWorkOrders() as $wo) {
            foreach ($wo->getRequirements() as $woReq) {
                if ($woReq->isProvidedByChild()) {
                    continue;
                }
                $status->addRequirement($woReq);
            }
        }
        return $status;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    public function getSupplierReference()
    {
        return $this->supplierReference;
    }

    public function setSupplierReference($ref)
    {
        $this->supplierReference = trim($ref);
        $this->setUpdated();
    }

    /**
     * @deprecated use getDateSent() instead
     * @return DateTime
     */
    public function getDatePrinted()
    {
        return $this->getDateSent();
    }


    /** @deprecated use isSent() instead */
    public function isPrinted()
    {
        return $this->isSent();
    }

    public function setSent($sender, $note, ?string $fileName = null)
    {
        $sent = new OrderSent($this, $sender, $note, $fileName);
        $this->sendHistory[] = $sent;
        $this->datePrinted = $sent->getDateSent();
        $this->approvalStatus = self::APPROVAL_PENDING;
        $this->setUpdated();

        $this->events[] = new PurchaseOrderSent($this);
    }

    /**
     * True if this PO has been sent to the supplier.
     * @return boolean
     */
    public function isSent()
    {
        return (bool) $this->getDateSent();
    }

    /**
     * The most recent date this PO was sent.
     *
     * @return DateTime|null
     */
    public function getDateSent()
    {
        return $this->datePrinted ? clone $this->datePrinted : null;
    }

    /**
     * The earliest date this PO was sent.
     *
     * @return DateTime|null
     */
    public function getFirstDateSent()
    {
        $first = null;
        foreach ($this->sendHistory as $h) {
            $date = $h->getDateSent();
            if (null === $first) {
                $first = $date;
            } elseif ($date < $first) {
                $first = $date;
            }
        }
        return $first ? clone $first : null;
    }

    /**
     * @return OrderSent[]
     */
    public function getSendHistory()
    {
        return $this->sendHistory->getValues();
    }

    public function setUpdated()
    {
        $this->dateUpdated = new DateTime();
    }

    /** @return DateTime */
    public function getDateUpdated()
    {
        return clone $this->dateUpdated;
    }

    /**
     * When do we want this order delivered by?
     * @return DateTime|null
     */
    public function getRequestedDate()
    {
        $dates = $this->items->map(function (StockProducer $item) {
            return $item->getRequestedDate();
        })->filter(function ($date) {
            return null !== $date;
        })->toArray();
        return count($dates) ? min($dates) : null;
    }

    public function setRequestedDate(DateTime $date = null)
    {
        foreach ($this->items as $item) {
            $item->setRequestedDate($date);
        }
    }

    /**
     * Returns the OrderNo of this record.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        $label = $this->hasSupplier()
            ? 'purchase order'
            : 'assembly order';
        return "$label {$this->id}";
    }

    /** @return string */
    public function getInitiator()
    {
        return $this->initiator;
    }

    /** @return bool */
    public function isInitiatedBy($initiator)
    {
        if ($initiator instanceof PurchaseInitiator) {
            $initiator = $initiator->getInitiatorCode();
        }
        return $initiator == $this->initiator;
    }

    /** @return User */
    public function getOwner()
    {
        return $this->owner;
    }

    /** @return GoodsReceivedNotice[] */
    public function getReceipts()
    {
        return $this->receipts;
    }

    /**
     * @return GoodsReceivedItem
     * @throws InvalidArgumentException
     *   If this has no matching receipt item.
     */
    public function getReceiptItem(StockMove $move)
    {
        foreach ($this->receipts as $grn) {
            if ($grn->getId() == $move->getSystemTypeNumber()) {
                return $grn->getItem($move);
            }
        }
        throw new InvalidArgumentException(
            "$this has no GRN " . $move->getSystemTypeNumber());
    }

    /**
     * Returns the id of the location into which the order will be delivered.
     *
     * @return string
     */
    public function getDeliveryLocationId()
    {
        return $this->deliveryLocation->getId();
    }

    /**
     * @return DateTime
     */
    public function getOrderDate()
    {
        return clone $this->orderDate;
    }

    public function getStreet1(): string
    {
        return $this->deliveryAddress->getStreet1();
    }

    public function getStreet2(): string
    {
        return $this->deliveryAddress->getStreet2();
    }

    public function getMailStop(): string
    {
        return $this->deliveryAddress->getMailStop();
    }

    public function getCity(): string
    {
        return $this->deliveryAddress->getCity();
    }

    public function getStateCode(): string
    {
        return $this->deliveryAddress->getStateCode();
    }

    public function getStateName(): string
    {
        return $this->deliveryAddress->getStateName();
    }

    public function getPostalCode(): string
    {
        return $this->deliveryAddress->getPostalCode();
    }

    public function getCountryCode(): string
    {
        return $this->deliveryAddress->getCountryCode();
    }

    public function getCountryName(): string
    {
        return $this->deliveryAddress->getCountryName();
    }

    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }

    public function getSupplierId()
    {
        return $this->supplier->getId();
    }

    /**
     * True if is purchase order is completed.
     *
     * @return boolean
     */
    public function isCompleted()
    {
        if (count($this->items) == 0) {
            return false;
        }
        foreach ($this->items as $poItem) {
            if (! $poItem->isClosed()) {
                return false;
            }
        }
        return true;
    }

    /** @return Shipper|null */
    public function getShipper()
    {
        return $this->shipper;
    }

    /** @return ShippingMethod|null */
    public function getShippingMethod()
    {
        if (! $this->shippingMethod) return null;
        if (! $this->shipper) return null;
        return $this->shipper->getShippingMethod($this->shippingMethod);
    }

    public function setShippingMethod(ShippingMethod $method = null)
    {
        if ($method) {
            $this->shipper = $method->getShipper();
            $this->shippingMethod = $method->getCode();
        } else {
            $this->shippingMethod = null;
        }
    }

    public function isApproved(): bool
    {
        return $this->approvalStatus == self::APPROVAL_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->approvalStatus == self::APPROVAL_REJECTED;
    }

    public function isPendingApproval(): bool
    {
        return $this->approvalStatus == self::APPROVAL_PENDING;
    }

    public function getApprovalStatus(): string
    {
        return $this->approvalStatus;
    }

    public function setApprovalStatus(string $status)
    {
        $this->approvalStatus = $status;
        $this->setUpdated();

        if ($this->isApproved()) {
            $this->events[] = new PurchaseOrderApproved($this);
        } elseif ($this->isRejected()) {
            $this->events[] = new PurchaseOrderRejected($this);
        }
    }

    public function getApprovalReason(): string
    {
        return $this->approvalReason;
    }

    public function setApprovalReason($reason)
    {
        $this->approvalReason = trim($reason);
    }

    /** @Assert\Callback */
    public function validateApprovalReason(ExecutionContextInterface $context)
    {
        if ($this->isRejected() && ! $this->approvalReason) {
            $context->addViolation("A reason is required if the PO is rejected.");
        }
    }

    public function getCurrency()
    {
        return $this->supplier->getCurrency();
    }

    /**
     * @return int|null
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int|null $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return Task[] Tasks that the supplier needs to do.
     */
    public function getSupplierTasks()
    {
        return $this->tasks->filter(function (Task $t) {
            return $t->hasRole([
                Role::SUPPLIER_SIMPLE,
                Role::SUPPLIER_ADVANCED,
            ]);
        })->toArray();
    }

    /**
     * @return Task[] Tasks that we need to do.
     */
    public function getEmployeeTasks()
    {
        return $this->tasks->filter(function (Task $t) {
            return ! $t->hasRole([
                Role::SUPPLIER_SIMPLE,
                Role::SUPPLIER_ADVANCED,
            ]);
        })->toArray();
    }

    public function isAutoAddItems(): bool
    {
        return $this->autoAddItems;
    }

    /**
     * @param boolean $autoAdd
     */
    public function setAutoAddItems($autoAdd)
    {
        $this->autoAddItems = (bool) $autoAdd;
    }

    public function isAutoAllocateTo(): bool
    {
        return $this->autoAllocateTo;
    }

    /**
     * @param boolean $auto
     */
    public function setAutoAllocateTo($allocateTo)
    {
        $this->autoAllocateTo = (bool) $allocateTo;
    }

    /**
     * Can the auto allocator manipulate this purchase order to satisfy a
     * requirement for the given Item and Version.
     */
    public function canSupplyItem(Item $item, Version $version): bool
    {
        return !$this->isFull() || $this->getLineItemIfExists($item, $version);
    }

    /**
     * @return string|null
     */
    public function getProductionNotes()
    {
        return $this->productionNotes;
    }

    /**
     * @param string|null $productionNotes
     */
    public function setProductionNotes($productionNotes)
    {
        $this->productionNotes = $productionNotes;
    }

    /**
     * @inheritdoc
     */
    public function popEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}
