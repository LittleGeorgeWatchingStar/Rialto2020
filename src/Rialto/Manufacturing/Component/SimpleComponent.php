<?php

namespace Rialto\Manufacturing\Component;

use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Measurement\Temperature\TemperatureRange;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\Version\Version;

/**
 * A basic implementation of Component.
 */
class SimpleComponent implements Component
{
    /** @var PhysicalStockItem */
    private $item;

    /** @var int|float */
    private $unitQty;

    /** @var string[] */
    private $designators = [];

    /** @var Version */
    public $version;

    /** @var Customization|null */
    public $customization = null;

    /** @var WorkType|null */
    public $workType = null;

    /** @var string|null */
    public $manufacturerCode = null;

    public function __construct(PhysicalStockItem $item, $unitQty, array $designators)
    {
        $this->item = $item;
        $this->unitQty = $unitQty;
        $this->designators = $designators;
        $this->version = $item->getAutoBuildVersion();
    }

    /**
     * The name of the parent item.
     * @return string
     */
    public function getDescription()
    {
        return $this->item->getName();
    }

    /**
     * The quantity of this component required per unit of the parent item.
     * @return int
     */
    public function getUnitQty()
    {
        return $this->unitQty;
    }

    /** @return string[] */
    public function getDesignators()
    {
        return $this->designators;
    }

    /** @return WorkType */
    public function getWorkType()
    {
        return $this->workType;
    }

    /** @return string */
    public function getPackage()
    {
        return $this->item->getPackage();
    }

    /** @return string */
    public function getPartValue()
    {
        return $this->item->getPartValue();
    }

    /** @return string */
    public function getManufacturerCode()
    {
        return $this->manufacturerCode;
    }

    /** @return TemperatureRange */
    public function getTemperatureRange()
    {
        return $this->item->getTemperatureRange();
    }

    /**
     * @param string $category The category ID
     * @return bool
     */
    public function isCategory($category)
    {
        return $this->item->isCategory($category);
    }

    /** @return Customization|null */
    public function getCustomization()
    {
        return $this->customization;
    }

    public function getSku()
    {
        return $this->item->getSku();
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
        return $this->version;
    }

    /** @return PhysicalStockItem */
    public function getStockItem()
    {
        return $this->item;
    }

    /**
     * @deprecated use getFullSku() instead.
     */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    /**
     * @return string The full SKU, including revision and customization
     *   codes; eg "GS3503F-R1234-C10085"
     */
    public function getFullSku()
    {
        return $this->item->getSku()
        . $this->version->getStockCodeSuffix()
        . Customization::getStockCodeSuffix($this->customization);
    }
}
