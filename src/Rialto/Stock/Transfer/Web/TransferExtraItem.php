<?php

namespace Rialto\Stock\Transfer\Web;

use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Item;
use Rialto\Stock\Transfer\Transfer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Adds a new item to a location transfer -- one that was not originally
 * part of the transfer.
 *
 * This is to deal with the situation when an additional item, or the wrong
 * item, is accidentally put in the package.
 */
class TransferExtraItem implements Item
{
    /** @var Transfer */
    private $transfer;

    /** @var StockBin */
    private $stockBin;

    /** @var string */
    private $stockCode;

    public function setTransfer(Transfer $transfer)
    {
        $this->transfer = $transfer;
    }

    /**
     * @return Transfer
     */
    public function getTransfer()
    {
        return $this->transfer;
    }

    public function setStockBin(StockBin $bin = null)
    {
        $this->stockBin = $bin;
    }

    public function getStockBin()
    {
        return $this->stockBin;
    }

    public function getSerialNo()
    {
        return $this->stockBin->getId();
    }

    public function setStockCode($stockCode)
    {
        $this->stockCode = strtoupper($stockCode);
    }

    public function getSku()
    {
        return $this->stockCode;
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /**
     * @Assert\Callback
     */
    public function validateBinAndItem(ExecutionContextInterface $context)
    {
        if (! $this->stockBin) {
            $context->buildViolation("No such bin exists.")
                ->atPath('stockBin')
                ->addViolation();
            return;
        }
        $this->validateBinStockCode($context);
        $this->validateBinLocation($context);
    }

    /**
     * We ask the user for the stock code and well as the bin ID as a
     * form of error-checking: this improves the chances of detecting a
     * mis-typed bin ID.
     */
    private function validateBinStockCode(ExecutionContextInterface $context)
    {
        if ($this->stockBin->getSku() != $this->stockCode) {
            $context->buildViolation("_bin does not contain _code.", [
                '_bin' => $this->stockBin,
                '_code' => $this->stockCode,
            ])
                ->atPath('stockCode')
                ->addViolation();
        }
    }

    private function validateBinLocation(ExecutionContextInterface $context)
    {
        $src = $this->transfer->getOrigin();
        $dst = $this->transfer->getDestination();
        if ($this->stockBin->isAtLocation($src)) return;
        elseif ($this->stockBin->isAtLocation($dst)) {
            /* In this case, the user has just confirmed what the system
             * already knew: that this bin is at the destination location.
             * This shouldn't normally happen, but we'll just quietly let
             * it pass. */
            return;
        }

        $context->buildViolation('_bin is supposed to be at _loc; ' .
            'are you sure you typed the _reel ID correctly?', [
            '_bin' => $this->stockBin,
            '_loc' => $this->stockBin->getLocation()->getName(),
            '_reel' => $this->stockBin->getBinStyle(),
        ])
            ->atPath("stockBin")
            ->addViolation();
    }
}
