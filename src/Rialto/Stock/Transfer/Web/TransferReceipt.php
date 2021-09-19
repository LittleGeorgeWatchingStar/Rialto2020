<?php

namespace Rialto\Stock\Transfer\Web;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Stock\Transfer\TransferEvent;
use Rialto\Stock\Transfer\TransferItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Used for marking a transfer as received.
 */
class TransferReceipt extends TransferEvent
{
    /** @var TransferItem[] */
    private $items;

    /**
     * Items that were not intended to be sent with this transfer, but that
     * were found by the receiver.
     *
     * @var TransferExtraItem[]
     * @Assert\Valid(traverse="true")
     */
    private $extraItems = [];

    /** @var DateTime */
    private $date;

    public function __construct(Transfer $transfer)
    {
        assertion($transfer->isSent(), "$transfer is not sent");
        parent::__construct($transfer);
        $this->items = new ArrayCollection();
        foreach ($transfer->getLineItems() as $item) {
            if (! $item->isReceived()) {
                $this->addItem($item);
            }
        }
        $this->date = new DateTime();
    }

    public function addItem(TransferItem $item)
    {
        /* Assume all items are fully received. The user
         * can change this if needed. */
        $item->setQtyReceived($item->getQtySent());
        $this->items[] = $item;
    }

    public function removeItem(TransferItem $item)
    {
        $this->items->removeElement($item);
    }

    /** @return TransferItem[] */
    public function getItems()
    {
        $items =  $this->items->toArray();
        usort($items, function (TransferItem $a, TransferItem $b) {
            return strcmp($a->getFullSku(), $b->getFullSku());
        });
        return $items;
    }

    /** @return TransferExtraItem[] */
    public function getExtraItems()
    {
        $items = $this->extraItems;
        usort($items, function (TransferExtraItem $a, TransferExtraItem $b) {
            return strcmp($a->getSku(), $b->getSku());
        });
        return $items;
    }

    public function addExtraItem(TransferExtraItem $item)
    {
        $item->setTransfer($this->getTransfer());
        $this->extraItems[] = $item;
    }

    public function removeExtraItem(TransferExtraItem $item)
    {
        $idx = array_search($item, $this->extraItems, true);
        if (isset($this->extraItems[$idx])) {
            unset($this->extraItems[$idx]);
        }
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return clone $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate(DateTime $date)
    {
        $this->date = clone $date;
    }
}
