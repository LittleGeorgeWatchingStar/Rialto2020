<?php

namespace Rialto\Madison\StockItem;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Manufacturing\Bom\BomException;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;

/**
 * Determines which other products are compatible with a given product.
 *
 * Compatibility is determined on the basis of having connectors that mate
 * with each other; for example, a USB male to female connection.
 */
class CompatibleProductCalculator
{
    /** @var StockItemRepository */
    private $repo;

    /** @var StockItem[] */
    private $connectors;

    /** @var StockItem[] */
    private $myComponents;

    /** @var StockItem[] */
    private $compatible;

    public function __construct(ObjectManager $om)
    {
        $this->repo = $om->getRepository(StockItem::class);
    }

    /** @return StockItem[] */
    public function findCompatibleProducts(StockItem $product)
    {
        $this->connectors = [];
        $this->myComponents = [$product];
        $this->compatible = [];

        $this->gatherConnectorsAndComponents($product);
        $this->pruneCommonConnectors();
        $this->calculateMatingItems();

        return $this->compatible;
    }

    /**
     * Recursively descend through the BOM, collecting connectors that connect
     * to the current item. Also keep track of all subcomponents of the item
     * in question -- we'll need them to prune out duplicates.
     */
    private function gatherConnectorsAndComponents(StockItem $stockItem)
    {
        $this->connectors = array_merge(
            $this->connectors,
            $stockItem->getConnectingComponents());

        if ($stockItem->hasSubcomponents()) {
            $version = $stockItem->getShippingVersion();
            $bom = $version->getBom();
            if ($bom->isEmpty()) {
                throw new BomException($bom, "$bom is empty");
            }
            foreach ($bom as $bomItem) {
                /* @var $bomItem BomItem */
                $component = $bomItem->getComponent();
                $this->myComponents[] = $component;
                $this->gatherConnectorsAndComponents($component);
            }
        }
    }

    /**
     * Remove any connectors that are also components of the item in question.
     */
    private function pruneCommonConnectors()
    {
        $pruned = [];
        foreach ($this->connectors as $connector) {
            if (!$this->isComponentOfCurrentItem($connector)) {
                $pruned[] = $connector;
            }
        }
        $this->connectors = $pruned;
    }

    private function isComponentOfCurrentItem(StockItem $item)
    {
        return in_array($item, $this->myComponents);
    }

    private function calculateMatingItems()
    {
        foreach ($this->connectors as $connector) {
            $this->calculateContainingItems($connector);
        }
        $this->compatible = array_merge($this->compatible, $this->connectors);
        $productsOnly = function (StockItem $item) {
            return $item->isCategory(StockCategory::PRODUCT);
        };
        $this->compatible = array_filter($this->compatible, $productsOnly);
        $this->compatible = array_unique($this->compatible);
    }

    private function calculateContainingItems(StockItem $component)
    {
        $parents = $this->findEligibleParents($component);
        $this->compatible = array_merge($this->compatible, $parents);
        foreach ($parents as $parent) {
            $this->calculateContainingItems($parent);
        }
    }

    private function findEligibleParents(StockItem $component)
    {
        $parents = $this->repo->findContainingItems($component);
        $eligible = [];
        foreach ($parents as $parent) {
            if ($this->isEligible($parent)) {
                $eligible[] = $parent;
            }
        }
        return $eligible;
    }

    private function isEligible(StockItem $parent)
    {
        if ($this->isComponentOfCurrentItem($parent)) return false;
        if ($parent->isDiscontinued()) return false;
        return true;
    }

}
