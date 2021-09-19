<?php

namespace Rialto\Purchasing\Invoice;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Gumstix\GeographyBundle\Model\Country;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Money;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Receiving\GoodsReceivedItem;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Cost\StandardCostException;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A line item from a supplier invoice.
 */
class SupplierInvoiceItem implements RialtoEntity, Item
{
    /**
     * Number of decimal places to which unit cost should be rounded.
     *
     * For small components, the unit cost can be *very* small.
     */
    const MONEY_PRECISION = 8;

    private $id;

    /** @var SupplierInvoice */
    private $supplierInvoice;

    /**
     * @var string
     * @Assert\NotBlank(message="Description must not be blank.")
     */
    private $description;

    /**
     * @var int
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     * @Assert\NotBlank
     */
    private $qtyOrdered = 0;

    /**
     * @var integer
     * @Assert\Type(type="numeric", message="Qty invoiced must be a number.")
     * @Assert\Range(min=0, minMessage="Qty invoiced cannot be negative.")
     */
    private $qtyInvoiced;

    /**
     * @var string
     */
    private $supplierReference;

    /**
     * @var int
     * @Assert\Type(type="integer")
     */
    private $lineNumber;

    /**
     * @var float
     * @Assert\Type(type="numeric", message="Unit cost must be a number.")
     */
    private $unitCost = 0.0;

    /**
     * @var float
     * @Assert\Type(type="numeric", message="Extended cost must be a number.")
     */
    private $extendedCost = 0.0;

    /**
     * @var float
     * @Assert\Type(type="numeric", message="Tariff must be a number.")
     */
    private $tariff = 0.0;

    private $approved = false;
    private $posted = false;

    /**
     * @var DateTime
     */
    private $invoiceDate;

    /** @var GoodsReceivedItem[] */
    private $grnItems;

    /** @var GLAccount */
    private $glAccount;

    private $supplier;

    /** @var PurchaseOrder */
    private $purchaseOrder;

    /** @var StockItem|null */
    private $stockItem;

    private $harmonizationCode = '';
    private $eccnCode = '';
    private $countryOfOrigin = '';
    private $leadStatus = '';
    private $rohsStatus = '';
    private $reachStatus = '';

    /** @var DateTime|null */
    private $reachDate = null;

    /** @return SupplierInvoiceItem */
    public static function fromStockProducer(StockProducer $poItem)
    {
        $invItem = new self();
        $invItem->description = $poItem->getDescription();
        $invItem->qtyOrdered = $poItem->getQtyOrdered();
        $invItem->unitCost = $poItem->getUnitCost();
        $invItem->stockItem = $poItem->getStockItem();
        return $invItem;
    }

