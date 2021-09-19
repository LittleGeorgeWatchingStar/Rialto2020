<?php

namespace Rialto\Stock\Level;

use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\ValidSku;
use Rialto\Stock\Transfer\TransferItem;

class StockLevelBindingTransfer
{
    /**
     * @var string
     *
     * @Assert\NotNull
     * @Assert\NotBlank(message="SKU is required.")
     * @ValidSku(groups={"create"})
     */
    private $stockItemSku;
    /**
     * The location to which stock will be transferred.
     * @var Facility
     * @Assert\NotNull
     */
    private $facility;
    private $stockItem;
    private $transferItem;
    /**
     * @Assert\Range(min = 0)
     */
    private $qtyInTransfer = 0;
    /**
     * @Assert\Range(min = 0)
     */
    private $qtyTransferAllocated = 0;


    public function __construct(StockItem $item, TransferItem $transferItem, Facility $facility)
    {
        $this->stockItem = $item;
        $this->transferItem = $transferItem;
        $this->stockItemSku = $item->getSku();
        $this->facility = $facility;
    }

    public function getStockItemSku(): string
    {
        return $this->stockItemSku;
    }

    public function getFacility(): Facility
    {
        return $this->facility;
    }

    /** @return StockItem */
    public function getStockItem(): StockItem
    {
        return $this->stockItem;
    }

    /** @return TransferItem */
    public function getTransferItem(): TransferItem
    {
        return $this->transferItem;
    }

    public function getTransferQty(){
        $this->qtyInTransfer = $this->transferItem->getQtySent();
        return $this->qtyInTransfer;
    }

    public function getTransferQtyAllocated(){
        $this->qtyTransferAllocated = $this->transferItem->getStockBin()->getQtyAllocated();
        return $this->qtyTransferAllocated;
    }
}
