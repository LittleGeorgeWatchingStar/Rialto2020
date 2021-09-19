<?php

namespace Rialto\Sales\Invoice;

use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Accounting\InvoiceItem;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Status\ConsumerStatus;
use Rialto\Entity\RialtoEntity;
use Rialto\Manufacturing\Bom\Bom;
use Rialto\Sales\Order\Allocation\Requirement;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Sales\Order\TaxableOrderItem;
use Rialto\Sales\Price\PriceCalculator;
use Rialto\Sales\Shipping\ShippableOrderItem;
use Rialto\Stock\Consumption\StockConsumption;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * A line item in a sales invoice.
 *
 * Indicates the item and quantity invoiced.
 */
class SalesInvoiceItem implements
    RialtoEntity,
    InvoiceItem,
    ShippableOrderItem,
    InvoiceableOrderItem,
    TaxableOrderItem
{
    private $id;

    /** @var SalesInvoice */
    private $invoice;

    /** @var DebtorInvoice */
    private $debtorTrans;

    /** @var SalesOrderDetail */
    private $orderItem;

    /**
     * @Assert\Type(type="integer", message="Quantity to ship must be an {{ type }}.")
     * @Assert\Range(min=0, minMessage="Quantity to ship cannot be negative.")
     */
    private $qtyInvoiced;

    /**
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    private $unitPrice;

    /**
     * @Assert\Type(type="float", message="Tax rate must be a {{ type }}.")
     * @Assert\Range(min=0, minMessage="Tax rate cannot be negative.")
     */
    private $taxRate;

    /**
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0, max=1)
     */
    private $discountRate;

    private $closeOrder = false;

    /**
     * Only the SalesInvoice class should create new instances of this
     * class.
     */
    public function __construct(
        SalesInvoice $invoice,
        SalesOrderDetail $orderItem)
    {
        $this->invoice = $invoice;
        $this->orderItem = $orderItem;
        $this->unitPrice = $orderItem->getBaseUnitPrice();
        $this->taxRate = $orderItem->getTaxRate();
        $this->discountRate = $orderItem->getDiscountRate();
        $this->setQtyToShip($this->getMaxQtyAvailable());
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSourceId()
    {
        return $this->orderItem->getSourceId();
    }

    public function __toString()
    {
        return (string) $this->orderItem;
    }

    private function getMaxQtyAvailable()
    {
        $qty = $this->orderItem->getTotalQtyUndelivered();
        if ($this->orderItem->requiresAllocation()) {
            $status = new ConsumerStatus($this->orderItem);
            $qty = min($qty, $status->getQtyAtLocation());
        }

        return $qty;
    }

    /**
     * @return double
     */
    public function getCurrencyRate()
    {
        return $this->invoice->getCurrencyRate();
    }

    public function getDiscountRate()
    {
        return $this->discountRate;
    }

    public function getDiscountAccount()
    {
        return $this->orderItem->getDiscountAccount();
    }

    public function getStockAccount()
    {
        return $this->orderItem->getStockAccount();
    }

    public function getSalesAccount()
    {
        return $this->orderItem->getSalesAccount();
    }

    /**
     * @return SalesInvoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Returns the quantity being invoiced right now, NOT:
     *  a) the total quantity ordered, or
     *  b) the total quantity previously invoiced.
     *
     * @return int|double
     */
    public function getQtyToShip()
    {
        return $this->getQtyInvoiced();
    }

    public function setQtyToShip($qty)
    {
        $this->qtyInvoiced = $qty;
        return $this;
    }

    public function getQtyInvoiced()
    {
        return $this->qtyInvoiced;
    }

    /** @Assert\Callback */
    public function validateQtyToShip(ExecutionContextInterface $context)
    {
        $max = $this->getMaxQtyAvailable();
        if ($this->qtyInvoiced > $max) {
            $context->buildViolation(
                'Quantity of _item to ship (_quantity) cannot be more than ' .
                'the maximum quantity available (_max).',
                [
                    '_item' => $this->getSku(),
                    '_quantity' => $this->qtyInvoiced,
                    '_max' => $max
                ])
                ->atPath('qtyToShip')
                ->addViolation();
        }
    }

    /**
     * A set of "partial" allocations that indicates to the warehouse staff
     * which sources should be used and how many of each item.
     *
     * @return SalesInvoiceAllocation[]
     */
    public function getAllocations()
    {
        $allocations = [];
        foreach ($this->orderItem->getRequirements() as $req) {
            $remaining = $req->getUnitQtyNeeded() * $this->qtyInvoiced;
            foreach ($req->getAllocations() as $alloc) {
                /* @var $alloc StockAllocation */
                if ($remaining <= 0) {
                    break;
                }
                if (! $alloc->isWhereNeeded()) {
                    continue;
                }

                $qty = min($alloc->getQtyAllocated(), $remaining);
                if ($qty <= 0) {
                    continue;
                }

                $allocations[] = new SalesInvoiceAllocation($alloc, $qty);
                $remaining -= $qty;
            }
        }
        return $allocations;
    }

    /**
     * Returns the total quantity in the original order.
     *
     * @return int|double
     */
    public function getQtyOrdered()
    {
        return $this->orderItem->getQtyOrdered();
    }

    public function getQtyPreviouslyInvoiced()
    {
        return $this->orderItem->getQtyInvoiced();
    }

    /**
     * Returns the sales order detail to which this invoice line item
     * corresponds.
     *
     * @return SalesOrderDetail
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @return SalesOrder
     */
    public function getSalesOrder()
    {
        return $this->orderItem->getSalesOrder();
    }

    public function getBaseUnitPrice()
    {
        return $this->orderItem->getBaseUnitPrice();
    }

    public function getPriceAdjustment()
    {
        return $this->orderItem->getPriceAdjustment();
    }

    public function getFinalUnitPrice()
    {
        $calculator = new PriceCalculator(SalesOrderDetail::UNIT_PRECISION);
        return $calculator->calculateFinalUnitPrice($this);
    }

    public function getExtendedPrice()
    {
        return round(
            $this->getFinalUnitPrice() * $this->qtyInvoiced,
            SalesOrderDetail::EXT_PRECISION);
    }

    public function getUnitValue()
    {
        return $this->getFinalUnitPrice() ?: $this->getStandardCost();
    }

    public function getExtendedValue()
    {
        return $this->getUnitValue() * $this->qtyInvoiced;
    }

    public function getTaxRate()
    {
        return $this->taxRate;
    }

    public function setTaxRate($rate)
    {
        $this->taxRate = $rate;
        return $this;
    }

    public function getTotalStandardCost()
    {
        $stockItem = $this->getStockItem();
        return $stockItem->getStandardCost() * $this->qtyInvoiced;
    }

    public function getTotalWeight()
    {
        return $this->getUnitWeight() * $this->qtyInvoiced;
    }

    public function getUnitWeight()
    {
        return $this->orderItem->getUnitWeight();
    }

    public function getUnits()
    {
        $stockItem = $this->getStockItem();
        return $stockItem->getUnits();
    }

    public function getSku()
    {
        return $this->orderItem->getSku();
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @deprecated use getFullSku() instead */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    public function getFullSku()
    {
        return $this->orderItem->getFullSku();
    }

    public function getCustomerPartNo(): string
    {
        return $this->orderItem->getCustomerPartNo();
    }

    public function getStockItem()
    {
        return $this->orderItem->getStockItem();
    }

    public function getStandardCost()
    {
        return $this->orderItem->getStandardCost();
    }

    public function getCountryOfOrigin()
    {
        return $this->orderItem->getCountryOfOrigin();
    }

    public function getDescription()
    {
        return $this->orderItem->getDescription();
    }

    /** @return string */
    public function getHarmonizationCode()
    {
        return $this->orderItem->getHarmonizationCode();
    }

    public function getEccnCode()
    {
        return $this->orderItem->getEccnCode();
    }

    public function getRoHS()
    {
        return $this->orderItem->getRoHS();
    }

    public function getWeight()
    {
        return $this->orderItem->getWeight();
    }

    public function hasWeight()
    {
        return $this->orderItem->hasWeight();
    }

    public function isAssembly()
    {
        return $this->orderItem->isAssembly();
    }

    public function getBom(): Bom
    {
        return $this->orderItem->getBom();
    }

    /** @return bool */
    public function isCompleted()
    {
        return $this->orderItem->isCompleted();
    }

    /** @return bool */
    public function isControlled()
    {
        return $this->orderItem->isControlled();
    }

    /**
     * Set this to true to mark this line item as completed when it is saved.
     *
     * @param bool $bool
     */
    public function setCloseOrder($bool)
    {
        $this->closeOrder = $bool;
    }

    /**
     * This method should only be called by the SalesInvoice class.
     *
     * @param Transaction $glTrans
     */
    public function process(DebtorInvoice $debtorTrans, Transaction $glTrans)
    {
        $sod = $this->orderItem;
        if ($sod->isCompleted()) {
            return;
        }
        /* Update the sales order line item. */
        $sod->addQuantityInvoiced($this->qtyInvoiced);
        if ($this->closeOrder) {
            $sod->close();
        }

        if ($this->qtyInvoiced == 0) {
            return;
        }

        $this->debtorTrans = $debtorTrans;
        $debtorTrans->addLineItem($this);

        /* Consume stock and enter stock moves. */
        foreach ($this->orderItem->getRequirements() as $req) {
            /* @var $req Requirement */
            $consumption = new StockConsumption($req, $glTrans);
            $extQty = $this->getQtyToShip() * $req->getUnitQtyNeeded();
            $stockMoves = $consumption->consume($extQty);
            foreach ($stockMoves as $stockMove) {
                $stockMove->setForSalesOrderItem($this);
            }
        }
    }

    /**
     * Adds the GL entries for this line item to the given GL transaction.
     * @param Transaction $glTrans
     */
    public function recordGLEntries(Transaction $glTrans)
    {
        assert($this->qtyInvoiced > 0);

        $customer = $this->invoice->getCustomer();

        if ($this->getStandardCost()) {
            $stockValueNarrative = sprintf('%s - %s x %s @ %s',
                $customer->getId(),
                $this->getSku(),
                $this->getQtyToShip(),
                $this->getStandardCost()
            );

            /* Record the cost of goods sold (COGS). */
            $cogsAccount = $this->orderItem->getCogsAccount();

            $glTrans->addEntry(
                $cogsAccount,
                $this->getTotalStandardCost(),
                $stockValueNarrative
            );

            /* Record a corresponding decrease in the stock account. */
            $stockAccount = $this->getStockAccount();
            $glTrans->addEntry(
                $stockAccount,
                -$this->getTotalStandardCost(),
                $stockValueNarrative
            );
        }

        if ($this->getBaseUnitPrice()) {
            $currencyRate = $this->getCurrencyRate();

            /* Record the pre-discount price of this item. */
            $preDiscountPrice = (
                    $this->getBaseUnitPrice() + $this->getPriceAdjustment())
                * $this->qtyInvoiced;
            $salesAccount = $this->orderItem->getSalesAccount();
            $glTrans->addEntry(
                $salesAccount,
                -$preDiscountPrice / $currencyRate,
                sprintf('%s - %s x %s @ %s',
                    $customer->getId(),
                    $this->getSku(),
                    $this->getQtyToShip(),
                    $this->getBaseUnitPrice()
                )
            );

            /* If there's a discount, record that in the discount account. */
            $discountRate = $this->orderItem->getDiscountRate();
            if ($discountRate) {
                $discountAccount = $this->orderItem->getDiscountAccount();
                $discountAmount = $preDiscountPrice - $this->getExtendedPrice();
                $glTrans->addEntry(
                    $discountAccount,
                    $discountAmount / $currencyRate,
                    sprintf('%s - %s @ %s%%',
                        $customer->getId(),
                        $this->getSku(),
                        $discountRate * 100
                    )
                );
            }
        }
    }
}
