<?php

namespace Rialto\Stock\Consumption;

use Rialto\Purchasing\Catalog\PurchasingDataException;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Level\StockLevelService;

/**
 * Statistics about the consumption of a particular version of a particular
 * stock item.
 */
class StockConsumptionStat
{
    /** @var StockItem */
    private $item;
    private $version;
    private $qtyConsumed = 0;
    private $qtyOnHand = 0;
    private $qtyOnOrder = 0;
    private $orderPoint = null;
    private $purchasingData;
    private $numDays = 1;

    /** @var bool[] */
    private $usedBy = [];

    /** @var Supplier */
    private $supplier;

    /** @var string[] */
    private $errors = [];

    public function __construct(Item $item)
    {
        $this->item = $item->getStockItem();
        $this->version = $item->getVersion();

        $this->supplier = $this->item->getPreferredSupplier();
        if ( $this->supplier ) {
            $this->purchasingData = $this->supplier->getPurchasingData($this->item);
        }
    }

    public function loadStockLevels(StockLevelService $stockLevels)
    {
        $this->qtyOnHand = $stockLevels->getTotalQtyUnallocated($this->item, $this->version);
        $this->qtyOnOrder = $stockLevels->getTotalQtyOnOrder($this->item, $this->version);
        $this->orderPoint = $stockLevels->getTotalOrderPoint($this->item);
    }

    public function getBom()
    {
        if (! $this->item->hasSubcomponents() ) {
            return null;
        }
        return $this->item->getBom($this->version);
    }

    public function addQtyConsumed($qty)
    {
        $this->qtyConsumed += $qty;
    }

    public function getQtyConsumed()
    {
        return $this->qtyConsumed;
    }

    public function getQtyPerDay()
    {
        return $this->qtyConsumed / $this->numDays;
    }

    public function getDaysRemaining()
    {
        return $this->getQtyOnHand() / $this->getQtyPerDay();
    }

    public function getStockItem()
    {
        return $this->item;
    }

    public function getStockCode()
    {
        return $this->item->getSku();
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getQtyOnHand()
    {
        return $this->qtyOnHand;
    }

    public function getQtyOnOrder()
    {
        return $this->qtyOnOrder;
    }

    public function getStandardCost()
    {
        return $this->item->getStandardCost();
    }

    public function getLeadTime()
    {
        try {
            return $this->purchasingData ?
                $this->purchasingData->getLeadTimeAtEoq() :
                null;
        } catch (PurchasingDataException $ex) {
            $this->errors[] = $ex->getMessage();
            return null;
        }
    }

    public function getCalculatedOrderPoint()
    {
        $leadTime = $this->getLeadTime();
        return $leadTime ? $this->getQtyPerDay() * $leadTime : null;
    }

    public function getActualOrderPoint()
    {
        return $this->orderPoint;
    }

    public function isLow()
    {
        return $this->qtyOnHand < $this->getCalculatedOrderPoint();
    }

    public function isDepleted()
    {
        return ($this->qtyOnHand + $this->qtyOnOrder) < $this->getCalculatedOrderPoint();
    }

    public function getPreferredSupplier()
    {
        return $this->supplier;
    }

    public function setNumOfDays($days)
    {
        $this->numDays = $days > 0 ? $days : 1;
    }

    public function addParentItem(Item $parent = null)
    {
        if ($parent) {
            $this->usedBy[$parent->getSku()] = true;
        }
    }

    public function getNumParents()
    {
        return count($this->usedBy);
    }

    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * @return string[]
     */
    public function getErrors()
    {
        return array_unique($this->errors);
    }
}
