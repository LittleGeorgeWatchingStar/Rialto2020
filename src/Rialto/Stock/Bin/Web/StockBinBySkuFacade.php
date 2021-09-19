<?php

namespace Rialto\Stock\Bin\Web;


use Rialto\Stock\Bin\StockBin;
use Rialto\Web\Serializer\ListableFacade;

class StockBinBySkuFacade
{
    use ListableFacade;

    /** @var StockBin */
    private $stockBin;

    public function __construct(StockBin $stockBin)
    {
        $this->stockBin = $stockBin;
    }

    public function getId()
    {
        $this->stockBin->getId();
    }

    public function getBinStyle()
    {
        $this->stockBin->getBinStyle();
    }

    public function getSku()
    {
        $this->stockBin->getFullSku();
    }

    public function getVersion()
    {
        $this->stockBin->getVersion();
    }

    public function getCustomization()
    {
        $this->stockBin->getCustomization();
    }

    public function getLocation()
    {
        $this->stockBin->getLocation();
    }

    public function getQuantity()
    {
        $this->stockBin->getQuantity();
    }
}
