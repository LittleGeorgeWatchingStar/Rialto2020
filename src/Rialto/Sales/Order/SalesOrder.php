<?php

namespace Rialto\Sales\Order;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Gumstix\GeographyBundle\Model\PostalAddress;
use InvalidArgumentException;
use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Debtor\DebtorCredit;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Accounting\Debtor\OrderAllocation;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Status\DetailedRequirementStatus;
use Rialto\Database\Orm\DbManager;
use Rialto\Email\Mailable\Mailable;
use Rialto\Entity\RialtoEntity;
use Rialto\Geography\Address\Address;
use Rialto\IllegalStateException;
use Rialto\Manufacturing\Customization\Customizable;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Sales\Customer\SalesArea;
use Rialto\Sales\Order\Allocation\SalesOrderDetailAllocator;
use Rialto\Sales\Order\Dates\TargetShipDateCalculator;
use Rialto\Sales\Price\Orm\ProductPriceRepository;
use Rialto\Sales\Price\PriceCalculator;
use Rialto\Sales\Price\ProductPrice;
use Rialto\Sales\Returns\SalesReturn;
use Rialto\Sales\SalesEvents;
use Rialto\Sales\Salesman\Salesman;
use Rialto\Sales\Shipping\ShippableOrder;
use Rialto\Sales\Type\SalesType;
use Rialto\Security\User\User;
use Rialto\Shipping\Export\AllowedCountry;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\ReasonForShipping;
use Rialto\Shipping\Shipment\ShipmentFactory;
use Rialto\Shipping\Shipper\Shipper;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Sku;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A sales order placed by a customer.
 *
 * @AllowedCountry
 */
