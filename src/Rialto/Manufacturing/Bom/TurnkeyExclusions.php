<?php

namespace Rialto\Manufacturing\Bom;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Bom\Orm\TurnkeyExclusionRepository;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\CompositeStockItem;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;

/**
 * @author Ian Phillips <ian@gumstix.com>
 */
class TurnkeyExclusions
{
    private $dbm;

    /** @var StockItem */
    private $parent;
    private $location;
    private $bom;
    private $components = [];
    private $exclusions;

    function __construct(DbManager $dbm, CompositeStockItem $parent, Facility $location)
    {
        $this->dbm = $dbm;
        $this->parent = $parent;
        $this->location = $location;
        $this->loadBom();
        $this->loadExclusions();
    }

    private function loadBom()
    {
        $version = $this->parent->getAutoBuildVersion();
        $this->bom = $this->parent->getBom($version);
        foreach ( $this->bom as $bomItem ) {
            /** @var $bomItem BomItem */
            $id = $bomItem->getSku();
            $this->components[$id] = $bomItem->getComponent();
        }
    }

    private function loadExclusions()
    {
        /** @var $repo StockItemRepository */
        $repo = $this->dbm->getRepository(StockItem::class);
        $excludedItems = $repo->findTurnkeyExclusions(
            $this->parent,
            $this->location
        );

        $this->exclusions = new ArrayCollection();
        foreach ( $excludedItems as $item ) {
            $id = $item->getId();
            if ( isset($this->components[$id]) ) {
                $this->exclusions[$id] = $this->components[$id];
            }
        }
    }

    public function getBom()
    {
        return $this->bom;
    }

    public function getComponents()
    {
        return $this->components;
    }

    public function getExclusions()
    {
        return $this->exclusions;
    }

    public function setExclusions(Collection $exclusions)
    {
        $this->exclusions = $exclusions;
    }

    public function save()
    {
        /** @var $repo TurnkeyExclusionRepository */
        $repo = $this->dbm->getRepository(TurnkeyExclusion::class);
        $repo->updateExclusions($this->parent, $this->location, $this->exclusions);
    }

}
