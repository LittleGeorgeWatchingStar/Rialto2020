<?php

namespace Rialto\Purchasing\Receiving;

use DateTime;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Entity\RialtoEntity;
use Rialto\IllegalStateException;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Invoice\SupplierInvoiceItem;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Move\StockMove;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A line item from a goods received notice.  Indicates the item and
 * quantity received.
 *
 * @see GoodsReceivedNotice
 */
class GoodsReceivedItem implements Item, RialtoEntity
{
    private $id;

    /**
     * @var DateTime
     * @Assert\DateTime
     * @Assert\NotNull
     */
    private $dateReceived;
    private $qtyReceived = 0;
    private $qtyToReverse = 0;
    private $qtyInvoiced = 0;

    /** @var BinStyle */
    private $binStyle;

    /**
     * True if this stock was just thrown away instead of being added
     * to stock.
     * @var bool
     */
    private $discarded = false;

    /**
     * True if the CM is still planning to charge us, even if this item
     * is being discarded.
     *
     * @var bool
     */
    private $chargeForDiscard = false;

    /** @var GoodsReceivedNotice */
    private $goodsReceivedNotice;

    /** @var StockProducer */
    private $producer;

    /** @var float */
    private $standardUnitCost;

    /** @var Facility */
    private $receivedInto;

    /** @var SupplierInvoiceItem */
    private $invoiceItem;

    /** @var StockAllocation[] */
    private $allocationsReceived = [];

    public function __construct(GoodsReceivedNotice $grn, StockProducer $producer, $qtyReceived)
    {
        $this->goodsReceivedNotice = $grn;
        $this->dateReceived = $grn->getDate();
        $this->receivedInto = $grn->getReceivedInto();

        $this->producer = $producer;
        $this->binStyle = $producer->getBinStyle();
        $this->standardUnitCost = $producer->getStandardUnitCost();

        $this->qtyReceived = $qtyReceived;
        $this->qtyToReverse = $this->getQtyReceived();
    }

    public function getReceivedInto()
    {
        return $this->receivedInto;
    }

    public function setReceivedInto(Facility $location)
    {
        $this->receivedInto = $location;
    }

    public function setBinStyle(BinStyle $binStyle)
    {
        $this->binStyle = $binStyle;
    }

    public function setDiscarded($discarded = true)
    {
        $this->discarded = $discarded;
    }

    public function isDiscarded()
    {
        return $this->discarded;
    }

    /**
     * @return boolean
     */
    public function isChargeForDiscard()
    {
        return $this->chargeForDiscard;
    }

    /**
     * @param boolean $charge
     */
    public function setChargeForDiscard($charge)
    {
        $this->chargeForDiscard = $charge;
    }

    /** @return StockBin */
    public function createBin()
    {
        assertion($this->isStockItem());
        assertion(! $this->discarded);
        $bin = new StockBin($this->getStockItem(), $this->receivedInto, $this->getVersion());
        $bin->setCustomization($this->producer->getCustomization());
        $bin->setBinStyle($this->binStyle);
        $bin->setManufacturer($this->producer->getManufacturer());
        $bin->setManufacturerCode($this->producer->getManufacturerCode());
        $bin->setPurchaseCost($this->producer->getUnitCost());
        $bin->setNewQty($this->qtyReceived);
        return $bin;
    }

    /** @return GLAccount */
    public function getStockAccount()
    {
        if ($this->discarded) {
            return GLAccount::fetchMaterialsCost();
        } else {
            return $this->producer->getGLAccount();
        }
    }

    public function getStandardUnitCost()
    {
        return $this->standardUnitCost;
    }

    /**
     * This is the amount used in the GRN accounting transaction.
     */
    public function getUnitPurchaseCost()
    {
        if ($this->producer->isWorkOrder()) {
            return $this->producer->getUnitCost();
        } elseif ($this->producer->isStockItem()) {
            return $this->standardUnitCost;
        } else {
            return $this->producer->getUnitCost();
        }
    }

    public function getExtendedPurchaseCost()
    {
        return $this->qtyReceived * $this->getUnitPurchaseCost();
    }

    public function isZeroCost()
    {
        return ($this->getUnitPurchaseCost() == 0) &&
        ($this->producer->isZeroCost());
    }

    public function requiresPurchaseAccounting()
    {
        if ($this->isZeroCost()) {
            return false;
        }
        if ($this->isDiscarded()) {
            return $this->isChargeForDiscard();
        }
        return true;
    }

    /**
     * @return StockProducer
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * @return GoodsReceivedNotice
     */
    public function getGoodsReceivedNotice()
    {
        return $this->goodsReceivedNotice;
    }

    public function getId()
    {
        return $this->id;
    }

    public function isWorkOrder()
    {
        return $this->producer->isWorkOrder();
    }

