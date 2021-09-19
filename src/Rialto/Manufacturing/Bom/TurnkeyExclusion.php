<?php

namespace Rialto\Manufacturing\Bom;

use Rialto\Database\Orm\ErpDbManager;
use Rialto\Manufacturing\Bom\Orm\TurnkeyExclusionRepository;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;

/**
 * Determines whether a component is a turnkey exclusion.
 *
 * A "turnkey" build is one in which the manufacturer provides most of the
 * components needed to build the product. Any components that the
 * manufacturer does NOT provide are called "turnkey exclusions", and must
 * be provided by the company.
 */
class TurnkeyExclusion
{
    private $parent;
    private $location;
    private $component;

    public function __construct(StockItem $parent, Facility $location, StockItem $component)
    {
        $this->parent = $parent;
        $this->location = $location;
        $this->component = $component;
    }

    /**
     * True if we must provide the component to the manufacturer.
     *
     * @static
     * @param Item $parent
     * @param Item $component
     * @param Facility $location
     * @return bool
     */
    public static function exists(
        Item $parent,
        Item $component,
        Facility $location )
    {
        $dbm = ErpDbManager::getInstance();
        /** @var $repo TurnkeyExclusionRepository */
        $repo = $dbm->getRepository(TurnkeyExclusion::class);
        return $repo->isExcluded($parent, $component, $location);
    }

    /** @return StockItem */
    public function getComponent()
    {
        return $this->component;
    }

    public function equals(Item $item)
    {
        return $this->component->getSku() === $item->getSku();
    }
}

