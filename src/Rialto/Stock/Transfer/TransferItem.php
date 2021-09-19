<?php

namespace Rialto\Stock\Transfer;

use DateTime;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Entity\RialtoEntity;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Cost\HasStandardCost;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\VersionedItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An item that was sent in a location transfer.
 */
class TransferItem implements RialtoEntity, VersionedItem, HasStandardCost
{
    private $id;

    /** @var Transfer */
    private $transfer;

    /** @var StockBin */
    private $stockBin;

    /** @var float */
    private $qtySent;

    /**
     * @Assert\Type(type="numeric", message="Quantity received must be a number.")
     * @Assert\Range(min=0, minMessage="Quantity received must be positive.")
     */
    private $qtyReceived = 0;

    /** @var DateTime|null */
    private $dateReceived = null;

    public function __construct(Transfer $transfer, StockBin $bin)
    {
        $this->transfer = $transfer;
        $this->stockBin = $bin;
        $this->qtySent = $bin->getQuantity();
    }

    public function __toString()
    {
        return sprintf('%s (%s) from stock transfer %s',
            $this->stockBin,
            $this->getSku(),
            $this->transfer->getId());
    }

    public function getId()
    {
        return $this->id;
    }

    /** @return Transfer */
    public function getTransfer()
    {
        return $this->transfer;
    }

    /** @return Facility */
    public function getOrigin()
    {
        return $this->transfer->getOrigin();
    }

    /** @return Facility */
    public function getDestination()
    {
        return $this->transfer->getDestination();
    }

    /**
     * Only used when splitting a transferred bin.
     */
    public function updateQtySent($newQty)
    {
        $this->qtySent = $newQty;
    }

    /**
     * @return int
     */
    public function getQtyReceived()
    {
        return $this->qtyReceived;
    }

    public function setQtyReceived($qty)
    {
        $this->qtyReceived = $qty;
    }

    /**
     * Whether this item should be treated as received or missing, based on
     * the qty that the receiver says they got.
     *
     * @return bool
     */
    public function shouldBeReceived()
    {
        return ($this->qtyReceived > 0) // we got something, or...
            || ($this->qtySent == 0); // they returned an empty bin to us
    }

    public function isReceived()
    {
        return null !== $this->dateReceived;
    }

    public function getDateReceived()
    {
        return $this->dateReceived ? clone $this->dateReceived : null;
    }

    public function getDateSent()
    {
        return $this->transfer->getDateShipped();
    }

    /**
     * @return int
     */
    public function getQtySent()
    {
        return $this->qtySent;
    }

    /**
     * @return StockBin|null
     *  Null if this is for an uncontrolled stock item.
     */
    public function getStockBin()
    {
        return $this->stockBin;
    }

    /**
     * @return StockItem
     */
    public function getStockItem()
    {
        return $this->stockBin->getStockItem();
    }

    public function getSku()
    {
        return $this->stockBin->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getCustomization()
    {
        return $this->stockBin->getCustomization();
    }

    public function getVersion()
    {
        return $this->stockBin->getVersion();
    }

    /**
     * @deprecated full getFullSku() instead
     */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->stockBin->getFullSku();
    }

    public function getItemVersion(): ItemVersion
    {
        return $this->stockBin->getItemVersion();
    }

    public function getFullSku()
    {
        return $this->stockBin->getFullSku();
    }

    public function getDescription()
    {
        return $this->getStockItem()->getName() . $this->getLabelText();
    }

    /**
     * @return string The text printed on the label, if this item is a printed
     * label.
     */
    private function getLabelText()
    {
        if ($this->getStockItem()->isPrintedLabel()) {
            $allocs = $this->stockBin->getAllocations();
            foreach ($allocs as $alloc) {
                return sprintf(' "%s"',
                    $alloc->getConsumer()->getFullSku());
            }
        }
        return '';
    }

    public function kit(Transaction $trans)
    {
        $trans->moveBin($this->stockBin, $this->transfer);
    }

    public function receive(Transaction $trans)
    {
        $this->dateReceived = clone $trans->getDate();
        if ($this->stockBin->isAtLocation($this->getDestination())) {
            return;
        }
        $trans->moveBin($this->stockBin, $this->getDestination());
        $this->closeMissingAllocations();
        $this->adjustBinQtyIfNeeded();
    }

    /**
     * Any "placeholder" allocations from the transfer origin location
     * can now be closed -- this bin is no longer missing.
     */
    private function closeMissingAllocations()
    {
        foreach ($this->stockBin->getAllocations() as $alloc) {
            if ($alloc->isMissingFrom($this->getOrigin())) {
                $alloc->close();
            }
        }
    }

    /**
     * Called when the receiver tells us that the quantity they received
     * is different than what we thought was on the bin.
     */
    private function adjustBinQtyIfNeeded()
    {
        $diff = $this->getQtyReceived() - $this->getQtySent();
        if ($diff != 0) {
            $this->stockBin->setQtyDiff($diff);
        }
    }

    public function lost(DateTime $dateLost)
    {
        $this->dateReceived = clone $dateLost;
        $this->setQtyReceived(0);
        $this->adjustBinQtyIfNeeded();
    }

    /**
     * Indicates that this item was never actually sent with the transfer.
     */
    public function neverSent(DateTime $dateFound)
    {
        $this->dateReceived = clone $dateFound;
        $this->qtySent = 0;
        $this->setQtyReceived(0);
        $this->adjustBinQtyIfNeeded();
    }

    public function isSent()
    {
        return $this->transfer->isSent();
    }

    /**
     * True if the transfer was received but this item was not in it.
     * @return boolean
     */
    public function isMissing()
    {
        return $this->transfer->isReceived() &&
            $this->stockBin->isAtLocation($this->transfer);
    }

    /** @return float */
    public function getUnitStandardCost()
    {
        return $this->stockBin->getUnitStandardCost();
    }

    /** @return float */
    public function getExtendedStandardCost()
    {
        return $this->getUnitStandardCost() * $this->qtySent;
    }

    /**
     * @return string
     */
    public function getShelfPosition()
    {
        return $this->stockBin->getShelfPosition();
    }

    public function getTotalWeight(): float
    {
        return $this->stockBin->getItemVersion()->getTotalWeight();
    }
}
