<?php

namespace Rialto\Stock\Level;

use Doctrine\ORM\EntityManagerInterface;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Level\Orm\StockLevelQueryBuilder;

class CompleteStockLevel implements AvailableStockLevel
{
    /** @var string */
    private $sku;

    /** @var Version */
    private $version;

    /** @var Facility */
    private $location;

    /** @var float */
    private $qtyInStock;

    /** @var float */
    private $qtyAllocated;

    /** @var float */
    private $orderPoint;

    public function __construct(string $sku,
                                Version $version,
                                Facility $location,
                                float $qtyInStock,
                                float $qtyAllocated,
                                float $orderPoint)
    {
        $this->sku = $sku;
        $this->version = $version;
        $this->location = $location;
        $this->qtyInStock = $qtyInStock;
        $this->qtyAllocated = $qtyAllocated;
        $this->orderPoint = $orderPoint;
    }

    public static function createBuilder(EntityManagerInterface $em)
    {
        return new StockLevelQueryBuilder($em);
    }

    /**
     * @return int|float The unallocated quantity available for new orders.
     */
    public function getQtyAvailable()
    {
        return $this->qtyInStock - $this->qtyAllocated;
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function getFullSku(): string
    {
        return $this->sku . $this->getVersion()->getStockCodeSuffix();
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getQtyInStock()
    {
        return $this->qtyInStock;
    }

    public function getQtyAllocated()
    {
        return $this->qtyAllocated;
    }

    public function getOrderPoint()
    {
        return $this->orderPoint;
    }
}
