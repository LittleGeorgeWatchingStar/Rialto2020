<?php

namespace Rialto\Stock\Item\Version\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Manufacturing\Bom\Bag\BagFinderGateway;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItemAttribute;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Version;

class ItemVersionRepository extends FilteringRepositoryAbstract implements
    BagFinderGateway
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('itemVersion');
        $builder->join('itemVersion.stockItem', 'stockItem');

        $builder->add('matching', function (QueryBuilder $qb, $pattern) {
            $skuExpr = "concat(stockItem.stockCode, '-R', itemVersion.version)";
            $qb->andWhere("$skuExpr like :pattern");
            $qb->setParameter('pattern', "%$pattern%");
        });

        $builder->add('component', function (QueryBuilder $qb, $sku) {
            $qb->join('itemVersion.bomItems', 'bom')
                ->andWhere('bom.component = :componentSku')
                ->setParameter('componentSku', $sku);
        });

        $builder->add('active', function (QueryBuilder $qb, $active) {
            if ('yes' == $active) {
                $qb->andWhere('itemVersion.active = 1');
            } elseif ('no' == $active) {
                $qb->andWhere('itemVersion.active = 0');
            }
        });

        $builder->add('discontinued', function (QueryBuilder $qb, $disc) {
            if ($disc == 'yes') {
                $qb->andWhere('stockItem.discontinued > 0');
            } elseif ($disc != 'all') {
                $qb->andWhere('stockItem.discontinued = 0');
            }
        });

        $builder->add('category', function (QueryBuilder $qb, $category) {
            $qb->andWhere('stockItem.category = :category')
                ->setParameter('category', $category);
        });

        if (!isset($params['discontinued'])) {
            $params['discontinued'] = 'no';
        }

        $builder->add('hasBeenProduced', function (QueryBuilder $qb, $produced) {
            if ($produced == 'yes') {
                $qb->leftJoin(PurchasingData::class, 'pd', Join::WITH,
                    'pd.stockItem = stockItem and (pd.version = itemVersion.version or pd.version = :any)')
                    ->setParameter('any', Version::ANY)
                    ->leftJoin(StockProducer::class, 'producer', Join::WITH,
                        'producer.purchasingData = pd and producer.dateClosed is not null')
                    ->andWhere('producer is not null');
            }
            if ($produced == 'no') {
                $qb->leftJoin(PurchasingData::class, 'pd', Join::WITH,
                    'pd.stockItem = stockItem and (pd.version = itemVersion.version or pd.version = :any)')
                    ->setParameter('any', Version::ANY)
                    ->leftJoin(StockProducer::class, 'producer', Join::WITH,
                    'producer.purchasingData = pd and producer.dateClosed is not null')
                    ->groupBy('itemVersion')
                    ->andHaving('count(distinct(producer)) = 0');
            }
        });

        $builder->add('hasBeenSold', function (QueryBuilder $qb, $sold) {
            if ($sold == 'yes') {
                $qb->leftJoin(SalesOrderDetail::class, 'detail', Join::WITH,
                    'detail.stockItem = stockItem and (detail.version = itemVersion.version or detail.version = :any)')
                    ->setParameter('any', Version::ANY)
                    ->andWhere('detail is not null');
            }
            if ($sold == 'no') {
                $qb->leftJoin(SalesOrderDetail::class, 'detail', Join::WITH,
                    'detail.stockItem = stockItem and (detail.version = itemVersion.version or detail.version = :any)')
                    ->setParameter('any', Version::ANY)
                    ->groupBy('itemVersion')
                    ->andHaving('count(distinct(detail)) = 0');
            }
        });

        return $builder->buildQuery($params);
    }

    /** @return QueryBuilder */
    public function queryActiveByItem(Item $item)
    {
        $qb = $this->queryActive();
        $qb->andWhere('version.stockItem = :stockCode')
            ->setParameter('stockCode', $item->getSku());
        return $qb;
    }

    /** @return QueryBuilder */
    private function queryActive()
    {
        $qb = $this->createQueryBuilder('version');
        $qb->andWhere('version.active = 1');
        return $qb;
    }

    /**
     * True if $parent contains a bag.
     */
    public function containsBag(ItemVersion $parent): bool
    {
        $qb = $this->createQueryBuilder('version');
        $qb->select('count(version)')
            ->join('version.bomItems', 'bomItem')
            ->join('bomItem.component', 'component')
            ->join(StockItemAttribute::class, 'attr', 'WITH',
                'attr.stockItem = component')
            ->andWhere('version.stockItem = :stockCode')
            ->setParameter('stockCode', $parent->getSku())
            ->andWhere('version.version = :version')
            ->andWhere('attr.attribute = :attribute')
            ->setParameter('attribute', StockItemAttribute::SHIELDED_BAG)
            ->setParameter('version', (string) $parent);

        $query = $qb->getQuery();
        return $query->getSingleScalarResult() > 0;
    }


    /**
     * @return ItemVersion[]
     */
    public function findEligibleBags(): array
    {
        $qb = $this->queryActiveByAttribute(StockItemAttribute::SHIELDED_BAG);
        $qb->andWhere('version.dimensionX > 0')
            ->andWhere('version.dimensionY > 0');
        return $qb->getQuery()->getResult();
    }

    public function getBagWorkType(): WorkType
    {
        return WorkType::fetchPackage($this->_em);
    }

    /**
     * @return QueryBuilder
     */
    public function queryEligibleBoxes()
    {
        $qb = $this->queryActiveByAttribute(StockItemAttribute::PRODUCT_BOX);
        $qb->andWhere('version.dimensionX > 0')
            ->andWhere('version.dimensionY > 0')
            ->andWhere('version.dimensionZ > 0');
        return $qb;
    }

    /**
     * @return ItemVersion[]
     */
    public function findEligibleBoxes()
    {
        $qb = $this->queryEligibleBoxes();
        return $qb->getQuery()->getResult();
    }

    /** @return QueryBuilder */
    private function queryActiveByAttribute($attribute)
    {
        $qb = $this->queryActive();
        $qb->join('version.stockItem', 'item')
            ->join(StockItemAttribute::class, 'attr', 'WITH',
                'attr.stockItem = item')
            ->andWhere('attr.attribute = :attribute')
            ->setParameter('attribute', $attribute);
        return $qb;
    }

    /**
     * @return ItemVersion[]
     */
    public function findBagsWithMissingDimensions(): array
    {
        $qb = $this->queryActiveByAttribute(StockItemAttribute::PRODUCT_BAG);
        $qb->andWhere('version.dimensionX = 0 or version.dimensionY = 0');
        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $string eg: "CON044", "GS3503F-R3445", "GS3503F-R3445-C1234"
     * @return ItemVersion|object|null
     */
    public function findByFullSku(string $string)
    {
        $string = strtoupper(trim($string));
        // Split base SKU from revision and customization
        $parts = explode('-R', $string);
        $sku = $parts[0];
        $rest = $parts[1] ?? '';

        // Split customization from revision
        $parts = explode('-C', $rest);
        $version = $parts[0] ?? '';
        return $this->findOneBy([
            'stockItem' => $sku,
            'version' => $version,
        ]);
    }

    /** @return ItemVersion[] */
    public function findByComponent(Item $component)
    {
        $qb = $this->createQueryBuilder('version');
        $qb->join('version.bomItems', 'bom')
            ->andWhere('bom.component = :stockCode')
            ->setParameter('stockCode', $component->getSku());
        return $qb->getQuery()->getResult();
    }
}
