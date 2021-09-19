<?php

namespace Rialto\Manufacturing\Component;

use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Measurement\Temperature\TemperatureRange;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\VersionedItem;

/**
 * A component that is used to build another item.
 */
interface Component extends VersionedItem
{
    /** @return PhysicalStockItem */
    public function getStockItem();

    /**
     * The quantity of this component required per unit of the parent item.
     * @return int
     */
    public function getUnitQty();

    /** @return string */
    public function getPackage();

    /** @return string */
    public function getPartValue();

    /** @return string */
    public function getManufacturerCode();

    /** @return TemperatureRange */
    public function getTemperatureRange();

    /** @return string[] */
    public function getDesignators();

    /** @return WorkType */
    public function getWorkType();

    /**
     * The description of the parent item.
     * @return string
     */
    public function getDescription();

    /**
     * @param string $category The category ID
     * @return bool
     */
    public function isCategory($category);
}
