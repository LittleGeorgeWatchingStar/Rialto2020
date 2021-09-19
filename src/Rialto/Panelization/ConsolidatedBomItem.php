<?php

namespace Rialto\Panelization;

use Rialto\Manufacturing\Component\Component;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Measurement\Temperature\TemperatureRange;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;

class ConsolidatedBomItem implements Component
{
    /** @var Component */
    private $prototype;

    private $unitQty = 0;

    private $designators = [];

    public function __construct(Component $c)
    {
        $this->prototype = $c;
    }

    public function increment(Component $c, $boardId)
    {
        assertion($c->getFullSku() == $this->getFullSku());
        $this->unitQty += $c->getUnitQty();
        $newDesignators = array_map(function ($des) use ($boardId) {
            return "$des-$boardId";
        }, $c->getDesignators());
        $this->designators = array_merge($this->designators, $newDesignators);
    }

    /**
     * The quantity of this component required per unit of the parent item.
     * @return int
     */
    public function getUnitQty()
    {
        return $this->unitQty;
    }

    /**
     * The name of the parent item.
     * @return string
     */
    public function getDescription()
    {
        return $this->prototype->getDescription();
    }

    /** @return string */
    public function getPackage()
    {
        return $this->prototype->getPackage();
    }

    /** @return string */
    public function getPartValue()
    {
        return $this->prototype->getPartValue();
    }

    /** @return string */
    public function getManufacturerCode()
    {
        return $this->prototype->getManufacturerCode();
    }

    /** @return TemperatureRange */
    public function getTemperatureRange()
    {
        return $this->prototype->getTemperatureRange();
    }

    /** @return string[] */
    public function getDesignators()
    {
        return $this->designators;
    }

    public function getSku()
    {
        return $this->prototype->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @return Version */
    public function getVersion()
    {
        return $this->prototype->getVersion();
    }

    /** @return Customization|null */
    public function getCustomization()
    {
        return $this->prototype->getCustomization();
    }

    /** @return StockItem */
    public function getStockItem()
    {
        return $this->prototype->getStockItem();
    }

    public function getFullSku()
    {
        return $this->prototype->getFullSku();
    }

    /** @deprecated */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    /** @return WorkType */
    public function getWorkType()
    {
        return $this->prototype->getWorkType();
    }

    /**
     * @param string $category The category ID
     * @return bool
     */
    public function isCategory($category)
    {
        return $this->prototype->isCategory($category);
    }

}
