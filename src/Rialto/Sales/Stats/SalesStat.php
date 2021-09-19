<?php

namespace Rialto\Sales\Stats;

use Rialto\Exception\InvalidDataException;
use Rialto\Purchasing\LeadTime\LeadTimeCalculator;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Sales\Price\Orm\ProductPriceRepository;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Level\StockLevelService;

/**
 * Statistics about the sales rate and stock level of a single stock item.
 */
class SalesStat implements Item
{
    const STATUS_IN_STOCK = 'instock';
    const STATUS_ON_ORDER = 'onorder';
    const STATUS_INSUFFICIENT = 'insufficient';

    /** @var PhysicalStockItem */
    private $stockItem;

    /** @var Facility */
    private $location;

    /** @var float */
    private $qtyInStock;
    private $qtyAllocated;
    private $numDays;
    private $targetDays;
    private $qtyShipped = 0;
    private $qtyBacklog = 0;
    private $orderPoint = null;

    /** @var StockProducer[] */
    private $openOrders = [];

    /** @var double|null */
    private $price = null;

    /**
     * The total lead time, in days.
     * @var int|null
     */
    private $leadTime = null;

    /** @var \Exception|null */
    private $leadTimeError = null;

    public function __construct(PhysicalStockItem $item, Facility $location)
    {
        $this->stockItem = $item;
        $this->location = $location;
    }

    public function loadStockLevels(StockLevelService $service)
    {
        $this->qtyInStock = $service->getQtyInStock($this->location, $this->stockItem);
        $this->qtyAllocated = $service->getQtyAllocated($this->location, $this->stockItem);
        $this->openOrders = $service->getAllOpenProducers($this->stockItem);
        $this->orderPoint = $service->getTotalOrderPoint($this->stockItem);
    }

    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function getSku()
    {
        return $this->stockItem->getSku();
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function isPurchased()
    {
        return $this->stockItem->isPurchased();
    }

    public function isManufactured()
    {
        return $this->stockItem->isManufactured();
    }

    public function getDescription()
    {
        return $this->stockItem->getDescription();
    }

    public function setNumDays($numDays)
    {
        if ($numDays <= 0) {
            throw new \InvalidArgumentException('Argument "numDays" must be positive');
        }
        $this->numDays = $numDays;
    }

    public function setTargetDays($target)
    {
        $this->targetDays = $target;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice(ProductPriceRepository $repo)
    {
        $this->price = $repo->findByItemAndSalesType($this);
    }

    /** @return int|null */
    public function getLeadTime()
    {
        return $this->leadTime;
    }

    public function setLeadTime(LeadTimeCalculator $calculator)
    {
        try {
            $leadTime = $calculator->forStockItem(
                $this->stockItem, $this->getQtyToOrder()
            );
            $this->leadTime = $leadTime->getTotalDays();
        } catch (InvalidDataException $ex) {
            $this->leadTime = null;
            $this->leadTimeError = $ex;
        }
    }

    public function getLeadTimeError()
    {
        return $this->leadTimeError;
    }

    public function addQtyBacklog($qty)
    {
        $this->qtyBacklog += $qty;
    }

    public function addQtyShipped($qty)
    {
        $this->qtyShipped += $qty;
    }

    public function getDaysRemaining()
    {
        $available = $this->getQtyAvailable();
        $orderRate = $this->getOrderRate();
        return ($orderRate > 0) ? ($available / $orderRate) : null;
    }

    public function getQtyOnHand()
    {
        return $this->qtyInStock;
    }

    public function getQtyAvailable()
    {
        return $this->getQtyOnHand() - $this->qtyAllocated;
    }

    public function getQtyBacklog()
    {
        return $this->qtyBacklog;
    }

    public function getQtyNeeded()
    {
        $leadTime = (int) $this->getLeadTime();
        $orderRate = $this->getOrderRate();
        $targetQty = ceil(($this->targetDays + $leadTime) * $orderRate);
        $available = $this->getQtyAvailable();
        return max($targetQty - $available, 0);
    }

    public function getOrderPoint()
    {
        return $this->orderPoint;
    }

    public function getEconomicOrderQty()
    {
        return $this->stockItem->getEconomicOrderQty();
    }

    /**
     * What we think the economic order qty should be based on our sales rate.
     */
    public function getTargetOrderQty()
    {
        return $this->targetDays * $this->getOrderRate();
    }

    /**
     * What we think the order point should be based on our sales rate.
     */
    public function getTargetOrderPoint()
    {
        if (null === $this->leadTime) {
            return null;
        }
        return $this->getOrderRate() * $this->leadTime;
    }

    public function getQtyToOrder()
    {
        return max($this->getQtyNeeded(), $this->getEconomicOrderQty());
    }

    public function getQtyOnOrder()
    {
        $total = 0;
        foreach ($this->openOrders as $producer) {
            $total += $producer->getQtyRemaining();
        }
        return $total;
    }

    /** @return StockProducer[] */
    public function getOpenOrders()
    {
        return $this->openOrders;
    }

    public function getQtyShipped()
    {
        return $this->qtyShipped;
    }

    /**
     * The number of products ordered per day.
     * @return float
     */
    public function getOrderRate()
    {
        return ($this->qtyShipped + $this->qtyBacklog) / $this->numDays;
    }

    /**
     * True if the number of days' worth of stock remaining is greater than
     * the lead time.
     *
     * This basically means we won't run out.
     *
     * @return boolean
     */
    public function isStockSufficient()
    {
        if ($this->getOrderRate() <= 0) {
            return true;
        } elseif (null === $this->leadTime) {
            return false;
        } else {
            return $this->getDaysRemaining() >= $this->leadTime;
        }
    }

    public function isEnoughOnOrder()
    {
        $onOrder = $this->getQtyOnOrder();
        return ($onOrder > 0) && ($onOrder >= $this->getQtyNeeded());
    }

    public function getStockStatus()
    {
        if ($this->isStockSufficient()) {
            return self::STATUS_IN_STOCK;
        } elseif ($this->isEnoughOnOrder()) {
            return self::STATUS_ON_ORDER;
        } else {
            return self::STATUS_INSUFFICIENT;
        }
    }
}
