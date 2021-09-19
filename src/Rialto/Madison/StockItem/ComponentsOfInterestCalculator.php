<?php

namespace Rialto\Madison\StockItem;

use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Stock\Item\ComponentOfInterest;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Publication\Orm\PublicationRepository;
use Rialto\Stock\Publication\Publication;

/**
 * Finds all of the "components of interest" in a stock item.
 */
class ComponentsOfInterestCalculator
{
    /** @var DbManager */
    private $dbm;

    /** @var ComponentOfInterest[] */
    private $components;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
    }

    /** @return ComponentOfInterest[] */
    public function getComponents(StockItem $item)
    {
        $this->components = [];
        $this->findComponentsRecursively($item, 1);
        return $this->components;
    }

    private function findComponentsRecursively(StockItem $item, $parentQty)
    {
        if (!$item->hasSubcomponents()) {
            return;
        }

        $version = $item->getShippingVersion();
        $bom = $item->getBom($version);
        foreach ($bom as $bomItem) {
            /* @var $bomItem BomItem */
            $component = $bomItem->getComponent();
            $stockFlags = $component->getFlags();
            $componentQty = $parentQty * $bomItem->getUnitQty();
            if ($stockFlags->isComponentOfInterest()) {
                $ofInterest = $this->getComponent($component);
                $ofInterest->addQuantity($componentQty);
                $ofInterest->setType($stockFlags->getFirstFlag());
            }
            $this->findComponentsRecursively($component, $componentQty);
        }
    }

    /** @return ComponentOfInterest */
    private function getComponent(StockItem $item)
    {
        $sku = $item->getSku();
        if (!isset($this->components[$sku])) {
            $ofInterest = new ComponentOfInterest($item);
            $ofInterest->setSpecs($this->findSpecs($item));
            $this->components[$sku] = $ofInterest;
        }
        return $this->components[$sku];
    }

    private function findSpecs(StockItem $item)
    {
        /** @var PublicationRepository $repo */
        $repo = $this->dbm->getRepository(Publication::class);
        return $repo->findSpecSheet($item);
    }

    /**
     * Get the "what's included" in a packaged stock item.
     *
     * @return ComponentOfInterest[]
     */
    public function getIncludedItems(StockItem $item)
    {
        $included = [];
        if ($item->hasSubcomponents()) {
            $version = $item->getShippingVersion();
            $bom = $version->getBom();
            foreach ($bom as $bomItem) {
                /* @var $bomItem BomItem */
                $component = $bomItem->getComponent();
                if ($component->isSellable()) {
                    $ofInterest = new ComponentOfInterest($component);
                    $ofInterest->addQuantity($bomItem->getQuantity());
                    $ofInterest->setType("what's included");
                    $included[] = $ofInterest;
                }
            }
        }
        return $included;
    }
}
