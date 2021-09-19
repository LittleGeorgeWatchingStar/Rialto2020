<?php

namespace Rialto\Stock\Move;

use DateTime;
use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Entity\RialtoEntity;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Sales\Invoice\InvoiceableOrderItem;
use Rialto\Sales\Invoice\SalesInvoiceItem;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\AssemblyStockItem;
use Rialto\Stock\Item\DummyStockItem;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Location;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Stock\VersionedItem;


/**
 * A stock move records a change in the amount of stock for a
 * single item at a single location for a single bin (if applicable).
 *
 * Generally, you should not create these yourself; various higher-level
 * transactions/service classes will do it.
 */
class StockMove implements RialtoEntity, VersionedItem, AccountingEvent
{
    private $id;

    /** @var StockItem */
    private $stockItem;

    /** @var Transaction */
    private $transaction;

    /** @deprecated use $transaction instead */
    private $systemType;
    /** @deprecated use $transaction instead */
    private $systemTypeNumber;

    /** @var Facility */
    private $facility = null;

    /** @var Transfer */
    private $transfer = null;

    /** @var StockBin|null */
    private $stockBin;

    /** @var DateTime */
    private $date;

    /** @var Period */
    private $period;
    private $reference = '';
    private $quantity = 0;

    private $standardCost = 0.0;
    private $showOnInvoice = true;

    /**
     * If this move was part of an assembly item, this field points to that
     * assembly item.
     *
     * @var AssemblyStockItem
     */
    private $parentItem = null;

    /**
     * Factory function
     */
    public static function fromAssemblyItem(
        AssemblyStockItem $item,
        Facility $location): self
    {
        return new self($item, $location);
    }

    /**
     * Factory function
     */
    public static function fromDummyItem(
        DummyStockItem $item,
        Facility $location): self
    {
        return new self($item, $location);
    }

    /**
     * Factory function
     */
    public static function fromBin(StockBin $bin): self
    {
        $move = new self(
            $bin->getStockItem(),
            $bin->getLocation());
        $move->stockBin = $bin;
        return $move;
    }

    private function __construct(StockItem $item, Location $location)
    {
        $this->stockItem = $item;
        $this->setLocation($location);
        $this->standardCost = $item->getStandardCost();
    }

    private function setLocation(Location $location)
    {
        if ($location instanceof Facility) {
            $this->facility = $location;
        } elseif ($location instanceof Transfer) {
            $this->transfer = $location;
        } else {
            $cls = get_class($location);
            throw new \UnexpectedValueException("Unknown location type $cls");
        }
    }

    /**
     * @return StockItem
     */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function getVersion(): Version
    {
        return $this->stockBin
            ? $this->stockBin->getVersion()
            : Version::unknown();
    }

    /**
     * @return Customization|null
     */
    public function getCustomization()
    {
        return $this->stockBin
            ? $this->stockBin->getCustomization()
            : null;
    }

    public function isPhysicalPart(): bool
    {
        return $this->stockItem instanceof PhysicalStockItem;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function equals(StockMove $other = null): bool
    {
        return $other && $this === $other;
    }

    public function getLocation(): Location
    {
        return $this->facility ?: $this->transfer;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity($qty)
    {
        $this->quantity = (float) $qty;
    }

    /**
     * @return string
     */
    public function getMemo(): string
    {
        return $this->reference;
    }

    /** @deprecated use getMemo() */
    public function getReference()
    {
        return $this->getMemo();
    }

    /**
     * @param string $memo
     */
    public function setMemo($memo)
    {
        $this->reference = trim($memo);
    }

    /** @deprecated use setMemo() */
    public function setReference($memo)
    {
        $this->setMemo($memo);
    }

    /** @return string */
    public function getSku()
    {
        return $this->stockItem->getSku();
    }

    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    public function getFullSku()
    {
        return $this->stockBin
            ? $this->stockBin->getFullSku()
            : $this->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function isItem($item)
    {
        return $this->stockItem->isItem($item);
    }

    /** @deprecated Use getUnitStandardCost() instead */
    public function getStandardCost()
    {
        return $this->getUnitStandardCost();
    }

    public function getUnitStandardCost()
    {
        return $this->standardCost;
    }

    public function getExtendedStandardCost()
    {
        return $this->standardCost * $this->quantity;
    }

    /** @return SystemType|null */
    public function getSystemType()
    {
        return $this->transaction->getSystemType();
    }

    public function getSystemTypeNumber()
    {
        return $this->transaction->getGroupNo();
    }

    public function getSummary(): string
    {
        return sprintf('%s x %s on %s',
            number_format($this->quantity),
            $this->getSku(),
            $this->stockBin);
    }

    public function getDate()
    {
        return clone $this->date;
    }

    public function setDate(DateTime $date)
    {
        $this->date = clone $date;
    }

    /**
     * Useful for sorting.
     */
    public function getTimestamp(): int
    {
        return $this->date->getTimestamp();
    }

    public function hasParentItem(): bool
    {
        return null !== $this->parentItem;
    }

    public function getParentItem()
    {
        return $this->parentItem;
    }

    public function setParentItem(AssemblyStockItem $parent)
    {
        $this->parentItem = $parent;
        $this->showOnInvoice = false;
    }

    /**
     * Returns true if this stock move should be shown on documents
     * sent to the customer.
     */
    public function isShowOnInvoice(): bool
    {
        return $this->showOnInvoice;
    }

    /**
     * Sets the fields of this stock move to indicate that it is a move for
     * a sales order detail.  Use this method in conjunction with setTransaction().
     */
    public function setForSalesOrderItem(InvoiceableOrderItem $item)
    {
        if ( $item instanceof SalesInvoiceItem && $item->isAssembly() ) {
            $this->setParentItem($item->getStockItem());
        }
    }

    /**
     * @return StockBin|null
     */
    public function getStockBin()
    {
        return $this->stockBin;
    }

    /**
     * Records the transaction that generated this stock move.
     */
    public function setTransaction(Transaction $source)
    {
        $this->transaction = $source;
        $this->systemType = $source->getSystemType();
        $this->systemTypeNumber = $source->getSystemTypeNumber();
        $this->date = $source->getDate();
        assertion($this->date instanceof DateTime);
        $this->period = $source->getPeriod();
        $this->reference = $source->getMemo();
    }
}
