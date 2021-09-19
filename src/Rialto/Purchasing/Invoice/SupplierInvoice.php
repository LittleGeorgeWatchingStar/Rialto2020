<?php

namespace Rialto\Purchasing\Invoice;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Money;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Company\Company;
use Rialto\Database\Orm\Persistable;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Supplier\Supplier;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use UnexpectedValueException;

/**
 * An invoice from a supplier.
 *
 * @UniqueEntity(fields={"purchaseOrder", "supplierReference"},
 *   message="This supplier reference already exists.")
 */
class SupplierInvoice implements RialtoEntity, Persistable
{
    const LINE_TAX = -1;
    const LINE_SHIPPING = -2;

    const MONEY_PRECISION = 2;

    /** @var string */
    private $id;

    /**
     * @var Supplier
     */
    private $supplier;

    /**
     * @var PurchaseOrder
     */
    private $purchaseOrder;

    /**
     * @var string
     * @Assert\NotBlank(message="Supplier reference must not be blank.")
     * @Assert\Length(max=50)
     * @Assert\Regex(pattern="/\d/",
     *   message="The supplier reference should probably contain some digits.")
     */
    private $supplierReference;

    /**
     * @var DateTime
     * @Assert\NotNull(message="Invoice date is required.")
     * @Assert\DateTime(message="Invoice date is not valid.")
     * @Assert\Range(max="+2 years", maxMessage="Invoice date is too far in the future.")
     */
    private $invoiceDate;

    /**
     * @var float
     * @Assert\NotBlank(message="Total cost is required.")
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    private $totalCost = 0;

    /**
     * @var SupplierInvoiceItem[]
     * @Assert\Valid(traverse=true)
     */
    private $items;

    private $approved = false;

    private $filename = '';

