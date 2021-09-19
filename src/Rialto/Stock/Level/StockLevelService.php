<?php

namespace Rialto\Stock\Level;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Rialto\Allocation\Allocation\Orm\StockAllocationRepository;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Purchasing\Producer\Orm\StockProducerRepository;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Level\Orm\StockLevelStatusRepository;

/**
 * A service for reporting the quantity of an item in stock, allocated, on
 * order, etc.
 */
class StockLevelService
{
    /** @var ObjectManager */
    protected $om;

    public function __construct(ObjectManager $dbm)
    {
        $this->om = $dbm;
    }

    /* IN STOCK */

    /** @return int|float */
    public function getQtyInStock(Facility $location, PhysicalStockItem $item, Version $version = null)
    {
        $repo = $this->getStockRepo();
        return $repo->getQtyInStock($location, $item, $version);
    }

    /** @return int|float */
    public function getTotalQtyInStock(PhysicalStockItem $item, Version $version = null)
    {
        $repo = $this->getStockRepo();
        return $repo->getTotalQtyInStock($item, $version);
    }

    /** @return StockBinRepository|ObjectRepository */
    protected function getStockRepo()
    {
        return $this->om->getRepository(StockBin::class);
    }

    /**
     * The sum of the order points for $item at each location.
     *
     * @return integer|float
     */
    public function getTotalOrderPoint(PhysicalStockItem $item)
    {
        /** @var $repo StockLevelStatusRepository */
        $repo = $this->om->getRepository(StockLevelStatus::class);
        $levels = $repo->findByItem($item);
        return array_sum(array_map(function(StockLevelStatus $level) {
            return $level->getOrderPoint();
        }, $levels));
    }


    /* ON ORDER */

    /** @return StockProducer[] */
    public function getAllOpenProducers(PhysicalStockItem $item, Version $version = null)
    {
        $repo = $this->getProducerRepo($item);
        return $repo->findAllOpenProducers($item, $version);
    }

    /** @return int|float */
    public function getTotalQtyOnOrder(PhysicalStockItem $item, Version $version = null)
    {
        $repo = $this->getProducerRepo($item);
        return $repo->getTotalQtyOnOrder($item, $version);
    }

    /** @return int|float */
    public function getQtyOnOrder(Facility $location, PhysicalStockItem $item, Version $version = null)
    {
        $repo = $this->getProducerRepo($item);
        return $repo->getQtyOnOrder($location, $item, $version);
    }

    /** @return StockProducerRepository|ObjectRepository */
    private function getProducerRepo(PhysicalStockItem $item)
    {
        $class = $item->isManufactured()
            ? WorkOrder::class
            : PurchaseOrderItem::class;
        return $this->om->getRepository($class);
    }


    /* ALLOCATED */

    /** @return int */
    public function getQtyAllocated(
        Facility $location,
        StockItem $item,
        Version $version = null)
    {
        $repo = $this->getAllocationRepo();
        return $repo->getQtyUndelivered($location, $item, $version);
    }

    /** @return int|float */
    public function getTotalQtyAllocated(PhysicalStockItem $item, Version $version = null)
    {
        $mapper = $this->getAllocationRepo();
        return $mapper->getTotalQtyUndelivered($item, $version);
    }

    /** @return int|float */
    public function getTotalQtyUnallocated(PhysicalStockItem $item, Version $version = null)
    {
        return $this->getTotalQtyInStock($item, $version)
            - $this->getTotalQtyAllocated($item, $version);
    }

    /** @return StockAllocationRepository|ObjectRepository */
    private function getAllocationRepo()
    {
        return $this->om->getRepository(StockAllocation::class);
    }
}