    public function __construct()
    {
        $this->grnItems = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /** @return SupplierInvoice */
    public function getSupplierInvoice()
    {
        return $this->supplierInvoice;
    }

    /** @return Supplier|null */
    public function getSupplier()
    {
        return $this->supplier;
    }

    public function setSupplier(Supplier $supplier)
    {
        if ($this->purchaseOrder) {
            assertion($supplier->equals($this->purchaseOrder->getSupplier()));
        }
        $this->supplier = $supplier;
    }

    public function setSupplierInvoice(SupplierInvoice $invoice)
    {
        $this->supplierInvoice = $invoice;
    }

    public function setPurchaseOrder(PurchaseOrder $po)
    {
        $this->purchaseOrder = $po;
        $this->setSupplier($po->getSupplier());
    }

    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    public function getPurchaseOrderNumber()
    {
        return $this->purchaseOrder ? $this->purchaseOrder->getId() : null;
    }

    public function setDate(DateTime $date)
    {
        $this->invoiceDate = $date;
    }

    public function getDate()
    {
        return $this->invoiceDate;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($desc)
    {
        $this->description = $desc;
    }

    public function setStockItem(StockItem $item = null)
    {
        $this->stockItem = $item;
    }

    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function getSku()
    {
        return $this->stockItem ? $this->stockItem->getSku() : null;
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getQtyInvoiced()
    {
        return $this->qtyInvoiced;
    }

    public function setQtyInvoiced($qty)
    {
        $this->qtyInvoiced = $qty;
    }

    public function getQtyOrdered()
    {
        return $this->qtyOrdered;
    }

    public function setQtyOrdered($qty)
    {
        $this->qtyOrdered = $qty;
    }

    public function getLineNumber()
    {
        return $this->lineNumber;
    }

    public function setLineNumber($num)
    {
        $this->lineNumber = (int) $num;
    }

    public function isTax()
    {
        return SupplierInvoice::LINE_TAX == $this->lineNumber;
    }

    public function isRegularItem()
    {
        return $this->lineNumber >= 0;
    }

    public function getUnitCost()
    {
        return (float) $this->unitCost;
    }

    public function setUnitCost($cost)
    {
        $this->unitCost = (float) $cost;
        if ($cost > 0 && $this->extendedCost == 0 && $this->qtyInvoiced > 0) {
            $this->setExtendedCost($cost * $this->qtyInvoiced);
        }
    }

    /**
     * For items with a very small unit cost, the supplier will frequently
     * round the unit cost, which means that:
     *   unit cost * qty invoiced != extended cost
     * We need the unit cost to be precise, so this method will recalculate
     * the unit cost if it within an acceptable tolerance.
     *
     * If this method cannot correct the unit price because the error is
     * too great, then this object will not pass validation (@see
     * validateExtendedCost())
     */
    public function fixUnitCost()
    {
        $calculated = $this->calculateUnitCostFromExtended();
        if ($calculated == 0) { // prevent division by zero
            return;
        }
        $this->fixUnitCostByRounding($calculated);
    }

    /**
     * In this strategy, we fix the unit cost if the error is below
     * a certain tolerance.
     *
     * This strategy didn't work too well in practice, hence
     * fixUnitCostByRounding() below.
     */
    private function fixUnitCostByTolerance($calculated)
    {
        static $tolerance = 0.001;
        $difference = abs($this->unitCost - $calculated);
        $error = $difference / $calculated;
        if ($error <= $tolerance) {
            $this->unitCost = $calculated;
        }
    }

    /**
     * In this strategy, we fix the unit cost if the calulated cost equals the
     * given after being rounded.
     */
    private function fixUnitCostByRounding($calculated)
    {
        static $precision = 5;
        if (round($calculated, $precision) == $this->unitCost) {
            $this->unitCost = $calculated;
        }
    }

    public function getExtendedCost()
    {
        return SupplierInvoice::round($this->extendedCost + $this->tariff);
    }

    public function setExtendedCost($total)
    {
        $this->extendedCost = (float) $total;
        if ($total > 0 && $this->unitCost == 0 && $this->qtyInvoiced > 0) {
            $this->setUnitCost($this->calculateUnitCostFromExtended());
        }
    }

    private function calculateUnitCostFromExtended()
    {
        if ($this->qtyInvoiced <= 0) { // Prevent division by zero
            return $this->unitCost;
        }
        return $this->round($this->extendedCost / $this->qtyInvoiced);
    }

    /** @Assert\Callback */
    public function validateExtendedCost(ExecutionContextInterface $context)
    {
        $expectedExt = SupplierInvoice::round(
            ($this->unitCost * $this->qtyInvoiced) + $this->tariff);
        $actualExt = $this->getExtendedCost();
        $expectedUnit = $this->calculateUnitCostFromExtended();
        if ($expectedExt != $actualExt) {
            /* Show both expected amounts, so that if it is a rounding error,
             * the user can see the exact precision required. */
            $context->buildViolation(
                "Expected _ext", ['_ext' => $expectedExt])
                ->atPath('extendedCost')
                ->addViolation();
            $context->buildViolation(
                "Expected _unit", ['_unit' => $expectedUnit])
                ->atPath('unitCost')
                ->addViolation();
        }
    }

    public function setSupplierReference($ref)
    {
        $this->supplierReference = $ref;
    }

    public function getSupplierReference()
    {
        return $this->supplierReference;
    }

    public function getGrnItems()
    {
        return $this->grnItems->toArray();
    }

    public function addGrnItem(GoodsReceivedItem $grnItem)
    {
        assert($this->isRegularItem());
        $grnItem->setInvoiceItem($this);
        $this->grnItems[] = $grnItem;
    }

    public function removeGrnItem(GoodsReceivedItem $grnItem)
    {
        $grnItem->setInvoiceItem(null);
        $this->grnItems->removeElement($grnItem);
    }

    public function getGLAccount()
    {
        return $this->glAccount;
    }

    public function setGLAccount(GLAccount $glAccount = null)
    {
        $this->glAccount = $glAccount;
    }

    /**
     * @Assert\Callback(groups={"approval"})
     */
    public function validateSomethingSelected(ExecutionContextInterface $context)
    {
        if ($this->qtyInvoiced == 0) {
            return;
        }
        if ((! $this->glAccount) && (count($this->grnItems) == 0)) {
            $context->buildViolation(
                "You must select either a GRN or a GL account.")
                ->atPath('grnItems')
                ->addViolation();
        } elseif ($this->glAccount && (count($this->grnItems) > 0)) {
            $context->buildViolation(
                "You cannot select both a GRN and a GL account.")
                ->atPath('glAccount')
                ->addViolation();
        }
    }

    /**
     * @Assert\Callback(groups={"approval"})
     */
    public function validateQuantitiesMatch(ExecutionContextInterface $context)
    {
        if (count($this->grnItems) == 0) {
            return;
        }

        $grnQty = $this->getGrnQty();
        if ($this->qtyInvoiced != $grnQty) {
            $context->buildViolation(
                "Quantity invoiced (_inv) does not match quantity received (_rec).",
                [
                    '_inv' => number_format($this->qtyInvoiced),
                    '_rec' => number_format($grnQty),
                ])
                ->atPath('grnItems')
                ->addViolation();
        }
    }

    private function getGrnQty()
    {
        $total = 0;
        foreach ($this->grnItems as $grnItem) {
            $total += $grnItem->getQtyReceived();
        }
        return $total;
    }

    /**
     * Make sure that the user didn't try to link one stock move to
     * multiple invoice items.
     *
     * @Assert\Callback(groups={"approval"})
     */
    public function validateNoDuplicates(ExecutionContextInterface $context)
    {
        foreach ($this->grnItems as $grnItem) {
            if ($grnItem->getInvoiceItem() !== $this) {
                $context->buildViolation(
                    "You cannot link $grnItem to multiple invoice items.")
                    ->atPath('grnItems')
                    ->addViolation();
            }
        }
    }

    /**
     * Make sure that we haven't invoiced more than were originally ordered --
     * that could be a symptom of a bad match or a erroneous invoice.
     *
     * @Assert\Callback(groups={"approval"})
     */
    public function validateMaxQuantity(ExecutionContextInterface $context)
    {
        if (count($this->grnItems) == 0) return;
        foreach ($this->grnItems as $grnItem) {
            $poItem = $grnItem->getProducer();
            $totalInv = $poItem->getQtyInvoiced() + $this->getGrnQty();
            $totalOrd = $poItem->getQtyOrdered();
            if ($totalInv > $totalOrd) {
                $context->buildViolation(
                    "You cannot invoice more (_inv) than were ordered (_ord).", [
                    '_inv' => number_format($totalInv),
                    '_ord' => number_format($totalOrd),
                ])
                    ->atPath('grnItems')
                    ->addViolation();
                return;
            }
        }
    }

    /**
     * Make sure that each selected GRN item has a valid unit cost.
     *
     * @Assert\Callback(groups={"approval"})
     */
    public function validateGrnUnitCost(ExecutionContextInterface $context)
    {
        foreach ($this->grnItems as $grnItem) {
            if (($cost = $grnItem->getUnitPurchaseCost()) <= 0) {
                $context->buildViolation("$grnItem has invalid unit cost ($cost)")
                    ->atPath('grnItems')
                    ->addViolation();
                return;
            }
        }
    }

    public function approve(Transaction $glTrans)
    {
        $this->approved = true;
        $this->posted = true;

        if (count($this->grnItems) > 0) {
            $this->addStockEntries($glTrans);
            $this->updateGrnItems();
        } else {
            $this->addAccountEntry($glTrans);
        }
    }

    private function addStockEntries(Transaction $glTrans)
    {
        $totalQty = 0;
        $totalCost = $this->tariff;
        foreach ($this->grnItems as $grnItem) {
            $unitCost = $grnItem->getUnitPurchaseCost();
            if ($unitCost <= 0) {
                throw new StandardCostException($grnItem, $unitCost);
            }
            $totalQty += $grnItem->getQtyReceived();
            $totalCost += $grnItem->getExtendedPurchaseCost();
        }
        $this->addInventoryEntry($glTrans, $totalQty, $totalCost);
        $this->addCostVarianceEntry($glTrans, $totalQty, $totalCost);
    }

    private function addInventoryEntry(Transaction $glTrans, $totalQty, $totalCost)
    {
        assertion($totalQty > 0);
        assertion($totalCost > 0);

        $account = GLAccount::fetchUninvoicedInventory();
        $memo = $this->createMemo($totalQty, $totalCost, 'std cost');
        $glTrans->addEntry($account, $totalCost, $memo);
    }

    private function createMemo($totalQty, $totalCost, $desc)
    {
        return strtr(
            "_supplierID - PO _poID - _item x _qty @ _desc of _amount",
            [
                '_supplierID' => $this->getSupplier()->getId(),
                '_poID' => $this->purchaseOrder->getId(),
                '_item' => $this->getSku(),
                '_qty' => number_format($totalQty),
                '_desc' => $desc,
                '_amount' => number_format($totalCost / $totalQty, 4),
            ]);
    }

    private function addCostVarianceEntry(Transaction $glTrans, $totalQty, $totalCost)
    {
        /* The order of operations here is very important in avoiding
         * rounding errors. */
        $extActual = SupplierInvoice::round(
            $this->unitCost * $totalQty + $this->tariff);
        $extStandard = SupplierInvoice::round($totalCost);
        $variance = $extActual - $extStandard;
        if (0 == $variance) {
            return;
        }

        $account = GLAccount::fetchPurchaseVariance();
        $memo = $this->createMemo($totalQty, $variance, 'cost var');
        $glTrans->addEntry($account, $variance, $memo);
    }

    private static function round($amount)
    {
        return Money::round($amount, self::MONEY_PRECISION);
    }

    private function updateGrnItems()
    {
        $qtyLeft = $this->qtyInvoiced;
        foreach ($this->grnItems as $grnItem) {
            if ($qtyLeft == 0) {
                break;
            }
            $qty = $grnItem->getQtyUninvoiced();
            if ($qty == 0) {
                continue;
            }
            $grnItem->addQtyInvoiced($qty, $this->unitCost);
            $qtyLeft -= $qty;
        }
        assert($qtyLeft >= 0);
    }

    private function addAccountEntry(Transaction $glTrans)
    {
        if (0 == $this->getExtendedCost()) {
            return;
        }
        $memo = sprintf('%s - %s',
            $this->getSupplier()->getId(),
            $this->description);
        $glTrans->addEntry($this->glAccount, $this->getExtendedCost(), $memo);
    }

    public function unapprove()
    {
        $this->approved = false;
        $this->posted = false;
        foreach ($this->grnItems as $grnItem) {
            $grnItem->setInvoiceItem(null);
            $grnItem->addQtyInvoiced(-$grnItem->getQtyInvoiced());
        }
        $this->grnItems->clear();
    }

    public function getHarmonizationCode()
    {
        return $this->harmonizationCode;
    }

    public function setHarmonizationCode($code)
    {
        $this->harmonizationCode = trim($code);
    }

    public function getEccnCode()
    {
        return $this->eccnCode;
    }

    public function setEccnCode($eccnCode)
    {
        $this->eccnCode = strtoupper(trim($eccnCode));
    }

    /** @return Country|null */
    public function getCountryOfOrigin()
    {
        return $this->countryOfOrigin ? new Country($this->countryOfOrigin) : null;
    }

    public function setCountryOfOrigin(Country $country = null)
    {
        $this->countryOfOrigin = $country ? $country->getCode() : '';
    }

    public function getLeadStatus()
    {
        return $this->leadStatus;
    }

    public function setLeadStatus($leadStatus)
    {
        $this->leadStatus = trim($leadStatus);
    }

    public function getRohsStatus()
    {
        return $this->rohsStatus;
    }

    public function setRohsStatus($rohsStatus)
    {
        $this->rohsStatus = trim($rohsStatus);
    }

    public function getReachStatus()
    {
        return $this->reachStatus;
    }

    public function setReachStatus($reachStatus)
    {
        $this->reachStatus = trim($reachStatus);
    }

    public function getReachDate()
    {
        return $this->reachDate;
    }

    public function setReachDate(DateTime $date = null)
    {
        $this->reachDate = $date;
    }

    public function getTariff(): float
    {
        return $this->tariff;
    }

    public function setTariff(float $tariff)
    {
        $this->tariff = $tariff;
    }
}
