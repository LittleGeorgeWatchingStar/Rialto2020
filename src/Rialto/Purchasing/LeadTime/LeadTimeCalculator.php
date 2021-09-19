<?php

namespace Rialto\Purchasing\LeadTime;

use Rialto\Allocation\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Catalog\PurchasingDataException;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;

/**
 * Calculates the lead times for stock items.
 */
class LeadTimeCalculator
{
    /** @var LeadTimeGateway */
    private $gateway;

    public function __construct(LeadTimeGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * The lead time for the work order plus the lead time of all subcomponents.
     */
    public function forWorkOrder(WorkOrder $wo): LeadTime
    {
        $purchData = $wo->getPurchasingData();
        $orderQty = $wo->getQtyOrdered();
        $leadTime = new LeadTime($purchData, $orderQty);
        foreach ($wo->getRequirements() as $woReq) {
            $leadTime->addComponent($this->forRequirement($woReq));
        }
        return $leadTime;
    }

    /**
     * The lead time for the item plus the lead time of all subcomponents.
     */
    public function forRequirement(Requirement $requirement): LeadTime
    {
        return $this->forItemAndVersion(
            $requirement->getStockItem(),
            $requirement->getVersion(),
            $requirement->getTotalQtyOrdered()
        );
    }

    /**
     * The total lead time, in days, for this item, plus the lead any
     * manufactured subcomponents.
     */
    public function forStockItem(StockItem $item, $orderQty = null): LeadTime
    {
        $orderQty = $orderQty ?: $item->getEconomicOrderQty();
        $orderQty = max($orderQty, 1);
        $version = $item->getAutoBuildVersion();
        return $this->forItemAndVersion($item, $version, $orderQty);
    }

    private function forItemAndVersion(StockItem $item,
                                       Version $version,
                                       $orderQty): LeadTime
    {
        $purchData = $this->gateway->findPurchasingData($item, $version, $orderQty);
        if (!$purchData) {
            $msg = "No purchasing data for $item that matches the constraints";
            throw new PurchasingDataException($item, $msg);
        }
        $leadTime = new LeadTime($purchData, $orderQty);
        if ($item->isManufactured()) {
            $this->addBom($leadTime, $version);
        }
        return $leadTime;
    }

    private function addBom(LeadTime $parent, Version $version)
    {
        $bom = $this->gateway->getConsignmentBom($parent->getPurchasingData(), $version);
        foreach ($bom as $bomItem) {
            $component = $bomItem->getComponent();
            $componentVer = $bomItem->getVersion();
            $componentQty = $parent->getOrderQty() * $bomItem->getUnitQty();
            if ($this->gateway->isInStock($component, $componentVer, $componentQty)) {
                continue;
            }
            $parent->addComponent($this->forItemAndVersion(
                $component,
                $componentVer,
                $componentQty
            ));
        }
    }
}
