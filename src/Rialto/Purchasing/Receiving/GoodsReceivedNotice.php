<?php

namespace Rialto\Purchasing\Receiving;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Ledger\Entry\Orm\GLEntryRepository;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Move\Orm\StockMoveRepository;
use Rialto\Stock\Move\StockMove;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A goods received notice is a record that a purchase order or work order
 * was received.
 *
 * @see PurchaseOrder
 * @see WorkOrder
 */
class GoodsReceivedNotice implements RialtoEntity, AccountingEvent
{
    private $id;

    /**
     * @var DateTime
     * @Assert\DateTime
     */
    private $date;

    /**
     * @var PurchaseOrder
     */
    private $purchaseOrder = null;

    /** @var Facility */
    private $receivedInto;

    /** @var SystemType */
    private $systemType;

    /**
     * @var User
     */
    private $receiver;

    /**
     * @var GoodsReceivedItem[]
     * @Assert\Valid(traverse="true")
     */
    private $items;


    public function __construct(PurchaseOrder $order, User $receiver)
    {
        $this->receivedInto = $order->getDeliveryLocation();
        $this->purchaseOrder = $order;
        $this->systemType = SystemType::fetchPurchaseOrderDelivery();
        $this->receiver = $receiver;
        $this->date = new DateTime();
        $this->items = new ArrayCollection();
    }

    /** @return GoodsReceivedItem */
    public function addItem(StockProducer $poItem, $qtyReceived)
    {
        $grnItem = new GoodsReceivedItem($this, $poItem, $qtyReceived);
        $this->items[] = $grnItem;
        return $grnItem;
    }

    /**
     * Returns the purchase order whose receipt is represented by this GRN.
     *
     * @return PurchaseOrder|null
     */
    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    /** @return Facility */
    public function getReceivedInto()
    {
        return $this->receivedInto;
    }

    /**
     * @deprecated
     */
    public function getLocation()
    {
        return $this->getReceivedInto();
    }

    public function setReceivedInto(Facility $facility)
    {
        $this->receivedInto = $facility;
    }

    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return 'GRN '. $this->id;
    }

    /** @return DateTime */
    public function getDate()
    {
        return clone $this->date;
    }

    public function setDate(DateTime $date)
    {
        $this->date = $date;
    }

    public function getMemo(): string
    {
        $desc = $this->getDescription();
        return "Receive $desc";
    }

    public function getDescription()
    {
        return sprintf("%s from %s",
            $this->purchaseOrder,
            $this->purchaseOrder->getSupplierName()
        );
    }

    /**
     * @return Period
     */
    public function getPeriod()
    {
        return Period::fetchForDate($this->getDate());
    }

    /** @return User */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @return SystemType
     */
    public function getSystemType()
    {
        return $this->systemType;
    }

    public function getSystemTypeNumber()
    {
        return $this->getId();
    }

    /** @return GLEntry[] */
    public function getGLEntries()
    {
        $dbm = ErpDbManager::getInstance();
        /** @var $repo GLEntryRepository */
        $repo = $dbm->getRepository(GLEntry::class);
        return $repo->findByEvent($this);
    }

    /**
     * Returns the amount received into stock.
     * @return float
     */
    public function getInventoryAmount()
    {
        $total = 0;
        foreach ( $this->getGLEntries() as $entry ) {
            switch( $entry->getAccountCode() ) {
            case GLAccount::RAW_INVENTORY:
            case GLAccount::FINISHED_INVENTORY:
                $total += $entry->getAmount();
                break;
            }
        }
        return GLEntry::round($total);
    }

    /** @return StockMove[] */
    public function getStockMoves()
    {
        /** @var $repo StockMoveRepository */
        $repo = ErpDbManager::getInstance()->getRepository(StockMove::class);
        return $repo->findByEvent($this);
    }

    /**
     * Returns true if this GRN has been matched to an invoice.
     *
     * @return boolean
     */
    public function isApproved()
    {
        foreach ( $this->items as $grnItem ) {
            if ( $grnItem->getInvoiceItem() ) {
                return true;
            }
        }
        return false;
    }

    /** @return GoodsReceivedItem[] */
    public function getItems()
    {
        return $this->items->toArray();
    }

    /** @param  GoodsReceivedItem[] $items */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return GoodsReceivedItem
     * @throws InvalidArgumentException
     *  If this does not contain the requested item.
     */
    public function getItem(Item $item)
    {
        $grnItem = $this->getItemOrNull($item);
        if ($grnItem) {
            return $grnItem;
        }
        throw new InvalidArgumentException(
            "$this does not contain ". $item->getSku());
    }

    /**
     * @return null|GoodsReceivedItem
     */
    private function getItemOrNull(Item $item)
    {
        foreach ( $this->items as $grnItem) {
            if ( $item->getSku() == $grnItem->getSku() ) {
                return $grnItem;
            }
        }
        return null;
    }

    public function hasItems()
    {
        return $this->items->count() > 0;
    }

    public function hasItem(Item $item)
    {
        return null !== $this->getItemOrNull($item);
    }

    public function hasBins()
    {
        foreach ( $this->items as $grnItem ) {
            if ( $grnItem->isStockItem() ) {
                return true;
            }
        }
        return false;
    }
}
