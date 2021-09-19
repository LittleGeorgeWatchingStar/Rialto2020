<?php

namespace Rialto\Stock\Level;

use Doctrine\ORM\EntityManagerInterface;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\ValidSku;
use Rialto\Stock\Transfer\Orm\TransferItemRepository;
use Rialto\Stock\Transfer\TransferItem;

class StockLevelTransferItemCombineBins
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
    private $stockLevelLocation;
    private $stockItem;
    private $stockLevel;
    /**
     * @Assert\Range(min = 0)
     */
    private $qtyInStock = 0;
    /**
     * @Assert\Range(min = 0)
     */
    private $qtyAllocated = 0;
    /**
     * @Assert\Range(min = 0)
     */
    private $qtyInTransferTotal = 0;
    /**
     * @Assert\Range(min = 0)
     */
    private $qtyTransferAllocatedTotal = 0;
    /**
     * @Assert\Range(min = 0)
     */
    private $qtyAvailableTotal = 0;
    /**
     * @var TransferItemRepository
     */
    private $transferItemRepo;

    /** @var StockLevelBindingTransfer[] */
    private $stockLevelBindingTransferArray = [];

    public function __construct(StockItem $item, CompleteStockLevel $stockLevel, Facility $stockLevelLocation, EntityManagerInterface $em)
    {
        $this->stockItem = $item;
        $this->stockLevel = $stockLevel;
        $this->stockItemSku = $item->getSku();
        $this->stockLevelLocation = $stockLevelLocation;
        $this->transferItemRepo = $em->getRepository(TransferItem::class);

        $transferItems = $this->transferItemRepo->findTransferItembyStockItem($this->stockItem);

        foreach ($transferItems as $transferItem) {
            $transferDestination = $transferItem->getDestination();
            if ($this->stockLevelLocation === $transferDestination) {
                $bindingBin = new StockLevelBindingTransfer($this->stockItem, $transferItem, $transferDestination);
                $this->stockLevelBindingTransferArray[] = $bindingBin;
            }
        }
        $this->setTransferQtyTotal();
        $this->setTransferQtyAllocatedTotal();
    }

    public function getStockItemSku(): string
    {
        return $this->stockItemSku;
    }

    public function getFacility(): Facility
    {
        return $this->stockLevelLocation;
    }

    /** @return StockItem */
    public function getStockItem(): StockItem
    {
        return $this->stockItem;
    }

    /** @return CompleteStockLevel */
    public function getCompleteStockLevel(): CompleteStockLevel
    {
        return $this->stockLevel;
    }

    /**
     * @return int|float In stock quantity available for new orders.
     */
    public function getQtyInStock()
    {
        $this->qtyInStock = $this->getCompleteStockLevel()->getQtyInStock();
        return $this->qtyInStock;
    }

    /**
     * @return int|float In stock quantity available for new orders.
     */
    public function getQtyAllocated()
    {
        $this->qtyAllocated = $this->getCompleteStockLevel()->getQtyAllocated();
        return $this->qtyAllocated;
    }

    private function setTransferQtyTotal(){
        foreach ($this->stockLevelBindingTransferArray as $stockLevelBindingTransfer) {
            $this->qtyInTransferTotal = $this->qtyInTransferTotal + $stockLevelBindingTransfer->getTransferQty();
        }
    }

    public function getTransferQtyTotal(){
        return $this->qtyInTransferTotal;
    }

    private function setTransferQtyAllocatedTotal(){
        foreach ($this->stockLevelBindingTransferArray as $stockLevelBindingTransfer) {
            $this->qtyTransferAllocatedTotal = $this->qtyTransferAllocatedTotal + $stockLevelBindingTransfer->getTransferQtyAllocated();
        }
    }

    public function getTransferQtyAllocatedTotal(){
        return $this->qtyTransferAllocatedTotal;
    }

    /**
     * @return int|float The unallocated quantity available for new orders.
     */
    public function getQtyAvailableTotal()
    {
        $this->qtyAvailableTotal = $this->qtyInStock + $this->qtyInTransferTotal - $this->qtyAllocated - $this->qtyTransferAllocatedTotal;
        return $this->qtyAvailableTotal;
    }
}
