<?php

namespace Rialto\Manufacturing\Bom\Orm;

use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Manufacturing\Bom\TurnkeyExclusion;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Version;

class BomItemRepository extends RialtoRepositoryAbstract
{
    public function isBomComponent(StockItem $stockItem)
    {
        $bomItems = $this->findBy([
            'component' => $stockItem->getSku()
        ]);
        return count($bomItems) > 0;
    }

    /**
     * Returns those BomItems that the manufacturer does not provide for the
     * given build.
     *
     * In the case of a turnkey build, this will be only the turnkey exclusions.
     * For a non-turnkey build, this will be all of the components.
     *
     * @return BomItem[]
     */
    public function findConsignmentComponents(PurchasingData $purchData, Version $version)
    {
        $item = $purchData->getStockItem();
        assert( $purchData->isManufactured() );
        assert( $item instanceof ManufacturedStockItem );
        if (! $purchData->isTurnkey() ) {
            return $item->getBom($version);
        }

        $qb = $this->createQueryBuilder('bomItem');
        $qb->join('bomItem.parent', 'parentVersion')
            ->join(TurnkeyExclusion::class, 'ex',
                'WITH', 'bomItem.component = ex.component')
            ->andWhere('ex.parent = :parent')
            ->setParameter('parent', $item->getSku())
            ->andWhere('ex.location = :location')
            ->setParameter('location', $purchData->getBuildLocation()->getId())
            ->andWhere('parentVersion.version = :version')
            ->setParameter('version', (string) $version);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param ItemVersion $version
     * @return BomItem|null
     *  The board component of the parent item. Null if there is no
     *  matching item.
     */
    public function findComponentBoard(ItemVersion $version)
    {
        $manufactured = ManufacturedStockItem::class;
        $qb = $this->createQueryBuilder('bom')
            ->join('bom.parent', 'parent')
            ->join('bom.component', 'component')
            ->andWhere('parent.stockItem = :parentItem')
            ->andWhere('parent.version = :version')
            ->andWhere("component instance of $manufactured")
            ->andWhere('component.category = :category')
            ->setMaxResults(1) // TODO: WS30002L has TWO component boards!
            ->setParameters([
                'parentItem' => $version->getSku(),
                'version' => (string) $version,
                'category' => StockCategory::BOARD,
            ]);
        $query = $qb->getQuery();
        return $query->getOneOrNullResult();
    }
}
