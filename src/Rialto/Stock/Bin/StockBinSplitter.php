<?php

namespace Rialto\Stock\Bin;

use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Stock\StockEvents;
use Rialto\Stock\Transfer\Transfer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Splits a stock bin in two and records all corresponding stock move
 * records.
 */
class StockBinSplitter
{
    /** @var DbManager */
    private $dbm;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(DbManager $dbm, EventDispatcherInterface $dispatcher)
    {
        $this->dbm = $dbm;
        $this->dispatcher = $dispatcher;
    }


    /**
     * Splits the selected quantity off of the bin and returns a new bin
     * containing that quantity.
     *
     * @return StockBin The new bin
     */
    public function split(StockBinSplit $split)
    {
        $newBin = $this->splitBin($split);
        $oldBin = $split->getOldBin();
        $this->createTransaction($oldBin, $newBin);
        $this->notifyOfSplit($oldBin, $newBin);
        return $newBin;
    }

    private function splitBin(StockBinSplit $split)
    {
        $oldBin = $split->getOldBin();
        $qty = $split->getQtyToSplit();
        assertion($qty > 0);
        $newBin = $oldBin->split($qty);
        $this->determineLocationOfNewBin($oldBin, $newBin);
        $this->dbm->persist($newBin);
        $this->dbm->flush(); // so that the new bin gets an ID
        assertion($newBin->getId());

        return $newBin;
    }

    /**
     * If the original bin is in transit, we return the new bin back to
     * the origin location.
     */
    private function determineLocationOfNewBin(StockBin $old, StockBin $new)
    {
        if (! $old->isInTransit()) {
            return;
        }
        /** @var Transfer $transfer */
        $transfer = $old->getLocation();
        assertion($transfer instanceof Transfer);
        $item = $transfer->getItem($old);
        $item->updateQtySent($old->getNewQty());
        $new->setLocation($transfer->getOrigin());
    }

    private function createTransaction(StockBin $oldBin, StockBin $newBin)
    {
        $transaction = new Transaction(
            SystemType::fetchStockBinSplit($this->dbm),
            $newBin->getId());
        $transaction->setMemo("Split $oldBin");
        $oldBin->applyNewQty($transaction);
        $newBin->applyNewQty($transaction);
        $this->dbm->persist($transaction);
    }

    private function notifyOfSplit(StockBin $oldBin, StockBin $newBin)
    {
        $event = new BinSplitEvent($oldBin, $newBin);
        $this->dispatcher->dispatch(StockEvents::BIN_SPLIT, $event);
    }

}
