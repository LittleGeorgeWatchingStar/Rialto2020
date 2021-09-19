<?php

namespace Rialto\Stock\Cost;

use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Bom\Bom;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Catalog\Template\OmegaCustomBoardStrategy;
use Rialto\Purchasing\Catalog\Template\PurchasingDataStrategy;
use Rialto\Purchasing\Catalog\Template\PurchasingDataTemplate;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Orm\ItemVersionRepository;
use Rialto\Stock\Item\Version\Version;

/**
 * Fetches information that helps the user to set the standard cost
 * of an item.
 */
class StandardCostHints
{
    /** @var DbManager */
    private $dbm;

    /** @var StandardCost */
    private $stdCost;

    /** @var StockItem */
    private $item;

    /** @var Version */
    private $version;

    /** @var PurchasingData|null */
    private $purchData;

    /** @var Bom|null */
    private $bom;

    /** @var PurchasingDataTemplate */
    private $temp;

    /** @var ?ItemVersion */
    private $itemVersion;

    public function __construct(DbManager $dbm, StandardCost $stdCost, ?PurchasingDataTemplate $temp = null)
    {
        $this->dbm = $dbm;
        $this->stdCost = $stdCost;
        $this->item = $stdCost->getStockItem();
        $this->version = $this->item->getAutoBuildVersion();
        $this->purchData = $this->loadPurchasingData();
        $this->bom = $this->loadBom();
        $this->itemVersion = $this->loadItemVersion();
        $this->temp = $temp;
    }

    public function getStandardCost()
    {
        return $this->stdCost;
    }

    public function getStockItem()
    {
        return $this->item;
    }

    public function getSku()
    {
        return $this->item->getSku();
    }

    private function loadPurchasingData()
    {
        $repo = $this->dbm->getRepository(PurchasingData::class);
        $orderQty = $this->item->getEconomicOrderQty() ?: 1;
        return $repo->findPreferred($this->item, $orderQty);
    }

    private function loadBom()
    {
        if(!$this->item->hasSubcomponents()) return null;
        return $this->item->getBom($this->version);
    }

    private function loadItemVersion()
    {
        /** @var ItemVersionRepository $repo */
        $repo = $this->dbm->getRepository(ItemVersion::class);
        return $repo->findOneBy([
            'stockItem' => $this->item,
            'version' => $this->version->getVersionCode(),
        ]);
    }

    public function hasPurchasingData()
    {
        return (bool) $this->purchData;
    }

    public function getPurchasingData()
    {
        return $this->purchData;
    }

    public function getSupplier()
    {
        return $this->purchData ? $this->purchData->getSupplier() : null;
    }

    public function getSupplierName()
    {
        return $this->purchData ? $this->purchData->getSupplierName() : '';
    }

    public function getSupplierCost()
    {
        return $this->purchData ? $this->purchData->getCost() : $this->getExpectedLabour();
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion(Version $version)
    {
        $this->version = $version;
    }

    public function hasBom(): bool
    {
        return $this->bom && count($this->bom) > 0;
    }

    public function getBomCost()
    {
        if (! $this->item->hasSubcomponents() ) return 0.0;
        return $this->bom ? $this->bom->getTotalStandardCost() : null;
    }

    public function getDistinctBomCount()
    {
        return $this->bom ? count($this->bom) : null;
    }

    public function getTotalBomCount()
    {
        return $this->bom ? $this->bom->getTotalNumberOfComponents() : null;
    }

    public function getExpectedLabour(): float
    {
        if ($this->temp && $this->itemVersion) {
            /** @var OmegaCustomBoardStrategy $strategy */
            $strategy = PurchasingDataStrategy::create(OmegaCustomBoardStrategy::STRATEGY_NAME);
            return $strategy->getModuleStandardLabourCost($this->temp, $this->itemVersion);
        } else {
            return 0;
        }
    }
}
