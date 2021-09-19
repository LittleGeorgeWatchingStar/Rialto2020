<?php

namespace Rialto\Stock\Level;

use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Stock\Item\AssemblyStockItem;

/**
 * Represents the stock level for an assembly item at a specific location.
 */
class AssemblyStockLevel implements AvailableStockLevel
{
    /** @var AssemblyStockItem */
    private $item;

    /** @var AvailableStockLevel[] */
    private $levels;

    public function __construct(AssemblyStockItem $item, array $levels)
    {
        $this->item = $item;
        $this->levels = $levels;
    }

    public function getSku()
    {
        return $this->item->getSku();
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getLocation()
    {
        $level = reset($this->levels);
        assert($level instanceof StockLevel);
        return $level->getLocation();
    }

    public function getQtyInStock()
    {
        return $this->doCount($this->indexQtyInStock());
    }

    public function getQtyAvailable()
    {
        return $this->doCount($this->indexQtyAvailable());
    }

    private function doCount(array $index)
    {
        $remaining = null;

        /** @var $bomItem BomItem */
        foreach ($this->item->getBom() as $bomItem) {
            $sku = $bomItem->getSku();
            $quantity = isset($index[$sku]) ? $index[$sku] : 0; // TODO php7

            /* How many of the parent item can we built with this quantity
             * of the component? */
            $parentQty = floor(
                $quantity / $bomItem->getQuantity()
            );

            /* The total quantity of the parent is the minimum of all above
             * quantities. */
            if (null === $remaining) {
                $remaining = $parentQty;
            } else {
                $remaining = min($remaining, $parentQty);
            }
        }
        return (int) $remaining;
    }

    private function indexQtyInStock()
    {
        $index = [];
        foreach ($this->levels as $status) {
            $index[$status->getSku()] = $status->getQtyInStock();
        }
        return $index;
    }

    private function indexQtyAvailable()
    {
        $index = [];
        foreach ($this->levels as $status) {
            $index[$status->getSku()] = $status->getQtyAvailable();
        }
        return $index;
    }

    /**
     * @return AvailableStockLevel[]
     */
    public function getComponents()
    {
        return $this->levels;
    }
}
