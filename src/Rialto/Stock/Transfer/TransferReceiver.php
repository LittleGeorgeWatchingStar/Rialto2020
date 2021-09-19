<?php

namespace Rialto\Stock\Transfer;

use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Count\StockAdjustment;
use Rialto\Stock\StockEvents;
use Rialto\Stock\Transfer\Web\TransferReceipt;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Records the receipt of a location transfer and adjusts quantities as needed.
 */
class TransferReceiver
{
    /** @var DbManager */
    private $dbm;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(
        DbManager $dbm,
        EventDispatcherInterface $dispatcher)
    {
        $this->dbm = $dbm;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return Transaction
     */
    public function receive(TransferReceipt $receipt)
    {
        $transfer = $receipt->getTransfer();
        $transaction = Transaction::fromInitiator($transfer, $this->dbm);
        $transaction->setDate($receipt->getDate());
        $this->createNewLineItems($receipt, $transaction);
        $transfer->setReceived($receipt->getDate());

        foreach ($receipt->getItems() as $item) {
            if ($item->shouldBeReceived()) {
                $item->receive($transaction);
            }
        }
        $this->dbm->persist($transaction);
        $this->adjustQuantitiesIfNeeded($receipt->getItems(), $receipt->getDate());

        $this->dispatcher->dispatch(StockEvents::TRANSFER_RECEIPT, $receipt);

        return $transaction;
    }

    private function createNewLineItems(TransferReceipt $receipt, Transaction $transaction)
    {
        $transfer = $receipt->getTransfer();
        $destination = $transfer->getDestination();
        foreach ($receipt->getExtraItems() as $extra) {
            $bin = $extra->getStockBin();
            if ($bin->isAtLocation($destination)) {
                continue;
            }
            $newItem = $transfer->addBin($bin);
            $newItem->kit($transaction);
            $receipt->addItem($newItem);
        }
    }

    /**
     * @param TransferItem[] $items
     */
    private function adjustQuantitiesIfNeeded(array $items, \DateTime $date)
    {
        $adjustment = new StockAdjustment('Stock adjustment for location transfer receipt');
        $adjustment->setDate($date);
        foreach ($items as $item) {
            $bin = $item->getStockBin();
            if ($item->isReceived() && $bin->hasNewQty()) {
                $adjustment->addBin($bin);
            }
        }
        if ($adjustment->hasChanges()) {
            $adjustment->adjust($this->dbm);
        }
    }

    /**
     * Makes the necessary changes when a missing transfer item is found.
     */
    public function resolveMissingItem(MissingTransferItem $missing)
    {
        $transaction = Transaction::fromInitiator($missing->getTransfer(), $this->dbm);
        $transaction->setMemo('Resolve missing item from transfer');
        $transaction->setDate($missing->getDateFound());
        switch ($missing->getLocation()) {
            case MissingTransferItem::LOCATION_DESTINATION:
                $this->foundAtDestination($missing, $transaction);
                break;
            case MissingTransferItem::LOCATION_ORIGIN:
                $this->foundAtSource($missing, $transaction);
                break;
            case MissingTransferItem::LOCATION_MISSING:
                $this->missing($missing);
                break;
            default:
                /* No need to persist the transaction */
                return;
        }
        $this->dbm->persist($transaction);
        $this->adjustQuantitiesIfNeeded([$missing->getTransferItem()], $missing->getDateFound());
        $event = new TransferEvent($missing->getTransfer());
        $this->dispatcher->dispatch(StockEvents::MISSING_ITEM_RESOLVED, $event);
    }

    private function foundAtDestination(
        MissingTransferItem $missing,
        Transaction $transaction)
    {
        $item = $missing->getTransferItem();
        $item->setQtyReceived($missing->getQtyFound());
        $item->receive($transaction);
    }

    private function foundAtSource(
        MissingTransferItem $missing,
        Transaction $transaction)
    {
        $bin = $missing->getStockBin();
        $item = $missing->getTransferItem();
        $location = $item->getOrigin();
        if (! $bin->isAtLocation($location)) {
            $transaction->moveBin($bin, $location);
        }
        $item->neverSent($missing->getDateFound());
    }

    private function missing(MissingTransferItem $missing)
    {
        $item = $missing->getTransferItem();
        $item->lost($missing->getDateFound());
    }
}
