<?php

namespace Rialto\Stock\Item\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Manufacturing\Bom\TurnkeyExclusion;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Sales\Discount\DiscountGroup;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\AssemblyStockItem;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\PurchasedStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\StockItemAttribute;
use Rialto\Stock\Item\Version\Version;

/**
 * Database mapper for StockItem class and its subclasses.
 */
class StockItemRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('item');

        $builder->add('stockCode', function(QueryBuilder $qb, $stockCode) {
            $qb->where('item.stockCode like :stockCode');
            $qb->setParameter('stockCode', $stockCode);
            return true;
        });

        $builder->add('matching', function(QueryBuilder $qb, $pattern) {
            $pattern = trim($pattern);
            $pattern = str_replace('*', '%', $pattern);
            $pattern = str_replace(' ', '%', $pattern);
            $pattern = "%$pattern%";
            $qb->andWhere('item.stockCode like :pattern');
            $qb->orWhere('item.description like :pattern');
            $qb->setParameter('pattern', $pattern);
        });

        $builder->add('manufacturerCode', function(QueryBuilder $qb, $manufacturerCode) {
            $manufacturerCode = trim($manufacturerCode);
            $manufacturerCode = str_replace('*', '%', $manufacturerCode);
            $manufacturerCode = str_replace(' ', '%', $manufacturerCode);
            $manufacturerCode = str_replace('-', '', $manufacturerCode);
            $manufacturerCode = "%$manufacturerCode%";
            $qb->join(PurchasingData::class, 'pd', 'WITH',
                    'pd.stockItem = item')
                ->andWhere("REPLACE(pd.manufacturerCode, '-', '') like :manufacturerCode")
                ->setParameter('manufacturerCode', $manufacturerCode);
        });

        $builder->add('category', function(QueryBuilder $qb, $categoryId) {
            $qb->andWhere('item.category = :category');
            $qb->setParameter('category', $categoryId);
        });

        $builder->add('discontinued', function (QueryBuilder $qb, $disc) {
            if ( $disc == 'yes' ) {
                $qb->andWhere('item.discontinued > 0');
            } elseif ( $disc == 'no' ) {
                $qb->andWhere('item.discontinued = 0');
            }
        });

        $builder->add('attribute', function (QueryBuilder $qb, $attr) {
            $qb->join(StockItemAttribute::class, 'attr', 'WITH',
                    'attr.stockItem = item')
                ->andWhere('attr.attribute like :attr')
                ->setParameter('attr', $attr);
        });

        return $builder->buildQuery($params);
    }

    /** @return AssemblyStockItem[] */
    public function findAssembliesContaining(array $componentSkus)
    {
        if ( count($componentSkus) == 0 ) {
            return [];
        }

        $qb = $this->createQueryBuilder('item')
            ->join('item.versions', 'version')
            ->join('version.bomItems', 'bomItem')
            ->andWhere('item instance of ' . AssemblyStockItem::class)
            ->andWhere('bomItem.component in (:componentSkus)')
            ->andWhere('version = item.shippingVersion')
            ->setParameters([
                'componentSkus' => $componentSkus,
            ]);
        $query = $qb->getQuery();
        return $query->getResult();
    }

    /**
     * @return StockItem[]
     */
    public function findByManufacturerPartNumber($part_no)
    {
        $qb = $this->createQueryBuilder('item')
            ->from(PurchasingData::class, 'data')
            ->where('data.stockItem = item')
            ->andWhere('data.manufacturerCode like :partNo')
            ->setParameter('partNo', $part_no);
        return $qb->getQuery()->getResult();
    }

    /**
     * @return StockItem[]
     */
    public function findItemsAvailableFromSupplier(Supplier $supp)
    {
        $qb = $this->queryItemsAvailableFromSupplier($supp);
        return $qb->getQuery()->getResult();
    }

    /** @return QueryBuilder */
    public function queryItemsAvailableFromSupplier(Supplier $supp)
    {
        $qb = $this->createQueryBuilder('item')
            ->from(PurchasingData::class, 'data')
            ->where('data.stockItem = item')
            ->andWhere('data.supplier = :supplier')
            ->andWhere('item instance of Rialto\Stock\Item\PurchasedStockItem')
            ->andWhere('item.discontinued = 0')
            ->setParameters([
                'supplier' => $supp->getId(),
            ])
            ->orderBy('item.stockCode');
        return $qb;
    }

    /**
     * @param string $search_key
     * @return StockItem[]
     */
    public function findMatchingItems($search_key)
    {
        /* Surround each word with database wildcards */
        $words = explode(' ', $search_key);
        $search_key = implode('%', $words);
        $search_key = '%' . $search_key . '%';

        $qb = $this->createQueryBuilder('item');
        $qb->where('item.discontinued = 0')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->like('item.stockCode', ':key'),
                $qb->expr()->like('item.description', ':key')
            ))
            ->setParameter('key', $search_key);
        return $qb->getQuery()->getResult();
    }

    public function findMatchingStockCodes($regex)
    {
        /* MySQL does not want regexes to begin and end with a slash. */
        $regex = trim($regex, '/');
        $conn = $this->_em->getConnection();
        $sql = "select StockID
                from StockMaster
                where StockID regexp :regex
                order by StockID asc";
        $stmt = $conn->executeQuery($sql, [
            'regex' => $regex
        ]);
        $results = $stmt->fetchAll();
        return array_map(function($result) {
            return $result['StockID'];
        }, $results);
    }

    public function findValidIds()
    {
        $qb = $this->createQueryBuilder('item')
            ->select('item.stockCode')
            ->orderBy('item.stockCode');
        return $qb->getQuery()->getResult();
    }

    public function isExistingStockId($stockCode)
    {
        $qb = $this->createQueryBuilder('item')
            ->select('count(item) as cnt')
            ->where('item.stockCode = :stockCode')
            ->setParameter('stockCode', $stockCode);
        $count = (int) $qb->getQuery()->getSingleScalarResult();
        return $count > 0;
    }

    /** @return StockItem */
    public function findByStockCode(String $stockCode)
    {
        $qb = $this->createQueryBuilder('item')
            ->where('item.stockCode = :stockCode')
            ->setParameter('stockCode', $stockCode);
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllParts()
    {
        return $this->findBy([
            'category' => StockCategory::PART
        ]);
    }

    /** @return string[] */
    public function findAllPackages()
    {
        $qb = $this->createQueryBuilder('item')
            ->select('item.package')
            ->distinct()
            ->andWhere('item.discontinued = 0')
            ->andWhere("item.package != ''")
            ->orderBy('item.package', 'asc');
        $results = $qb->getQuery()->getScalarResult();
        return array_map(function(array $result) {
            return $result['package'];
        }, $results);
    }

    /** @return string[][] */
    public function findPartsData()
    {
        $sql = "select i.StockID as stockCode
                , i.StockID as stockId
                , i.Package as package
                , i.PartValue as partValue
                , p.ManufacturerCode as manufacturerCode
            from StockMaster as i
            left join (
                select StockID, ManufacturerCode
                from PurchData
                order by Preferred desc
            ) as p
                on p.StockID = i.StockID
            where i.CategoryID = :category
            and i.Package != ''
            and i.Discontinued = 0
            group by i.StockID
            order by package, partValue";
        $stmt = $this->_em->getConnection()->executeQuery($sql, [
            'category' => StockCategory::PART,
        ]);
        return $stmt->fetchAll();
    }

    /** @return string[] */
    public function findPartData(string $sku)
    {
        $sql = "select i.StockID as stockCode
                , i.StockID as stockId
                , i.Package as package
                , i.PartValue as partValue
                , p.ManufacturerCode as manufacturerCode
            from StockMaster as i
            left join (
                select StockID, ManufacturerCode
                from PurchData
                order by Preferred desc
            ) as p
                on p.StockID = i.StockID
            where i.CategoryID = :category
            and i.StockID = :stockID
            and i.Package != ''
            and i.Discontinued = 0
            group by i.StockID
            order by package, partValue";
        $stmt = $this->_em->getConnection()->executeQuery($sql, [
            'category' => StockCategory::PART,
            'stockID' => $sku,
        ]);
        return $stmt->fetch();
    }

    /** @deprecated */
    public function findForPartsReport()
    {
        $qb = $this->createQueryBuilder('item')
            ->where('item.category = :category')
            ->andWhere('item.package != :string')
            ->andWhere('item.discontinued = 0')
            ->orderBy('item.package')
            ->addOrderBy('item.partValue')
            ->setParameters([
                'category' => StockCategory::PART,
                'string' => "",
            ]);
        return $qb->getQuery()->getResult();
    }

    /** @return StockItem[] */
    public function findActiveProducts()
    {
        $qb = $this->createQueryBuilder('item')
            ->where('item.category = :category')
            ->andWhere('item.discontinued = 0')
            ->setParameter('category', StockCategory::PRODUCT);
        return $qb->getQuery()->getResult();
    }

    /** @return PhysicalStockItem[] */
    public function findActivePhysicalStockItems()
    {
        $qb = $this->createQueryBuilder('item')
            ->andWhere('item instance of ' . ManufacturedStockItem::class. ' or item instance of ' . PurchasedStockItem::class)
            ->andWhere('item.discontinued = 0');
        return $qb->getQuery()->getResult();
    }

    /** @return StockItem[] Items that contain $component. */
    public function findContainingItems(StockItem $component)
    {
        $qb = $this->queryContainingItems($component);
        return $qb->getQuery()->getResult();
    }

    /**
     * @return QueryBuilder A query to find items that contain $component.
     */
    private function queryContainingItems(StockItem $component)
    {
        $qb = $this->createQueryBuilder('item')
            ->join('item.versions', 'version')
            ->join('version.bomItems', 'bomItem')
            ->andWhere('version.version = item.shippingVersion')
            ->andWhere('bomItem.component = :component')
            ->setParameter('component', $component->getSku())
            ->andWhere('item.discontinued = 0');
        return $qb;
    }

    /**
     * @return StockItem[] Assembly products that contain $component.
     */
    public function findPacks(StockItem $component)
    {
        $qb = $this->queryContainingItems($component);
        $assembly = AssemblyStockItem::class;
        $qb->andWhere("item instance of $assembly")
            ->andWhere('item.category = :product')
            ->setParameter('product', StockCategory::PRODUCT);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param StockItem $item The parent item (a product)
     * @param Version $version
     * @return StockItem|null The board component of the parent item. Null if
     *    there is no matching item.
     */
    public function findComponentBoard(
        StockItem $item,
        Version $version)
    {
        $manufactured = ManufacturedStockItem::class;
        $qb = $this->createQueryBuilder('component')
            ->from(BomItem::class, 'bom')
            ->join('bom.parent', 'parent')
            ->where('bom.component = component')
            ->andWhere('parent.stockItem = :parentItem')
            ->andWhere('parent.version = :version')
            ->andWhere("component instance of $manufactured")
            ->andWhere('component.category = :category')
            ->setMaxResults(1) // TODO: WS30002L has TWO component boards!
            ->setParameters([
                'parentItem' => $item->getSku(),
                'version' => (string) $version,
                'category' => StockCategory::BOARD,
            ]);
        $query = $qb->getQuery();
        return $query->getOneOrNullResult();
    }

    /**
     * @return string[]
     *  The most commonly occurring countries of origin.
     */
    public function findPreferredCountriesOfOrigin()
    {
        $qb = $this->createQueryBuilder('item')
            ->select('item.countryOfOrigin')
            ->addSelect('count(item.stockCode) as cnt')
            ->where('item.countryOfOrigin != :emptyString')
            ->groupBy('item.countryOfOrigin')
            ->orderBy('cnt', 'DESC')
            ->setParameters([
                'emptyString' => "",
            ])
            ->setMaxResults(4);
        $results = $qb->getQuery()->getResult();
        $countriesOfOrigin = [];
        foreach( $results as $result ) {
            $countriesOfOrigin[] = $result['countryOfOrigin'];
        }
        return $countriesOfOrigin;
    }

    /**
     * Returns the list of components that the manufacturer does not provide
     * when building the parent item.
     *
     * @param StockItem $parent
     *  The parent item
     * @param Facility $location
     *  The manufacturing location
     * @return StockItem[]
     *  A list of excluded components.
     */
    public function findTurnkeyExclusions(StockItem $parent, Facility $location)
    {
        $qb = $this->createQueryBuilder('component')
            ->from(TurnkeyExclusion::class, 'ex')
            ->where('ex.component = component')
            ->andWhere('ex.parent = :parent')
            ->andWhere('ex.location = :loc')
            ->setParameters([
                'parent' => $parent->getSku(),
                'loc' => $location->getId()
            ]);
        return $qb->getQuery()->getResult();
    }

    public function findItemsByDiscountGroup(DiscountGroup $group)
    {
        $qb = $this->createQueryBuilder('item')
            ->from(DiscountGroup::class, 'g')
            ->join('g.items', 'component')
            ->where('item = component')
            ->andWhere('g = :g');
        $qb->setParameter('g', $group->getId());
        return $qb->getQuery()->getResult();
    }

    /**
     * For use by StockNeedMapper.
     * @return string[][]
     */
    public function findStockNeeds($mbFlag)
    {
        $sql = "select item.StockID as stockCode,
            ifnull(bins.inStock, 0) as inStock,
            ifnull(producers.onOrder, 0) as onOrder,
            ifnull(allocs.allocated, 0) as allocated,
            level.orderPoint
            from StockMaster item
            join (
                select sum(level.orderPoint) as orderPoint
                  , level.stockCode
                from StockLevelStatus level
                join Locations location on level.locationID = location.LocCode
                where location.Active = 1
                group by level.stockCode
            ) as level on level.stockCode = item.StockID
            left join (
                select sum(Quantity) as inStock, StockID
                from StockSerialItems
                where Quantity > 0
                group by StockID
            ) as bins on bins.StockID = item.StockID
            left join (
                select sum(sp.qtyOrdered - sp.qtyReceived) as onOrder,
                pd.StockID as stockCode
                from StockProducer sp
                join PurchData pd on sp.purchasingDataID = pd.ID
                where sp.dateClosed is null
                and sp.qtyOrdered > sp.qtyReceived
                group by pd.StockID
            ) as producers on producers.stockCode = item.StockID
            left join (
                select sum(Qty) as allocated, StockID
                from StockAllocation
                where Qty > 0
                group by StockID
            ) as allocs on allocs.StockID = item.StockID
            where item.Discontinued = 0
            and item.MBflag = :mbFlag
            having orderPoint > 0
            and (inStock + onOrder - allocated) < orderPoint
            ";

        $conn = $this->_em->getConnection();
        $stmt = $conn->executeQuery($sql, [
            'mbFlag' => $mbFlag,
        ]);
        return $stmt->fetchAll();
    }

    public function findModules()
    {
        return $this->findBy([
            'category' => StockCategory::MODULE,
        ]);
    }
}
