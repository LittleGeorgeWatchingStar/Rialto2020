<?php

namespace Rialto\Stock\Level;


use Rialto\Stock\Item;

class StockLevelUpdate implements Item
{
    /** @var StockLevelStatus */
    private $status;

    /** @var int */
    private $currentInStock;

    /** @var int */
    private $currentAllocated;

    public function __construct(StockLevelStatus $status, $currentInStock, $currentAllocated)
    {
        $this->status = $status;
        $this->currentInStock = $currentInStock;
        $this->currentAllocated = $currentAllocated;
    }

    public function getSku()
    {
        return $this->status->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /**
     * @return StockLevelStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function applyUpdate()
    {
        $this->status->update($this->currentInStock, $this->currentAllocated);
    }

    public function __toString()
    {
        return sprintf('%s from %s to %s',
            $this->status,
            number_format($this->status->getQtyAvailable()),
            number_format($this->getCurrentAvailable()));
    }

    private function getCurrentAvailable()
    {
        return $this->currentInStock - $this->currentAllocated;
    }
}
