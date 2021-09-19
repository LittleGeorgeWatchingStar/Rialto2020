<?php

namespace Rialto\Sales\Order\Allocation;

use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Allocation\Requirement\Requirement as RequirementAbstract;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\Version\Version;

/**
 * An item that is required for a SalesOrderDetail.
 *
 * @see SalesOrderDetail
 */
class Requirement extends RequirementAbstract
{
    const CONSUMER_TYPE = 'SalesOrderDetail';

    /**
     * @var SalesOrderDetail
     */
    private $orderItem;

    /**
     * Factory function for assembly items.
     *
     * @return Requirement
     */
    public static function fromAssembly(SalesOrderDetail $soItem, BomItem $bomItem)
    {
        assertion($soItem->isAssembly());
        $req = new self($bomItem->getComponent());
        $req->setVersion($bomItem->getVersion());
        $req->setUnitQtyNeeded($bomItem->getUnitQty());
        $req->orderItem = $soItem;
        return $req;
    }

    /**
     * Factory function for physical items.
     *
     * @return Requirement
     */
    public static function fromPhysicalItem(SalesOrderDetail $soItem)
    {
        $stockItem = $soItem->getStockItem();
        assertion($stockItem instanceof PhysicalStockItem);
        $req = new self($stockItem);
        $req->setVersion($soItem->getVersion());
        $req->orderItem = $soItem;
        $req->setUnitQtyNeeded(1);
        $req->setCustomization($soItem->getCustomization());
        return $req;
    }

    /**
     * @return StockConsumer
     */
    public function getConsumer()
    {
        return $this->orderItem;
    }

    public function getConsumerDescription()
    {
        return 'sales order '. $this->orderItem->getOrderNumber();
    }

    public function getConsumerType()
    {
        return self::CONSUMER_TYPE;
    }

    public function getTotalQtyOrdered()
    {
        return $this->getUnitQtyNeeded() * $this->orderItem->getQtyOrdered();
    }

    private function getTotalQtyInvoiced()
    {
        return $this->getUnitQtyNeeded() * $this->orderItem->getQtyInvoiced();
    }

    public function getTotalQtyUndelivered()
    {
        if ($this->orderItem->isCompleted()) {
            return 0;
        }
        return $this->getTotalQtyOrdered() - $this->getTotalQtyInvoiced();
    }

    public function setVersion(Version $version)
    {
        $this->version = (string) $version;
    }
}
