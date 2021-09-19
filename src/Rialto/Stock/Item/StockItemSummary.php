<?php

namespace Rialto\Stock\Item;

use Rialto\Web\Serializer\ListableFacade;

class StockItemSummary
{
    use ListableFacade;

    /** @var StockItem */
    private $item;

    public function __construct(StockItem $item)
    {
        $this->item = $item;
    }

    public function getId()
    {
        return $this->getSku();
    }

    public function getSku()
    {
        return $this->item->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        return $this->getSku();
    }

    public function getName()
    {
        return $this->item->getName();
    }

    /** @deprecated use getName() instead */
    public function getDescription()
    {
        return $this->getName();
    }

    public function getLongDescription()
    {
        return $this->item->getLongDescription();
    }

    public function getCategory()
    {
        return $this->item->getCategory()->getId();
    }

    public function getStockType(): string
    {
        return $this->item->getStockType();
    }

    public function getPackage()
    {
        return $this->item->getPackage();
    }

    public function getPartValue()
    {
        return $this->item->getPartValue();
    }

    public function getStandardCost()
    {
        return $this->item->getStandardCost();
    }

    public function getCountryOfOrigin()
    {
        return $this->item->getCountryOfOrigin();
    }

    public function getEconomicOrderQty()
    {
        return $this->item->getEconomicOrderQty();
    }

    /** @deprecated use getEconomicOrderQty() instead */
    public function getOrderQuantity()
    {
        return $this->getEconomicOrderQty();
    }

    public function getEccnCode()
    {
        return $this->item->getEccnCode();
    }

    public function getRohs()
    {
        return $this->item->getRoHS();
    }

    public function getHarmonizationCode()
    {
        $hts = $this->item->getHarmonizationCode();
        return $hts ? $hts->getId() : null;
    }

    public function isEsdSensitive(): bool
    {
        return $this->item->isEsdSensitive();
    }

    public function getShippingVersion()
    {
        return (string) $this->item->getShippingVersion();
    }

    public function getAutoBuildVersion()
    {
        return (string) $this->item->getAutoBuildVersion();
    }

    public function getDiscontinued()
    {
        return $this->item->getDiscontinued();
    }

    public function isCloseCount()
    {
        return (bool) $this->item->isCloseCount();
    }

    public function getWeight()
    {
        return $this->item->getWeight();
    }
}