class SalesOrder implements
    RialtoEntity,
    ShippableOrder,
    PostalAddress,
    Mailable,
    TaxableOrder
{
    /**
     * The fraction of the total order price that is required as a deposit.
     *
     * @var float
     */
    const DEPOSIT_FRACTION = 0.35;

    /**
     * The amount of time after which a sales order is overdue.
     *
     * @var int
     */
    const OVERDUE_SECONDS = 86400; // one day

    const ORDER = 'order';
    const QUOTATION = 'quotation';
    const BUDGET = 'budget';

    /**
     * @var string
     */
    private $id;

    /**
     * @var CustomerBranch
     * @Assert\NotNull(message="No customer branch set.")
     */
    private $customerBranch;

    /**
     * @Assert\Length(max=255, maxMessage="Customer reference is too long.")
     */
    private $customerReference = '';

    /**
     * @Assert\Length(max=255, maxMessage="Customer tax ID is too long.")
     */
    private $customerTaxId = '';

    /**
     * @var string|null
     */
    private $comments;

    /**
     * @var string|null
     */
    private $productionNotes = '';

    /**
     * When the order was created.
     *
     * @var DateTime
     */
    private $dateOrdered;

    /**
     * When we have committed to have this order shipped.
     *
     * @var DateTime|null
     */
    private $targetShipDate = null;

    /**
     * When the customer wants the order to be delivered.
     *
     * @var DateTime|null
     */
    private $deliveryDate = null;

    /**
     * When the order was approved to ship.
     *
     * @var DateTime|null
     */
    private $dateToShip = null;

    /**
     * When the order was last downloaded as a PDF, or when the customer was
     * last emailed about the order.
     *
     * @todo convert to send history table
     * @var DateTime|null
     */
    private $datePrinted = null;

    private $shipmentType = '';

    /**
     * @Assert\Length(max=255, maxMessage="Billing name is too long.")
     */
    private $billingName = '';

    /**
     * @var Address
     * @Assert\NotNull
     * @Assert\Valid
     */
    private $billingAddress;

    /**
     * @Assert\NotBlank(message="Delivery company name is required.")
     * @Assert\Length(max=255, maxMessage="Delivery company name is too long.")
     */
    private $deliveryCompany;

    /**
     * The name of the person the order will be delivered to.
     * @Assert\NotBlank(message="Contact name is required.")
     */
    private $deliveryName;

    /**
     * @var Address
     * @Assert\NotNull
     * @Assert\Valid
     */
    private $shippingAddress;

    /**
     * @Assert\NotBlank(message="Contact phone is required.")
     *@Assert\Length(max=50, maxMessage="Contact phone is too long.")
     */
    private $contactPhone;

    /**
     * @Assert\NotBlank(message="Contact email is required.")
     * @Assert\Length(max=255, maxMessage="Contact email is too long.")
     * @Assert\Email(message="Contact email is not a valid email address.")
     */
    private $contactEmail;

    /**
     * @Assert\Type(type="numeric", message="Shipping price must be a number.")
     * @Assert\Range(min=0, minMessage="Shipping price cannot be negative.")
     */
    private $shippingPrice = 0;

    /** @deprecated Use getTaxAmount() instead */
    private $salesTaxes = 0;

    /**
     * @Assert\Range(min=0)
     */
    private $depositAmount = 0;

    private $salesStage = self::QUOTATION;

    /**
     * For example, "warranty replacement".
     * @var string
     * @Assert\NotBlank(message="Reason for shipping is required.")
     * @Assert\Length(max=255, maxMessage="Reason for shipping is too long.")
     */
    private $reasonForShipping;

    /**
     * @var string
     * @Assert\Length(max=255, maxMessage="Reason not to ship is too long.")
     */
    private $reasonNotToShip = '';

    /**
     * @var int
     */
    private $sourceID = 0;

    /**
     * @var bool
     */
    private $priority = false;

    /**
     * @var SalesOrderDetail[]
     *  Indexed by SalesOrderDetail id.
     * @Assert\Count(max=50,
     *   maxMessage="Orders cannot have more than {{ limit }} items.")
     */
    private $lineItems;

    /**
     * @var StockItem
     * A new item that will be added to the line items if addNewItem()
     * is called.
     * @Assert\Valid
     */
    private $newItem;

    /**
     * @var User
     * @Assert\NotNull(message="No creator set.")
     */
    private $createdBy;

    /** @var SalesType */
    private $salesType;

    /** @var Shipper|null */
    private $shipper;

    /**
     * The stock facility from which this order will be shipped.
     * Stock needs to be here in order for this order to be shipped.
     *
     * @Assert\NotNull(message="No shipping facility set.")
     */
    private $shipFromFacility;

    /** @var DebtorInvoice[] */
    private $invoices;

    /** @var CardTransaction[] */
    private $cardTransactions;

    /** @var OrderAllocation[] */
    private $creditAllocations;

    public function __construct(CustomerBranch $branch)
    {
        $this->lineItems = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->cardTransactions = new ArrayCollection();
        $this->creditAllocations = new ArrayCollection();

        $this->customerBranch = $branch;
        $this->reasonForShipping = $branch->isInternalCustomer()
            ? ReasonForShipping::INTERNAL
            : ReasonForShipping::SALE;
        $this->shipFromFacility = $branch->getDefaultLocation();
        $this->dateOrdered = new DateTime();
        $this->setBillingName($branch->getContactName());
        $this->setBillingAddress($branch);
        $this->setShipper($branch->getDefaultShipper());
        $this->setDeliveryCompany($branch->getBranchName());
        $this->setDeliveryName($branch->getContactName());
        $this->setDeliveryAddress($branch);
        $this->setContactPhone($branch->getContactPhone());
        $this->setEmail($branch->getEmail());
        $this->setCustomerTaxId($branch->getTaxId());
    }

    /**
     * Factory method.
     *
     * @return SalesOrder
     *  A replacement quotation for the given sales return.
     */
    public static function fromSalesReturn(SalesReturn $rma)
    {
        $origOrder = $rma->getOriginalOrder();
        $replacementOrder = clone $origOrder;
        $replacementOrder->lineItems = new ArrayCollection();
        $replacementOrder->customerReference = $rma->getRmaNumber();
        $replacementOrder->salesType = SalesType::fetchReplacementSale();
        $replacementOrder->shippingPrice = 0;
        $replacementOrder->createdBy = $rma->getAuthorizedBy();
        $replacementOrder->reasonForShipping = ReasonForShipping::REPLACEMENT;
        return $replacementOrder;
    }

    /**
     * Allocates stock to fill this order's line items.
     *
     * @return int
     *  The total number of units allocated.
     */
    public function allocateForLineItems(AllocationFactory $factory, DbManager $dbm)
    {
        $qtyAllocated = 0;
        foreach ($this->getLineItems() as $item) {
            foreach ($item->getRequirements() as $req) {
                $allocator = SalesOrderDetailAllocator::create($req, $dbm);
                $allocator->setShareBins(true);
                $qtyAllocated += $allocator->allocateFromStock($factory);
                $qtyAllocated += $allocator->allocateFromOrders($factory);
            }
        }
        return $qtyAllocated;
    }

    /** @return DetailedRequirementStatus */
    public function getAllocationStatus()
    {
        $status = new DetailedRequirementStatus($this->getShipFromFacility());
        foreach ($this->getLineItems() as $item) {
            foreach ($item->getRequirements() as $req) {
                $status->addRequirement($req);
            }
        }
        return $status;
    }


    /**
     * Marks this order as completed.
     */
    public function close()
    {
        /* An order is complete if all of its line items are complete. */
        foreach ($this->lineItems as $lineItem) {
            $lineItem->close();
        }
    }

    /**
     * @return bool
     *  True if any of the line items of this order is a physical product
     *  that can be shipped.
     */
    public function containsShippableItems()
    {
        foreach ($this->lineItems as $item) {
            if (!$item->isDummy()) {
                return true;
            }
        }
        return false;
    }

    public function __clone()
    {
        if (!$this->id) {
            return; // Required by Doctrine
        }

        $this->id = null;
        $this->sourceID = 0;
        $this->dateOrdered = new DateTime();
        $this->deliveryDate = null;
        $this->dateToShip = null;
        $this->datePrinted = null;
        $this->salesStage = self::QUOTATION;
        $this->salesType = SalesType::fetchDirectSale();
        $newRef = $this->customerReference ?: $this->id;
        $this->customerReference = '(RPT) ' . $newRef;
        $originalItems = $this->lineItems;
        $this->lineItems = new ArrayCollection();
        foreach ($originalItems as $item) {
            $newItem = clone $item;
            $this->addLineItem($newItem);
        }
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customerBranch->getCustomer();
    }

    /**
     * @return CustomerBranch
     */
    public function getCustomerBranch()
    {
        return $this->customerBranch;
    }

    public function setCustomerBranch(CustomerBranch $branch)
    {
        $this->customerBranch = $branch;
    }

    public function containsItem(Item $item): bool
    {
        return (bool) $this->getItemOrNull($item);
    }

    /**
     * Returns the line item for the given stock item.
     *
     * @throws InvalidArgumentException
     *  If this order does not contain the given item.
     * @see containsItem()
     */
    public function getLineItem(Item $stockItem): SalesOrderDetail
    {
        $lineItem = $this->getItemOrNull($stockItem);
        if ($lineItem) {
            return $lineItem;
        }
        throw new InvalidArgumentException(sprintf(
            'Sales order %s does not contain item %s; ' .
            'try calling containsItem() first',
            $this->getId(),
            $stockItem->getSku()
        ));
    }

    private function getItemOrNull(Item $item)
    {
        $customization = ($item instanceof Customizable)
            ? $item->getCustomization()
            : null;
        foreach ($this->lineItems as $lineItem) {
            if ($lineItem->isMatch($item, $customization)) {
                return $lineItem;
            }
        }
        return null;
    }

    /**
     * @Assert\Valid(traverse="true")
     * @return SalesOrderDetail[]
     */
    public function getLineItems()
    {
        return $this->lineItems->toArray();
    }

    /**
     * @return SalesOrderDetail[]
     */
    public function getTangibleLineItems()
    {
        return array_filter($this->getLineItems(), function (SalesOrderDetail $lineItem) {
            return !Sku::isServiceFee($lineItem->getSku());
        });
    }

    public function addLineItem(SalesOrderDetail $lineItem)
    {
        $lineItem->setSalesOrder($this);
        $this->lineItems[] = $lineItem;
    }

    public function removeLineItem(SalesOrderDetail $lineItem)
    {
        $this->lineItems->removeElement($lineItem);
    }

    public function clearLineItems()
    {
        $this->lineItems->clear();
    }

    /**
     * Adds $item to this order, or increments the quantity if
     * $item is already in the order.
     */
    public function addItem(
        StockItem $item,
        GLAccount $discountAccount,
        $quantity = 1): SalesOrderDetail
    {
        $lineItem = $this->getItemOrNull($item);
        if (!$lineItem) {
            $lineItem = new SalesOrderDetail($item, $discountAccount);
            $lineItem->setQtyOrdered($quantity);
            $this->addLineItem($lineItem);
        } else {
            $lineItem->addQtyOrdered($quantity);
        }
        return $lineItem;
    }

    public function hasItems()
    {
        return count($this->lineItems) > 0;
    }

    public function setNewItem(StockItem $stockItem = null)
    {
        $this->newItem = $stockItem;
    }

    /** @Assert\Callback */
    public function validateNewItem(ExecutionContextInterface $context)
    {
        if ($this->hasItems()) {
            if ($this->newItem && $this->containsItem($this->newItem)) {
                $context->buildViolation("This order already contains {$this->newItem}.")
                    ->atPath('newItem')
                    ->addViolation();
            }
        } elseif (!$this->newItem) {
            $context->buildViolation("Orders must have at least one item.")
                ->atPath('newItem')
                ->addViolation();
        }
    }

    public function hasNewItem()
    {
        return null !== $this->newItem;
    }

    public function addNewItem(DbManager $dbm)
    {
        if (!$this->newItem) {
            return;
        }
        $lineItem = $this->addItem($this->newItem, GLAccount::fetchSalesDiscounts($dbm));

        /** @var $repo ProductPriceRepository */
        $repo = $dbm->getRepository(ProductPrice::class);
        $price = $repo->findBySalesOrderDetail($lineItem);
        $lineItem->setBaseUnitPrice($price);

        $this->newItem = null;
    }

    public function getNewItem()
    {
        return $this->newItem;
    }

    public function resetRequirements(ObjectManager $om)
    {
        foreach ($this->lineItems as $item) {
            $item->resetRequirements($om);
        }
    }

    /**
     * Returns the number of short shipments that have been made for this
     * order.
     *
     * @return int
     */
    public function getNumShortShipments()
    {
        $numShipments = count($this->getInvoices());
        $numShort = $this->isCompleted() ? $numShipments - 1 : $numShipments;
        return max($numShort, 0);
    }

    /**
     * Returns all invoices for this order.
     *
     * @return DebtorInvoice[]
     */
    public function getInvoices()
    {
        return $this->invoices->toArray();
    }

    /**
     * Returns all credits, of any type, against this order.
     *
     * @return DebtorCredit[]
     */
    public function getAllCredits()
    {
        return $this->creditAllocations->map(function (OrderAllocation $alloc) {
            return $alloc->getCredit();
        })->toArray();
    }

    public function addInvoice(DebtorInvoice $trans)
    {
        if (!$this->invoices->contains($trans)) {
            $this->invoices[] = $trans;
        }
    }

    /** @return Shipper */
    public function getShipper()
    {
        return $this->shipper;
    }

    public function setShipper(Shipper $shipper)
    {
        if (!$shipper->equals($this->shipper)) {
            $this->shipper = $shipper;
            $this->shipmentType = '';
        }
    }

    /**
     * Return the shipping method selected for this sales order, or null
     * if no method is selected yet.
     *
     * @return ShippingMethod|null
     */
    public function getShippingMethod()
    {
        $shipper = $this->getShipper();
        if (!$this->shipmentType) return null;
        return $shipper->getShippingMethod($this->shipmentType);
    }

    public function setShippingMethod(ShippingMethod $method = null)
    {
        if ($method) {
            $this->shipper = $method->getShipper();
        }
        $this->shipmentType = $method ? $method->getCode() : '';
    }

    public function getShippingPrice()
    {
        return (float) $this->shippingPrice;
    }

    /**
     * @param float $price
     *  The price that the customer pays for shipping.
     */
    public function setShippingPrice($price)
    {
        $this->shippingPrice = $price;
    }

    /**
     * Uses $factory to calculate and update the shipping price of this order.
     */
    public function updateShippingPrice(ShipmentFactory $factory)
    {
        if (!$this->containsShippableItems()) {
            $this->setShippingPrice(0);
            return;
        }

        $method = $this->getShippingMethod();
        if (!$method) {
            throw new IllegalStateException("$this has no shipping method selected");
        }
        $shipment = $factory->createShipment($this, $method);
        $factory->refreshShippingCosts($shipment);
        $cost = $shipment->getShippingCost();
        $this->setShippingPrice($cost);
    }

    public function getTrackingNumber()
    {
        return ''; // Orders don't have tracking numbers.
    }

    /**
     * Returns the total amount invoiced, including taxes and shipping.
     *
     * @return float
     */
    public function getTotalAmountInvoiced()
    {
        $total = 0;
        foreach ($this->getInvoices() as $inv) {
            $total += $inv->getTotalAmount();
        }
        return $total;
    }

    /**
     * Returns the total dollar amount that has been paid so far against this
     * sales order.
     *
     * @return float
     */
    public function getTotalAmountPaid()
    {
        $total = 0;
        foreach ($this->creditAllocations as $alloc) {
            $total += $alloc->getAmount();
        }
        return $total;
    }

    /**
     * Since users can manually allocate credits/receipts to orders, we need
     * to validate the amounts.
     *
     * @Assert\Callback(groups={"orderAllocation"})
     */
    public function validateTotalAmountPaid(ExecutionContextInterface $context)
    {
        if ($this->getTotalAmountPaid() > $this->getTotalPrice()) {
            $context->addViolation("Cannot allocate more than the total price of $this.");
        }
    }

    /**
     * @return float The total amount remaining to be paid.
     */
    public function getTotalAmountOutstanding()
    {
        return $this->getTotalPrice() - $this->getTotalAmountPaid();
    }

    /**
     * Determines how much money the customer owes on this order.
     *
     * @return float
     */
    public function getAmountOwedByCustomer()
    {
        $amtInvoiced = $this->getTotalAmountInvoiced();
        $amtPaid = $this->getTotalAmountPaid();
        $amtOwed = $amtInvoiced - $amtPaid;
        return max($amtOwed, 0.0);
    }

    /**
     * Returns the amount that has already been invoiced for shipping.
     *
     * @return float
     */
    public function getAmountInvoicedForShipping()
    {
        $total = 0.0;
        foreach ($this->getInvoices() as $inv) {
            $total += $inv->getShippingAmount();
        }
        return $total;
    }

    /** @deprecated The branchCode field is deprecated */
    public function getBranchCode()
    {
        return $this->customerBranch->getBranchCode();
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function getCompanyName()
    {
        return $this->deliveryCompany ?
            $this->deliveryCompany :
            $this->customerBranch->getBranchName();
    }

    /** @deprecated Use getShippingAddress instead */
    public function getStreet1(): string
    {
        return $this->shippingAddress->getStreet1();
    }

    /** @deprecated Use getShippingAddress instead */
    public function getStreet2(): string
    {
        return $this->shippingAddress->getStreet2();
    }

    /** @deprecated Use getShippingAddress instead */
    public function getMailStop(): string
    {
        return $this->shippingAddress->getMailStop();
    }

    /** @deprecated Use getShippingAddress instead */
    public function getCity(): string
    {
        return $this->shippingAddress->getCity();
    }

    /** @deprecated Use getShippingAddress instead */
    public function getStateCode(): string
    {
        return $this->shippingAddress->getStateCode();
    }

    /** @deprecated Use getShippingAddress instead */
    public function getStateName(): string
    {
        return $this->shippingAddress->getStateName();
    }

    /** @deprecated Use getShippingAddress instead */
    public function getCountryCode(): string
    {
        return $this->shippingAddress->getCountryCode();
    }

    /** @deprecated Use getShippingAddress instead */
    public function getCountry()
    {
        return $this->shippingAddress->getCountry();
    }

    /** @deprecated Use getShippingAddress instead */
    public function getCountryName(): string
    {
        return $this->shippingAddress->getCountryName();
    }

    /** @deprecated Use getShippingAddress instead */
    public function getPostalCode(): string
    {
        return $this->shippingAddress->getPostalCode();
    }

    public function addCardTransaction(CardTransaction $cardTrans)
    {
        if (!$this->cardTransactions->contains($cardTrans)) {
            $cardTrans->setSalesOrder($this);
            $this->cardTransactions[] = $cardTrans;
        }
    }

    /**
     * @return CardTransaction[]
     */
    public function getCardTransactions()
    {
        return $this->cardTransactions->toArray();
    }

    /**
     * Returns the first uncaptured credit card authorization for this
     * order, if there is one.
     *
     * @return CardTransaction|null
     */
    public function getCardAuthorization()
    {
        foreach ($this->cardTransactions as $cardTrans) {
            if ($cardTrans->canBeCaptured()) {
                return $cardTrans;
            }
        }
        return null;
    }

    /**
     * Returns a list of all credit card receipts that have been made against
     * this sales order.
     *
     * @return CardTransaction[]
     */
    public function getCreditCardReceipts()
    {
        return $this->cardTransactions->filter(function (CardTransaction $ct) {
            return $ct->isType(SystemType::RECEIPT);
        })->toArray();
    }

    /**
     * @return OrderAllocation[]
     */
    public function getCreditAllocations()
    {
        return $this->creditAllocations->toArray();
    }

    public function addCreditAllocation(OrderAllocation $alloc)
    {
        $this->creditAllocations[] = $alloc;
    }

    public function removeCreditAllocation(OrderAllocation $alloc)
    {
        $this->creditAllocations->removeElement($alloc);
    }

    public function getBillingCompany()
    {
        return $this->customerBranch->getBranchName();
    }

    public function setBillingName($name)
    {
        $this->billingName = trim($name);
    }

    public function getBillingName()
    {
        return $this->billingName;
    }

    /** @return Address */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(PostalAddress $addr)
    {
        $this->billingAddress = Address::fromAddress($addr);
    }

    /** @return Address */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(PostalAddress $addr)
    {
        $this->shippingAddress = Address::fromAddress($addr);
    }

    /** @return Address */
    public function getDeliveryAddress()
    {
        return $this->getShippingAddress();
    }

    public function setDeliveryAddress(PostalAddress $addr)
    {
        $this->setShippingAddress($addr);
    }

    public function getDeliveryCompany()
    {
        return $this->deliveryCompany;
    }

    public function setDeliveryCompany($name)
    {
        $this->deliveryCompany = trim($name);
        return $this;
    }

    public function getDeliveryName()
    {
        return $this->deliveryName;
    }

    public function getDeliveryFirstName()
    {
        return $this->extractFirstName($this->getDeliveryName());
    }

    public function getDeliveryLastName()
    {
        return $this->extractLastName($this->getDeliveryName());
    }

    public function setDeliveryName($name)
    {
        $this->deliveryName = trim($name);
        return $this;
    }

    public function getCustomerReference(): string
    {
        return $this->customerReference;
    }

    public function setCustomerReference($ref): self
    {
        $this->customerReference = trim($ref);
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerTaxId()
    {
        return $this->customerTaxId;
    }

    public function setCustomerTaxId($taxId)
    {
        $this->customerTaxId = trim($taxId);
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->getCustomer()->getCurrency();
    }

    /**
     * @return float
     */
    public function getCurrencyRate()
    {
        $currency = $this->getCurrency();
        return $currency->getRate();
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function approveToShip(EventDispatcherInterface $dispatcher)
    {
        assertion(!$this->isQuotation());
        assertion(!$this->doNotShip());
        $this->dateToShip = new DateTime();
        $event = new SalesOrderEvent($this);
        $dispatcher->dispatch(SalesEvents::APPROVED_TO_SHIP, $event);
    }

    public function unapproveToShip()
    {
        $this->dateToShip = null;
    }

    public function isApprovedToShip()
    {
        return null !== $this->dateToShip;
    }

    public function isDueToShip()
    {
        $now = new DateTime();
        return $this->isApprovedToShip() && ($now >= $this->dateToShip);
    }

    /**
     * Returns the date when the order is supposed to ship.
     *
     * @return DateTime
     */
    public function getDateToShip()
    {
        return $this->dateToShip ? clone $this->dateToShip : null;
    }

    /**
     * Returns the date by which the customer requested the order be
     * delivered.
     *
     * @return DateTime
     */
    public function getDeliveryDate()
    {
        return $this->deliveryDate ? clone $this->deliveryDate : null;
    }

    public function setDeliveryDate(DateTime $date = null)
    {
        $this->deliveryDate = $date;
    }

    /**
     * Returns the dollar amount that is usually required for the deposit.
     * @return int
     */
    public function getDefaultDepositAmount()
    {
        /* Round to nearest multiple of 10 */
        return round($this->getTotalPrice() * self::DEPOSIT_FRACTION, -1);
    }

    /**
     * Returns the dollar amount that is required for a deposit.
     *
     * @return float
     */
    public function getDepositAmount()
    {
        return (float) $this->depositAmount;
    }

    public function setDepositAmount($amount)
    {
        $this->depositAmount = $amount;
        return $this;
    }

    /**
     * How much of the deposit the customer still needs to pay.
     */
    public function getDepositAmountOutstanding()
    {
        return max(0, $this->getDepositAmount() - $this->getTotalAmountPaid());
    }

    public function setPriority()
    {
        $this->priority = 1;
    }

    public function removePriority()
    {
        $this->priority = 0;
    }

    public function hasPriority()
    {
        return $this->priority;
    }

    /**
     * Implements Mailable
     * @return string
     */
    public function getName()
    {
        return $this->getContactName();
    }

    /**
     * Implements Mailable
     * @return string
     */
    public function getEmail()
    {
        return $this->contactEmail;
    }

    public function setEmail($email)
    {
        $this->contactEmail = trim($email);
        return $this;
    }

    /**
     * @return string
     */
    public function getReasonForShipping()
    {
        return $this->reasonForShipping;
    }

    /**
     * @param string $reason
     */
    public function setReasonForShipping($reason)
    {
        $this->reasonForShipping = trim($reason);
    }

    /**
     * Returns the OrderNo of this location.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function getSubtotalPrice()
    {
        $total = 0;
        foreach ($this->lineItems as $item) {
            $total += $item->getExtendedPrice();
        }
        return $total;
    }

    public function getSubtotalValue()
    {
        $total = 0;
        foreach ($this->getTangibleLineItems() as $item) {
            $total += $item->getExtendedValue();
        }
        return $total;
    }

    /** @return DateTime */
    public function getDateOrdered()
    {
        return clone $this->dateOrdered;
    }

    public function setDateOrdered(DateTime $date)
    {
        $this->dateOrdered = clone $date;
    }

    /** @deprecated  Use getDateOrdered() instead */
    public function getOrderDate()
    {
        return $this->getDateOrdered();
    }

    /** @deprecated  Use setDateOrdered() instead */
    public function setOrderDate(DateTime $date)
    {
        $this->setDateOrdered($date);
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getTargetShipDate()
    {
        return $this->targetShipDate ? clone $this->targetShipDate : null;
    }

    public function hasTargetShipDate()
    {
        return null !== $this->targetShipDate;
    }

    /**
     * @param DateTime|null $date
     */
    public function setTargetShipDate(DateTime $date = null)
    {
        $this->targetShipDate = $date ? clone $date : null;
        if ($this->targetShipDate) {
            $this->targetShipDate->setTime(TargetShipDateCalculator::SAME_DAY_CUTOFF, 0, 0);
        }
    }

    /**
     * True if this order has missed its target ship date.
     *
     * @param \DateTimeInterface $asOf For unit testing.
     * @return bool
     */
    public function isOverdue(\DateTimeInterface $asOf = null)
    {
        if ($this->isCompleted()) {
            return false;
        }
        if (null === $this->targetShipDate) {
            return false;
        }
        if (null === $asOf) {
            $asOf = new DateTime(); // now
        }
        return $this->targetShipDate < $asOf;
    }

    public function getOrderNumber()
    {
        return $this->id;
    }

    public function __toString()
    {
        return sprintf('%s %s',
            $this->getSalesStageLabel(),
            $this->id);
    }

    /**
     * Eg, "quotation 1234 (PO 0987-B)"
     */
    public function getSummaryWithCustomerRef(): string
    {
        $ref = $this->customerReference;
        $ref = $ref ? " ($ref)" : '';
        return "{$this}{$ref}";
    }

    public function getContactName()
    {
        return $this->customerBranch->getContactName();
    }

    public function getContactFirstName()
    {
        return $this->extractFirstName($this->getContactName());
    }

    private function extractFirstName($fullName)
    {
        $parts = explode(" ", $fullName);
        array_pop($parts);
        return join(" ", $parts);
    }

    public function getContactLastName()
    {
        return $this->extractLastName($this->getContactName());
    }

    private function extractLastName($fullName)
    {
        $parts = explode(" ", $fullName);
        return array_pop($parts);
    }

    public function getContactPhone()
    {
        return $this->contactPhone;
    }

    public function isTaxExempt()
    {
        return $this->customerBranch->isTaxExempt();
    }

    /**
     * @return float
     */
    public function getTaxAmount()
    {
        $calculator = new PriceCalculator(SalesOrderDetail::EXT_PRECISION);
        return $calculator->calculateTaxAmount($this);
    }

    /**
     * @return Facility
     */
    public function getShipFromFacility()
    {
        return $this->shipFromFacility;
    }

    public function setShipFromFacility(Facility $facility)
    {
        $this->shipFromFacility = $facility;
        return $this;
    }

    /**
     * @deprecated use getShipFromFacility instead
     */
    public function getLocation()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getShipFromFacility();
    }

    /**
     * @deprecated use setShipFromFacility instead
     * @return SalesOrder
     */
    public function setLocation(Facility $loc)
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->setShipFromFacility($loc);
    }

    /**
     * @return SalesType
     */
    public function getSalesType()
    {
        return $this->salesType;
    }

    /** @return SalesArea */
    public function getSalesArea()
    {
        return $this->customerBranch->getSalesArea();
    }

    /** @return Salesman */
    public function getSalesman()
    {
        return $this->customerBranch->getSalesman();
    }

    /**
     * Returns the total price of the order, including all line items,
     * discounts, freight charges, and taxes.
     *
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->getSubtotalPrice() +
            $this->getShippingPrice() +
            $this->getTaxAmount();
    }

    /**
     * Returns the total weight (in kilograms) of this sales order.
     *
     * @return float
     */
    public function getTotalWeight()
    {
        $total = 0;
        foreach ($this->lineItems as $item) {
            $total += $item->getTotalWeight();
        }
        return $total;
    }

    /**
     * Returns true if any of the line items for this order have been
     * invoiced.
     *
     * @return bool
     */
    public function hasBeenInvoiced()
    {
        foreach ($this->lineItems as $item) {
            if ($item->getQtyInvoiced() > 0) return true;
        }
        return false;
    }

    /**
     * @return bool
     *  True if the customer's credit card has been charged for this sales
     *  order.
     */
    public function hasCreditCardReceipts()
    {
        return count($this->getCreditCardReceipts()) > 0;
    }

    /**
     * Returns true if this sales order is completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        /* A sales order is completed if all of its line items are
         * completed. */

        /* If it has no line items, then it is probably being created.
         * Assume it is not completed.  */
        if (!count($this->lineItems)) {
            return false;
        }
        foreach ($this->lineItems as $item) {
            if (!$item->isCompleted()) {
                return false;
            }
        }
        return true;
    }

    public function isFullyPaid()
    {
        return $this->getTotalAmountPaid() >= $this->getTotalPrice();
    }

    public function isNew()
    {
        return !$this->getId();
    }

    public function isOnlineSale()
    {
        return $this->salesType->isOnlineSale();
    }

    public function isDirectSale()
    {
        return $this->salesType->isDirectSale();
    }

    /**
     * @param SalesType|string $type
     * @return bool
     */
    public function isSalesType($type)
    {
        return $this->salesType->equals($type);
    }

    public function isQuotation()
    {
        return in_array($this->salesStage, [
            self::QUOTATION,
            self::BUDGET,
        ]);
    }

    public function getSalesStage()
    {
        return $this->salesStage;
    }

    public function getSalesStageLabel()
    {
        return $this->isQuotation() ? 'quotation' : 'sales order';
    }

    public function setSalesStage($stage)
    {
        $this->salesStage = $stage;
        return $this;
    }

    /**
     * @return string[]
     */
    public static function getValidStages()
    {
        $all = [
            self::ORDER,
            self::QUOTATION,
            self::BUDGET,
        ];
        return array_combine($all, $all);
    }

    public function convertToOrder()
    {
        $this->setSalesStage(self::ORDER);
    }

    public function isReplacement()
    {
        return $this->salesType->isReplacement();
    }

    public function setSalesType(SalesType $type)
    {
        $this->salesType = $type;
        return $this;
    }

    public function setContactPhone($phoneNo)
    {
        $this->contactPhone = $phoneNo;
        return $this;
    }

    public function setComments($comments): self
    {
        $this->comments = $comments;
        return $this;
    }

    /** @deprecated */
    public function setSalesTaxes($tax)
    {
        $this->salesTaxes = $tax;
        return $this;
    }

    /**
     * @param float $rate The tax rate (1.0 = 100%)
     * @param bool $taxShipping Whether shipping charges should be taxed
     */
    public function setTaxRate($rate, $taxShipping)
    {
        foreach ($this->lineItems as $item) {
            $item->setTaxRate($rate);
        }
        // TODO: allow shipping to be taxed.
    }

    /** @return User */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $user)
    {
        $this->createdBy = $user;
    }

    public function getSourceId()
    {
        return $this->sourceID;
    }

    public function setSourceId($sourceId)
    {
        $this->sourceID = (int) $sourceId;
    }

    public function getDatePrinted()
    {
        return $this->datePrinted;
    }

    public function updateDatePrinted()
    {
        $this->datePrinted = new DateTime();
    }

    public function shipperPaysDuties()
    {
        return $this->getCustomer()->isInternalCustomer();
    }

    public function getReasonNotToShip(): string
    {
        return $this->reasonNotToShip;
    }

    public function setReasonNotToShip($reason): self
    {
        $this->reasonNotToShip = trim($reason);
        if ($this->doNotShip()) {
            $this->unapproveToShip();
        }
        return $this;
    }

    public function doNotShip(): bool
    {
        return (bool) $this->reasonNotToShip;
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
}