    /** @var string */
    private $trackingNumber = '';

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
        $this->items = new ArrayCollection();
        $this->setDate(new DateTime());
    }

    /***
     * Factory method.
     *
     * @param PurchaseOrder $po
     * @return SupplierInvoice
     * @throws \LogicException if $po has no supplier
     */
    public static function fromPurchaseOrder(PurchaseOrder $po)
    {
        $invoice = new self($po->getSupplier());
        $invoice->setPurchaseOrder($po);
        return $invoice;
    }

    public function getId()
    {
        return $this->id;
    }

    /** @return Supplier */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /** @return PurchaseOrder */
    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    public function setPurchaseOrder(PurchaseOrder $po = null)
    {
        $this->purchaseOrder = $po;
        if (!$po) {
            return;
        } elseif (!$po->hasSupplier()) {
            throw new \InvalidArgumentException("$po has no supplier");
        }
        $poSupplier = $po->getSupplier();
        if ($poSupplier->isSubsidiaryOf($this->supplier)) {
            $this->supplier = $poSupplier;
        } else {
            throw new \InvalidArgumentException(
                "$po is for $poSupplier, not {$this->supplier}"
            );
        }
    }

    public function getIndexKey()
    {
        $id = $this->supplier->getId();
        $ref = $this->getSupplierReference() ?:
            ($this->purchaseOrder ? $this->purchaseOrder->getId() : '');
        return "{$id}_{$ref}";
    }

    /**
     * @return string The supplier's internal invoice number.
     */
    public function getSupplierReference()
    {
        return (string) $this->supplierReference;
    }

    public function setSupplierReference($ref)
    {
        $this->supplierReference = trim($ref);
    }

    /**
     * @return string The supplier's internal order number.
     */
    public function getSupplierOrderReference()
    {
        return $this->purchaseOrder
            ? $this->purchaseOrder->getSupplierReference()
            : '';
    }

    public function setSupplierOrderReference($orderRef)
    {
        if ($this->purchaseOrder) {
            $this->purchaseOrder->setSupplierReference($orderRef);
        }
    }

    public function __toString()
    {
        return sprintf('invoice %s from %s',
            $this->supplierReference,
            $this->getSupplier());
    }

    public function getDate()
    {
        return $this->getInvoiceDate();
    }

    public function setDate(DateTime $date = null)
    {
        $this->setInvoiceDate($date);
    }

    public function getInvoiceDate()
    {
        return $this->invoiceDate ? clone $this->invoiceDate : null;
    }

    public function setInvoiceDate(DateTime $date = null)
    {
        $this->invoiceDate = $date ? clone $date : null;
    }

    /** @return DateTime */
    private function getDueDate()
    {
        $supplier = $this->getSupplier();
        $terms = $supplier->getPaymentTerms();
        return $terms->calculateDueDate($this->invoiceDate);
    }

    public function getTotalCost()
    {
        return $this->round($this->totalCost);
    }

    public static function round($value)
    {
        return Money::round($value, self::MONEY_PRECISION);
    }

    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;
    }

    /**
     * Cross-check the total against the sum of the line item amounts.
     *
     * @Assert\Callback
     */
    public function validateTotalCost(ExecutionContextInterface $context)
    {
        if (count($this->items) == 0) return;
        $calculated = $this->calculateTotalCost();
        if ($this->round($calculated) != $this->round($this->totalCost)) {
            $context->buildViolation(sprintf(
                "Total cost (%s) does not match sum of line items (%s)",
                number_format($this->totalCost, 2),
                number_format($calculated, 8)
            ))
                ->atPath('totalCost')
                ->addViolation();
        }
    }

    private function calculateTotalCost()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getExtendedCost();
        }
        return $total;
    }

    /**
     * @see SupplierInvoiceItem->fixUnitCost()
     */
    public function fixUnitCosts()
    {
        foreach ($this->items as $item) {
            $item->fixUnitCost();
        }
    }

    /** @return SupplierInvoiceItem[] */
    public function getItems()
    {
        return $this->items->toArray();
    }

    public function addItem(SupplierInvoiceItem $item)
    {
        $this->items[] = $item;
        $item->setSupplierInvoice($this);
    }

    public function removeItem(SupplierInvoiceItem $item)
    {
        $this->items->removeElement($item);
    }

    /**
     * It is okay to have no line items as long as the total cost is set.
     * In this case, the prepare() method will create a single line item
     * for the total cost.
     *
     * @Assert\Callback
     */
    public function validateLineItems(ExecutionContextInterface $context)
    {
        if (count($this->items) == 0) {
            if ($this->totalCost < 0.01) {
                $context->buildViolation("No line items found.")
                    ->atPath('items')
                    ->addViolation();
            }
        }
    }

    public function setFreightCharges($charges)
    {
        $item = new SupplierInvoiceItem();
        $item->setLineNumber(self::LINE_SHIPPING);
        $item->setDescription('Freight charges');
        $item->setQtyInvoiced(1);
        $item->setUnitCost($charges);
        $item->setGLAccount(GLAccount::fetchShippingExpenses());
        $this->addItem($item);
    }

    public function setTaxCharges($charges)
    {
        $item = new SupplierInvoiceItem();
        $item->setLineNumber(self::LINE_TAX);
        $item->setDescription('Taxes charged');
        $item->setQtyInvoiced(1);
        $item->setUnitCost($charges);
        $item->setGLAccount(GLAccount::fetchSalesTaxes());
        $this->addItem($item);
    }

    /**
     * Normalizes this invoice and prepares it to be persisted.
     */
    public function prepare()
    {
        if (count($this->items) == 0) {
            $item = new SupplierInvoiceItem();
            $item->setLineNumber(0);
            $item->setQtyInvoiced(1);
            $item->setUnitCost($this->totalCost);
            $item->setDescription($this->supplierReference);
            $this->addItem($item);
        }

        foreach ($this->items as $item) {
            $this->loadHeaderFields($item);
        }
    }

    /* Maintain legacy fields in item records */
    private function loadHeaderFields(SupplierInvoiceItem $item)
    {
        if ($this->purchaseOrder) {
            $item->setPurchaseOrder($this->purchaseOrder);
        } else {
            $item->setSupplier($this->supplier);
        }
        $item->setDate($this->invoiceDate);
        $item->setSupplierReference($this->supplierReference);
    }

    public function isApproved()
    {
        return $this->approved;
    }

    /**
     * Approves the invoice and creates a Transaction for it.
     */
    public function approve(SystemType $sysType,
                            Company $company): SupplierTransaction
    {
        $subtotal = 0;
        $tax = 0;

        $glTrans = new Transaction($sysType);
        $glTrans->setDate($this->getDate());
        $suppTrans = $this->createSupplierTransaction($glTrans);
        foreach ($this->items as $item) {
            $item->approve($glTrans);
            if ($item->isTax()) {
                $tax += $item->getExtendedCost();
            } else {
                $subtotal += $item->getExtendedCost();
            }
        }

        $total = $subtotal + $tax;
        if ($total != 0) {
            $glTrans->addEntry($company->getCreditorsAccount(), -$total, sprintf(
                '%s - Inv %s USD%s @ a rate of 1.0000',
                $this->getSupplier()->getId(),
                $this->supplierReference,
                number_format($total, 2)
            ));
        }
        if (!$glTrans->isBalanced()) {
            throw new UnexpectedValueException(
                "Entries for invoice {$this->id} are not balanced");
        }

        $suppTrans->setSubtotalAmount($subtotal);
        $suppTrans->setTaxAmount($tax);
        $this->approved = true;

        return $suppTrans;
    }

    private function createSupplierTransaction(Transaction $glTrans): SupplierTransaction
    {
        $suppTrans = new SupplierTransaction($glTrans, $this->supplier);
        $suppTrans->setDate($this->getInvoiceDate());
        $suppTrans->setReference($this->supplierReference);
        $suppTrans->setDueDate($this->getDueDate());
        $suppTrans->setMemo(sprintf('%s invoice %s',
            $this->supplier,
            $this->supplierReference));
        return $suppTrans;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setFilename($filename)
    {
        $this->filename = trim($filename);
    }

    public function getEntities()
    {
        return [$this];
    }

    public function unapprove()
    {
        foreach ($this->items as $item) {
            $item->unapprove();
        }
        $this->approved = false;
    }

    public function getDeliveryLocation()
    {
        return $this->purchaseOrder ?
            $this->purchaseOrder->getDeliveryLocation() : null;
    }

    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber($trackingNumber)
    {
        $this->trackingNumber = $trackingNumber ?: '';
    }
}
