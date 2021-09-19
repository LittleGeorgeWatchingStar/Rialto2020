<?php

namespace Rialto\Sales\Order;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Money;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Allocation\Status\ConsumerStatus;
use Rialto\Allocation\Status\DetailedRequirementStatus;
use Rialto\Entity\RialtoEntity;
use Rialto\IllegalStateException;
use Rialto\Manufacturing\Bom\Bom;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Validator\CustomizationMatchesVersion;
use Rialto\Sales\Invoice\InvoiceableOrderItem;
use Rialto\Sales\Order\Allocation\Requirement;
use Rialto\Sales\Price\PriceCalculator;
use Rialto\Sales\Shipping\ShippableOrderItem;
use Rialto\Shipping\Export\Document\ElectronicExportInformation;
use Rialto\Shipping\Shipment\Document\ShipmentInvoice;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\AssemblyStockItem;
use Rialto\Stock\Item\CompositeStockItem;
use Rialto\Stock\Item\DummyStockItem;
use Rialto\Stock\Item\PurchasedStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A line item from a sales order.
 *
 * @CustomizationMatchesVersion
 * @UniqueEntity(fields={"salesOrder", "stockItem", "customization"},
 *   message="A matching item already exists in this order.")
 */
class SalesOrderDetail implements
    RialtoEntity,
    ShippableOrderItem,
    InvoiceableOrderItem,
    TaxableOrderItem,
    StockConsumer
{
    /**
     * Round unit prices to this many places.
     */
    const UNIT_PRECISION = 4;

    /**
     * Round extended prices to this many places.
     */
    const EXT_PRECISION = 2;

    private $id;

    /**
     * If this item was created by an external shopping cart application,
     * (eg Shopify), this field stores the ID assigned to this item by
     * that system.
     * @var string
     */
    private $sourceID = '';

    /**
     * The SKU or part no. the customer uses to refer to this item.
     *
     * @Assert\Length(max=50,
     *     maxMessage="Customer part no. cannot be longer than {{ limit }} characters.")
     */
    private $customerPartNo = '';

    private $qtyInvoiced = 0;

    /**
     * @var float
     * @Assert\Type(type="numeric", message="Unit price should be {{ type }}.")
     * @Assert\Range(min=0, minMessage="Unit price cannot be negative.")
     */
    private $baseUnitPrice = 0.0;

    /**
     * This value can be calculated quickly, but we store it in the database
     * for use in SQL queries.
     *
     * @var float
     */
    private $finalUnitPrice = null;

    /**
     * @Assert\Type(type="numeric", message="Quantity should be a {{ type }}.")
     * @Assert\Range(min=1, minMessage="Quantity must be positive.")
     */
    private $qtyOrdered = 1;

    /**
     * @Assert\Type(type="numeric", message="Discount rate should be {{ type }}.")
     * @Assert\Range(
     *      min=0, max=1,
     *      minMessage="Discount rate cannot be less than zero.",
     *      maxMessage="Discount rate cannot be greater than 100%."
     * )
     */
    private $discountRate = 0;
    private $dateDispatched;
    private $completed = false;

    /**
     * @Assert\Type(type="numeric", message="Sales tax rate should be a {{ type }}.")
     * @Assert\Range(min=0, minMessage="Sales tax rate cannot be less than zero.")
     */
    private $taxRate = 0;

    /**
     * @var SalesOrder
     * @Assert\NotNull
     */
    private $salesOrder;

    /**
     * @var StockItem
     * @Assert\NotNull
     */
    private $stockItem;

    /** @Assert\NotNull */
    private $discountAccount;
    private $version = Version::ANY;

    /** @var Customization */
    private $customization;

    /**
     * Whether the customization price adjustment should be reflected in
     * the final unit price.
     *
     * This field is primarily here for legacy reasons, to prevent the price of
     * pre-existing orders from changing when this feature was introduced.
     * @var boolean
     */
    private $chargeForCustomizations = true;

    /** @var Requirement[] */
    private $requirements;

    private $dirtyRequirements = false;

    /**
     * @param GLAccount $discountAccount
     */
    public function __construct(StockItem $item, GLAccount $discountAccount)
    {
        $this->stockItem = $item;
        $this->discountAccount = $discountAccount;
        $this->requirements = new ArrayCollection();
    }

    /**
     * Increases the quantity invoiced by $qty.
     *
     * @param int $qty
     */
    public function addQuantityInvoiced($qty)
    {
        $uninvoiced = $this->qtyOrdered - $this->qtyInvoiced;
        assertion($qty <= $uninvoiced);
        $this->qtyInvoiced += $qty;
        $this->updateCompleted();
    }

    /**
     * Marks this line item as completed, regardless of whether it has
     * been entirely invoiced.
     */
    public function close()
    {
        if ( $this->requiresAllocation() ) {
            $this->closeAllocations();
        }
        $this->completed = true;
    }

    private function closeAllocations()
    {
        foreach ( $this->requirements as $req ) {
            $req->closeAllocations();
        }
    }

    public function __clone()
    {
        if (! $this->id ) {
            return; // Required by Doctrine
        }
        $this->id = null;
        $this->salesOrder = null;
        $this->qtyInvoiced = 0;
        $this->dateDispatched = null;
        $this->completed = false;
        $this->requirements = new ArrayCollection();
    }

    /** @return StockAllocation[] */
    public function getAllocations()
    {
        $this->initRequirements();
        $allocs = [];
        foreach ( $this->requirements as $requirement ) {
            $allocs = array_merge($allocs, $requirement->getAllocations());
        }
        return $allocs;
    }

    /** @return Requirement[] */
    public function getRequirements()
    {
        $this->initRequirements();
        return $this->requirements->getValues();
    }

    private function initRequirements()
    {
        if (! $this->requiresAllocation() ) {
            return;
        } elseif ( count($this->requirements) > 0 ) {
            return;
        } elseif ( $this->isAssembly() ) {
            $this->initAssemblyRequirements();
        } else {
            $this->requirements[] = Requirement::fromPhysicalItem($this);
        }
    }

    /**
     * We have to explicitly remove any stale requirements in order to
     * prevent ORM synchronization problems.
     */
    public function resetRequirements(ObjectManager $om)
    {
        if (! $this->dirtyRequirements) {
            return;
        }
        foreach ($this->requirements as $req) {
            $om->remove($req);
        }
        $this->clearRequirements();
    }

    /**
     * Force the regeneration of all requirements.
     */
    public function clearRequirements()
    {
        $this->requirements->clear();
        $this->dirtyRequirements = false;
    }

    private function initAssemblyRequirements()
    {
        foreach ( $this->getBom() as $bomItem ) {
            $this->requirements[] = Requirement::fromAssembly($this, $bomItem);
        }
    }

    /**
     * COGS = cost of goods sold
     * @return GLAccount
     */
    public function getCogsAccount()
    {
        $salesArea = $this->salesOrder->getSalesArea();
        $salesType = $this->salesOrder->getSalesType();
        $stockCat = $this->stockItem->getCategory();

        return GLAccount::fetchCogsAccount($salesArea, $stockCat, $salesType);
    }

    public function getStockAccount()
    {
        return $this->stockItem->getStockAccount();
    }

    /** @return GLAccount */
    public function getSalesAccount()
    {
        $salesArea = $this->salesOrder->getSalesArea();
        $salesType = $this->salesOrder->getSalesType();
        $stockCat = $this->stockItem->getCategory();

        return GLAccount::fetchSalesAccount($salesArea, $stockCat, $salesType);
    }

    /**
     * @return bool
     */
    public function hasCustomizations()
    {
        return (bool) $this->customization;
    }

    /**
     * @return Customization
     */
    public function getCustomization()
    {
        return $this->customization;
    }

    /**
     * @return SalesOrderDetail fluent interface.
     */
    public function setCustomization(Customization $cust=null)
    {
        if (! Customization::areEqual($this->customization, $cust) ) {
            $this->customization = $cust;
            $this->recalculateFinalPrice();
            $this->setDirtyRequirements();
        }
        return $this;
    }

    private function setDirtyRequirements()
    {
        $this->dirtyRequirements = true;
    }

    public function isChargeForCustomizations()
    {
        return $this->chargeForCustomizations;
    }

    public function setChargeForCustomizations($charge)
    {
        $this->chargeForCustomizations = (bool) $charge;
        $this->recalculateFinalPrice();
    }

    /**
     * @return GLAccount|null
     */
    public function getDiscountAccount()
    {
        return $this->discountAccount;
    }

    public function setDiscountAccount(GLAccount $account)
    {
        $this->discountAccount = $account;
        return $this;
    }

    /**
     * @deprecated  Use getDiscountAccount instead.
     */
    public function getDiscountAccountId()
    {
        return $this->discountAccount->getId();
    }

    /**
     * Returns the discount for this item as a fraction between 0.0 and 1.0.
     * For example, a five percent discount is 0.05.
     *
     * @return float
     */
    public function getDiscountRate()
    {
        return (float) $this->discountRate;
    }

    /**
     * @param float $rate
     *  The discount rate as a fraction between 0.0 and 1.0.  For example,
     *  a five percent discount is 0.05.
     * @return SalesOrderDetail
     */
    public function setDiscountRate($rate)
    {
        $this->discountRate = $rate;
        $this->recalculateFinalPrice();
        return $this;
    }

    /**
     * @deprecated  Use getDiscountRate() instead.
     *
     * @return int
     */
    public function getDiscountPercentage()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return (int) ($this->discountRate * 100);
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSourceId()
    {
        return $this->sourceID;
    }

    /**
     * @param string $sourceID
     */
    public function setSourceId($sourceID)
    {
        $this->sourceID = trim($sourceID);
    }

    /**
     * @return Facility
     */
    public function getLocation()
    {
        return $this->salesOrder->getShipFromFacility();
    }

    /**
     * @return int
     */
    public function getOrderNumber()
    {
        return $this->salesOrder->getId();
    }

    /**
     * @return SalesOrder
     */
    public function getSalesOrder()
    {
        return $this->salesOrder;
    }

    public function setSalesOrder(SalesOrder $order)
    {
        $this->salesOrder = $order;
    }

    public function isForSameOrder(StockConsumer $other)
    {
        return ( $other instanceof SalesOrderDetail ) &&
            ($this->getOrderNumber() == $other->getOrderNumber());
    }

    public function setQtyOrdered($qty)
    {
        $this->qtyOrdered = $qty;
        $this->updateCompleted();
        return $this;
    }

    public function addQtyOrdered($diff)
    {
        $this->qtyOrdered += $diff;
        $this->updateCompleted();
    }

    /** @deprecated
     * use setQtyOrdered instead
     */
    public function setQuantity($qty)
    {
        trigger_error("Call to deprecated method ". __METHOD__, E_USER_DEPRECATED);
        return $this->setQtyOrdered($qty);
    }

    public function getQtyOrdered()
    {
        return $this->qtyOrdered;
    }

    /** @deprecated */
    public function getQuantity()
    {
        trigger_error("Call to deprecated method ". __METHOD__, E_USER_DEPRECATED);
        return $this->getQtyOrdered();
    }

    /** @Assert\Callback */
    public function validateQuantity(ExecutionContextInterface $context)
    {
        if ( $this->qtyOrdered < $this->qtyInvoiced ) {
            $context->buildViolation("You cannot order less than have been invoiced.")
                ->atPath('qtyOrdered')
                ->addViolation();
        }
    }

    public function getQtyInvoiced()
    {
        return $this->qtyInvoiced;
    }

    public function getQtyToShip()
    {
        if ( $this->isDummy() ) {
            return $this->qtyOrdered - $this->qtyInvoiced;
        }

        $status = new ConsumerStatus($this);
        return $status->getQtyAtLocation() - $status->getQtyDelivered();
    }

    public function getStandardCost()
    {
        return $this->stockItem->getStandardCost();
    }

    public function getSku()
    {
        return $this->stockItem->getSku();
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getCustomerPartNo(): string
    {
        return $this->customerPartNo;
    }

    public function setCustomerPartNo($partNo)
    {
        $this->customerPartNo = trim($partNo);
    }

    public function __toString()
    {
        return sprintf('%s in %s',
            $this->stockItem,
            $this->salesOrder);
    }

    /**
     * @return StockItem
     */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    private function isItem($stockCode)
    {
        if ($stockCode instanceof Item) {
            $stockCode = $stockCode->getSku();
        }
        return $this->getSku() == $stockCode;
    }

    /**
     * If this item matches the given item/stock code and customization.
     *
     * @param string|Item $stockCode
     * @param Customization|null $c
     * @return bool
     */
    public function isMatch($stockCode, Customization $c = null)
    {
        return $this->isItem($stockCode)
            && Customization::areEqual($this->customization, $c);
    }

    /**
     * Returns the sales tax rate for this item as a fraction between 0.0 and
     * 1.0. For example, a five percent tax rate is 0.05.
     */
    public function getTaxRate(): float
    {
        return (float) $this->taxRate;
    }

    public function getTotalQtyUndelivered()
    {
        return $this->getTotalQtyOrdered() - $this->getQtyInvoiced();
    }

    public function getTotalQtyOrdered()
    {
        return $this->getQtyOrdered();
    }

    public function getTotalWeight()
    {
        return $this->getUnitWeight() * $this->getQtyOrdered();
    }

    public function setBaseUnitPrice($price)
    {
        $this->baseUnitPrice = (float) $price;
        $this->recalculateFinalPrice();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBaseUnitPrice()
    {
        return Money::round($this->baseUnitPrice, self::UNIT_PRECISION);
    }

    /**
     * The adjustment that the customization has on the
     * unit price.
     * @return float
     */
    public function getPriceAdjustment()
    {
        if ( $this->chargeForCustomizations && $this->customization ) {
            return $this->customization->getPriceAdjustment();
        }
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function getFinalUnitPrice()
    {
        if (null === $this->finalUnitPrice) {
            $this->recalculateFinalPrice();
        }
        return $this->finalUnitPrice;
    }

    private function recalculateFinalPrice()
    {
        $calculator = new PriceCalculator(self::UNIT_PRECISION);
        $this->finalUnitPrice = $calculator->calculateFinalUnitPrice($this);
    }

    public function getExtendedPrice()
    {
        return Money::round(
            $this->getFinalUnitPrice() * $this->getQtyOrdered(),
            self::EXT_PRECISION);
    }

    public function getUnitValue()
    {
        return $this->getFinalUnitPrice() ?: $this->getStandardCost();
    }

    public function getExtendedValue()
    {
        return $this->getUnitValue() * $this->getQtyOrdered();
    }

    public function getUnitWeight()
    {
        return $this->stockItem->getWeight();
    }

    /** @return Version */
    public function getVersion()
    {
        $version = new Version($this->version);
        return $version->isSpecified() ?
            $this->stockItem->getVersion($version) :
            $version;
    }

    public function setVersion(Version $version)
    {
        if ( $version->equals($this->version) ) {
            return;
        }
        /* Any allocations will become invalid if we change the version. */
        if ( $this->id && $this->requiresAllocation() ) {
            $this->closeAllocations();
        }
        $this->version = $version;
        $this->setDirtyRequirements();
    }

    public function getFullSku()
    {
        return $this->getSku()
            . $this->getVersion()->getStockCodeSuffix()
            . Customization::getStockCodeSuffix($this->customization);
    }

    /** @deprecated */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    /**
     * @Assert\NotBlank
     */
    public function getDescription()
    {
        return $this->stockItem->getDescription();
    }

    /** @return string */
    public function getHarmonizationCode()
    {
        return (string) $this->stockItem->getHarmonizationCode();
    }

    public function getEccnCode()
    {
        return $this->stockItem ? $this->stockItem->getEccnCode() : '';
    }

    public function getRoHS()
    {
        return $this->stockItem->getRoHS();
    }

    public function getCountryOfOrigin()
    {
        return $this->stockItem->getCountryOfOrigin();
    }

    public function getWeight()
    {
        return $this->stockItem->getWeight();
    }

    public function hasWeight()
    {
        return $this->stockItem->hasWeight();
    }

    /** @Assert\Callback */
    public function validateWeight(ExecutionContextInterface $context)
    {
        if ( $this->hasWeight() && ($this->getWeight() <= 0) ) {
            $item = $this->stockItem;
            $context->addViolation("$item has no weight.");
        }
    }

    /** @Assert\Callback */
    public function validateCountryOfOrigin(ExecutionContextInterface $context)
    {
        $internationalInvoice = new ShipmentInvoice($this->salesOrder);
        if (! $internationalInvoice->isRequired() ) {
            return;
        }
        if (! $this->getCountryOfOrigin() ) {
            $item = $this->stockItem;
            $context->addViolation("$item has no country of origin.");
        }
    }

    /** @Assert\Callback */
    public function validateHarmonizationCode(ExecutionContextInterface $context)
    {
        $eei = new ElectronicExportInformation($this->salesOrder);
        if (! $eei->isRequired() ) {
            return;
        }
        if (! $this->getHarmonizationCode() ) {
            $item = $this->stockItem;
            $context->addViolation("$item has no harmonization code.");
        }
    }

    /** @return bool */
    public function isControlled()
    {
        return $this->stockItem->isControlled();
    }

    /** @return bool */
    public function isAssembly()
    {
        return $this->stockItem instanceof AssemblyStockItem;
    }

    /** @return bool */
    public function isDummy()
    {
        return $this->stockItem instanceof DummyStockItem;
    }

    /** @return bool */
    public function isPurchased()
    {
        return $this->stockItem instanceof PurchasedStockItem;
    }

    public function hasSubcomponents()
    {
        return $this->stockItem instanceof CompositeStockItem;
    }

    public function getBom(): Bom
    {
        if (! $this->hasSubcomponents() ) {
            throw new IllegalStateException("{$this->stockItem} has no BOM");
        }
        $version = $this->getVersion();
        if (! $version->isSpecified() ) {
            $version = $this->stockItem->getShippingVersion();
        }
        return $this->stockItem->getBom($version);
    }

    private function updateCompleted()
    {
        if ($this->qtyInvoiced >= $this->qtyOrdered) {
            $this->completed = true;
        } else {
            $this->completed = false;
        }
    }

    public function isCompleted()
    {
        return (bool) $this->completed;
    }

    public function isCancelled()
    {
        return $this->isCompleted() && ($this->qtyInvoiced == 0);
    }

    public function isInvoiced()
    {
        return $this->qtyInvoiced > 0;
    }

    public function isNew()
    {
        return ! $this->getSku();
    }

    /**
     * @return bool
     *  True if this line item requires that stock be allocated to it.
     */
    public function requiresAllocation()
    {
        return $this->stockItem->isRequestable();
    }

    public function setTaxRate($rate)
    {
        $this->taxRate = $rate;
        return $this;
    }

    /**
     * The date, if any, by which this consumer needs to be fulfilled.
     * @return DateTime|null
     */
    public function getDueDate()
    {
        return $this->salesOrder->getDeliveryDate();
    }

    public function getAllocationStatus(): DetailedRequirementStatus
    {
        return DetailedRequirementStatus::forConsumer($this);
    }
}

