<?php

namespace Rialto\Stock\Transfer;

use Rialto\Stock\Bin\HasStockBins;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event related to location transfers.
 */
class TransferEvent extends Event implements HasStockBins
{
    private $transfer;

    public function __construct(Transfer $transfer)
    {
        $this->transfer = $transfer;
    }

    public function getTransfer()
    {
        return $this->transfer;
    }

    public function getBins()
    {
        return $this->transfer->getBins();
    }
}
