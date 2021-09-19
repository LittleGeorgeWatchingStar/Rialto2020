<?php

namespace Rialto\Stock\Level\Orm;

use Gumstix\Doctrine\HighLevelQueryBuilder;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\AssemblyStockItem;

class StockLevelStatusQueryBuilder extends HighLevelQueryBuilder
{
    public function __construct(StockLevelStatusRepository $repo)
    {
        parent::__construct($repo, 'status');
        $this->qb->join('status.location', 'location');
    }

    public function byItem($item)
    {
        $this->qb->andWhere('status.stockItem = :item')
            ->setParameter('item', $item);

        return $this;
    }

    public function sellableItems()
    {
        $this->qb
            ->join('status.stockItem', 'item')
            ->andWhere('item.category in (:sellable)')
            ->setParameter('sellable', StockCategory::getSellableIds());
        return $this;
    }

    public function byLocation($location)
    {
        $this->qb->andWhere('location = :location')
            ->setParameter('location', $location);

        return $this;
    }

    public function isActiveLocation()
    {
        $this->qb->andWhere('location.active = 1');

        return $this;
    }

    public function excludeSpecialLocations()
    {
        $this->qb->andWhere('location not in (:exclude)')
            ->setParameter('exclude', [
                Facility::TESTING_ID
            ]);

        return $this;
    }

    public function componentsOfAssembly(AssemblyStockItem $assembly)
    {
        $this->qb->distinct()
            ->join('status.stockItem', 'component')
            ->join(BomItem::class, 'bomItem', 'WITH', 'bomItem.component = component')
            ->join('bomItem.parent', 'parent')
            ->andWhere('parent.stockItem = :assembly')
            ->setParameter('assembly', $assembly);

        return $this;
    }
}
