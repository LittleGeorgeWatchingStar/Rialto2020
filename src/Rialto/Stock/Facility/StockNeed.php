<?php

namespace Rialto\Stock\Facility;

use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Figures out supply and demand for a stock item at a location.
 */
class StockNeed
{
    /**
     * @var PhysicalStockItem
     */
    private $item;

    /**
     * @var Supplier
     */
    private $supplier = null;

    /**
     * @var PurchasingData
     * @Assert\NotNull(message="No purchasing data.")
     * @Assert\Valid
     */
    private $purchData = null;

    /**
     * @var int
     */
    private $qtyInStock;
    private $qtyOnOrder;
    private $qtyAllocated;
    private $orderPoint;

    /** @var StockProducer */
    private $poItem = null;


    public function __construct(
        PhysicalStockItem $item, $qtyInStock, $qtyOnOrder,
        $qtyAllocated, $orderPoint)
    {
        $this->item = $item;
        $this->qtyInStock = $qtyInStock;
        $this->qtyOnOrder = $qtyOnOrder;
        $this->qtyAllocated = $qtyAllocated;
        $this->orderPoint = $orderPoint;
    }

    public function getQtyOnHand()
    {
        return $this->qtyInStock;
    }

    public function getQtyOnOrder()
    {
        return $this->qtyOnOrder;
    }

    public function getQtyAllocated()
    {
        return $this->qtyAllocated;
    }

    public function getQtyAvailable()
    {
        return $this->qtyInStock + $this->qtyOnOrder - $this->qtyAllocated;
    }

    public function getQtyToOrder()
    {
        $deficit = $this->orderPoint - $this->getQtyAvailable();
        if ($deficit <= 0) {
            return 0;
        }
        $eoq = $this->getOrderQuantity();
        if ($eoq <= 0) {
            return 0;
        }
        return (int) ($eoq * ceil($deficit / $eoq));
    }

    /** @Assert\Range(min=1, minMessage="No order point.") */
    public function getOrderPoint()
    {
        return $this->orderPoint;
    }

    /** @return StockItem */
    public function getStockItem()
    {
        return $this->item;
    }

    public function getStockCode()
    {
        return $this->item->getSku();
    }

    public function getDescription()
    {
        return $this->item->getDescription();
    }

    /** @Assert\Range(min=1, minMessage="No EOQ.") */
    public function getOrderQuantity()
    {
        return $this->item->getOrderQuantity();
    }

    /** @return PurchasingData|null */
    public function loadPurchasingData(PurchasingDataRepository $repo)
    {
        $this->purchData = $repo->findPreferred($this->item, $this->getQtyToOrder());
        $this->supplier = $this->purchData ? $this->purchData->getSupplier() : null;
        return $this->purchData;
    }

    /** @return PurchasingData|null */
    public function getPurchasingData()
    {
        return $this->purchData;
    }

    /** @Assert\Callback */
    public function assertPurchasingDataIsActive(ExecutionContextInterface $context)
    {
        if ($this->purchData && (! $this->purchData->isActive())) {
            $context->buildViolation(
                "Preferred purchasing data record is not active.")
                ->atPath('purchData')
                ->addViolation();
        }
    }

    public function getSupplier()
    {
        return $this->purchData->getSupplier();
    }

    public function setPurchaseOrderItem(StockProducer $poItem)
    {
        $this->poItem = $poItem;
    }

    public function getOrderNo()
    {
        return $this->poItem ? $this->poItem->getOrderNumber() : null;
    }

    public function getStockAccount()
    {
        return $this->poItem ? $this->poItem->getGLAccount() : null;
    }
}
