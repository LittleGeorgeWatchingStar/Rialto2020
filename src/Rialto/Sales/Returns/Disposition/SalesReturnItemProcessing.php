<?php

namespace Rialto\Sales\Returns\Disposition;

use Rialto\Sales\Returns\SalesReturn;
use Rialto\Sales\Returns\SalesReturnItem;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;


/**
 * Subclasses store the results of the various stages of processing
 * a sales return item, such as receiving and testing.
 */
abstract class SalesReturnItemProcessing
implements Item
{
    /** @var SalesReturnItem */
    protected $rmaItem;

    /**
     * Instructions for the warehouse staff.
     *
     * @var SalesReturnInstructions
     */
    public $instructions;

    public function __construct(SalesReturnItem $rmaItem)
    {
        $this->rmaItem = $rmaItem;
        $this->instructions = new SalesReturnInstructions($rmaItem);
    }

    public function getSalesReturnItem(): SalesReturnItem
    {
        return $this->rmaItem;
    }

    public function getSalesReturn(): SalesReturn
    {
        return $this->rmaItem->getSalesReturn();
    }

    public function getSku()
    {
        return $this->rmaItem->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @return StockItem */
    public function getStockItem()
    {
        return $this->rmaItem->getStockItem();
    }

    /** @return SalesReturnInstructions */
    public function getInstructions()
    {
        return $this->instructions;
    }
}