    /** @return GoodsReceivedItem */
    public function createChildReceipt(WorkOrder $child)
    {
        assertion($this->isWorkOrder());
        $childReceipt = $this->goodsReceivedNotice->addItem($child, $this->qtyReceived);
        $childReceipt->setReceivedInto($this->producer->getLocation());
        return $childReceipt;
    }

    public function isStockItem()
    {
        return $this->producer->isStockItem();
    }

    public function getStockItem()
    {
        return $this->isStockItem() ? $this->producer->getStockItem() : null;
    }

    public function getSku()
    {
        return $this->isStockItem() ? $this->producer->getSku() : '';
    }

    /**
     * @deprecated use getSku() instead
     */
    public function getStockCode()
    {
        return $this->getSku();
    }

    /** @return StockMove[] */
    public function getStockMoves()
    {
        if (! $this->isStockItem()) {
            return [];
        }
        $sku = $this->getSku();
        $moves = $this->goodsReceivedNotice->getStockMoves();
        return array_filter($moves, function (StockMove $move) use ($sku) {
            return $move->getSku() == $sku;
        });
    }

    public function getDescription()
    {
        return $this->producer->getDescription();
    }

    public function getSummary()
    {
        return sprintf('%s x %s from GRN %s',
            number_format($this->qtyReceived),
            $this->getDescription(),
            $this->goodsReceivedNotice->getId());
    }

    public function __toString()
    {
        return $this->getSummary();
    }

    public function isAutoReceive()
    {
        return $this->producer->isAutoReceive();
    }

    public function updateProducer()
    {
        $this->producer->addQtyReceived($this->qtyReceived);
        if ($this->qtyReceived > 0 && $this->isWorkOrder()) {
            $this->producer->setOpenForAllocation(false);
        }
    }

    public function getInvoiceItem()
    {
        return $this->invoiceItem;
    }

    public function setInvoiceItem(SupplierInvoiceItem $invoiceItem = null)
    {
        $this->invoiceItem = $invoiceItem;
    }

    public function getItemDescription()
    {
        return $this->producer->getDescription();
    }

    public function getVersion()
    {
        return $this->producer->getVersion();
    }

    public function getQtyOrdered()
    {
        return $this->producer->getQtyOrdered();
    }

    public function getQtyPreviouslyReceived()
    {
        return $this->producer->getQtyReceived();
    }

    private function getQtyRemaining()
    {
        return $this->getQtyOrdered() - $this->getQtyPreviouslyReceived();
    }

    public function getQtyReceived()
    {
        return $this->qtyReceived;
    }

    public function adjustQtyReceived($diff)
    {
        $this->qtyReceived += $diff;
    }

    public function isReceived()
    {
        return $this->isAutoReceive() &&
        ($this->qtyReceived > 0);
    }

    public function setReceived($received)
    {
        if (! $this->isAutoReceive()) {
            throw new IllegalStateException("Not an auto-receive item");
        }
        $this->qtyReceived = $received ? $this->getQtyRemaining() : 0;
    }

    public function getQtyToReverse(): int
    {
        return $this->qtyToReverse;
    }

    public function setQtyToReverse(int $qtyToReverse)
    {
        $this->qtyToReverse = $qtyToReverse;
    }

    /**
     * @Assert\Callback
     */
    public function validateQtyReceived(ExecutionContextInterface $context)
    {
        $qtyRec = $this->getQtyReceived();
        $qtyRem = $this->getQtyRemaining();
        if ($qtyRec > $qtyRem) {
            $context->addViolation(
                "Cannot receive more (_rec) than is left (_rem).",
                [
                    '_rec' => number_format($qtyRec),
                    '_rem' => number_format($qtyRem),
                ]);
        }
    }

    public function getQtyInvoiced()
    {
        return $this->qtyInvoiced;
    }

    public function addQtyInvoiced($qtyInvoiced, $actualCost = null)
    {
        $this->qtyInvoiced += $qtyInvoiced;
        $this->producer->addQtyInvoiced($qtyInvoiced, $actualCost);
    }

    public function getQtyUninvoiced()
    {
        return $this->qtyReceived - $this->qtyInvoiced;
    }

    public function isCompleted()
    {
        return $this->producer->isClosed();
    }

    public function getDate()
    {
        return $this->dateReceived;
    }

    public function getDateReceived()
    {
        return $this->dateReceived;
    }

    public function setDate(DateTime $date)
    {
        $this->dateReceived = $date;
    }

    /**
     * The unit cost of the PO item.
     * @return float
     */
    public function getUnitCost()
    {
        return $this->producer->getUnitCost();
    }

    public function getUnitStandardCost()
    {
        return $this->producer->getStandardUnitCost();
    }

    public function getAllocationsReceived()
    {
        return $this->allocationsReceived;
    }

    /** @param StockAllocation[] $allocations */
    public function setAllocationsReceived(array $allocations)
    {
        $this->allocationsReceived = $allocations;
    }
}


