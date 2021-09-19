<?php

namespace Rialto\Purchasing\Catalog\Orm;

use DateTime;
use Doctrine\ORM\Query\Expr\Join;
use Gumstix\Doctrine\HighLevelQueryBuilder;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Catalog\Remote\SupplierApi;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\PurchasedStockItem;
use Rialto\Stock\Item\Version\Version;

class PurchasingDataQueryBuilder extends HighLevelQueryBuilder
{
    public function __construct(PurchasingDataRepository $repo)
    {
        parent::__construct($repo, 'pd');
        $this->qb->join('pd.stockItem', 'item')
            ->leftJoin('pd.supplier', 'supplier')
            ->leftJoin('pd.costBreaks', 'cost');
    }

    public function prefetchItem()
    {
        $this->qb
            ->addSelect('item');
        return $this;
    }

    public function prefetchSupplier()
    {
        $this->qb
            ->addSelect('supplier');
        return $this;
    }

    public function prefetchBreaks()
    {
        $this->qb
            ->addSelect('cost');
        return $this;
    }

    /**
     * @deprecated use getRecordCount() instead
     */
    public function getCount()
    {
        return $this->qb->select('count(distinct pd.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function isActive()
    {
        $this->qb->andWhere('item.discontinued = 0')
            ->andWhere('pd.endOfLife is null or pd.endOfLife > :today')
            ->setParameter('today', date('Y-m-d'));

        return $this;
    }

    public function isInactive()
    {
        $this->qb->andWhere('item.discontinued > 0')
            ->andWhere('pd.endOfLife is not null')
            ->andWhere('pd.endOfLife <= :today')
            ->setParameter('today', date('Y-m-d'));

        return $this;
    }

    public function bySku($sku)
    {
        $this->qb
            ->andWhere('item = :sku')
            ->setParameter('sku', $sku);
        return $this;
    }

    public function byItem(Item $item, $orderQty = 1, $leadTime = null)
    {
        $this->bySku($item->getSku());
        $this->qb->resetDQLPart('join');
        $this->qb
            ->join('pd.stockItem', 'item')
            ->leftJoin('pd.supplier', 'supplier')
            ->leftJoin('pd.costBreaks', 'cost', 'WITH',
                'cost.minimumOrderQty <= :orderQty')
            ->setParameter('orderQty', $orderQty);

        if ($leadTime) {
            $this->qb->andWhere($this->qb->expr()->orX(
                $this->qb->expr()->andX(
                    'pd.stockLevel >= :orderQty',
                    'cost.supplierLeadTime <= :leadTime'
                ),
                'cost.manufacturerLeadTime <= :leadTime'
            ));
            $this->qb->setParameter('leadTime', $leadTime);
        }

        return $this;
    }

    public function matches($pattern)
    {
        $pattern = str_replace('-', '%', $pattern);
        $this->qb
            ->andWhere(
                'item.stockCode like :pattern' .
                ' or pd.catalogNumber like :pattern' .
                ' or pd.quotationNumber like :pattern' .
                " or REPLACE(pd.manufacturerCode, '-', '') like :pattern")
            ->setParameter('pattern', "%$pattern%");
        return $this;
    }

    public function byItemIdentifier($identifier)
    {
        $this->qb
            ->andWhere(
                'item.stockCode like :identifier' .
                ' or pd.manufacturerCode like :identifier' .
                ' or pd.catalogNumber like :identifier')
            ->setParameter('identifier', "%$identifier%");
        return $this;
    }

    public function isPhysicalItem()
    {
        $man = ManufacturedStockItem::class;
        $pur = PurchasedStockItem::class;
        $this->qb->andWhere(
            "(item instance of $man) or (item instance of $pur)"
        );
        return $this;
    }

    public function bySupplier($supplier)
    {
        $this->qb->andWhere('pd.supplier = :supplier')
            ->setParameter('supplier', $supplier);

        return $this;
    }

    public function bySupplierPattern($supplierPattern)
    {
        $this->qb
            ->andWhere(
                'supplier.name like :supplierPattern' .
                ' or supplier.website like :supplierPattern')
            ->setParameter('supplierPattern', "%$supplierPattern%");
        return $this;
    }

    public function byLocation(Facility $location)
    {
        $this->qb->andWhere('pd.buildLocation = :locCode')
            ->setParameter('locCode', $location->getId());
        return $this;
    }

    public function byActiveLocations()
    {
        $this->qb->join('pd.buildLocation', 'loc')
            ->andWhere('loc.active = 1')
            ->addOrderBy('loc.name', 'ASC');
        return $this;
    }

    public function byVersion(Version $version)
    {
        if ($version->isSpecified()) {
            $this->qb->andWhere('pd.version in (:versions)')
                ->setParameter('versions', [
                    (string) $version,
                    Version::ANY,
                    Version::AUTO,
                ]);
        }
        return $this;
    }

    public function byExactVersion(Version $version)
    {
        $this->qb->andWhere('pd.version = :version')
            ->setParameter('version', (string) $version);
        return $this;
    }

    public function byManufacturer($manufacturer)
    {
        $this->qb
            ->andWhere('pd.manufacturer = :manufacturer')
            ->setParameter('manufacturer', $manufacturer);
        return $this;
    }

    public function hasManufacturer()
    {
        $this->qb->andWhere('pd.manufacturer is not null');
        return $this;
    }

    public function doesNotHaveManufacturer()
    {
        $this->qb->andWhere('pd.manufacturer is null');
        return $this;
    }

    public function hasManufacturerCode()
    {
        $this->qb->andWhere('pd.manufacturerCode != :string')
            ->setParameter('string', "");
        return $this;
    }

    /**
     * Selects existing records that match either the MPN or catalog number of
     * any of $purchData.
     *
     * @param PurchasingData[] $purchData
     */
    public function isDuplicateOfAny(array $purchData)
    {
        $mpns = $this->mapUnique($purchData, function (PurchasingData $pd) {
            return $pd->getManufacturerCode();
        });
        $catalogNos = $this->mapUnique($purchData, function (PurchasingData $pd) {
            return $pd->getCatalogNumber();
        });
        $this->qb->andWhere('pd.manufacturerCode in (:mpns) or pd.catalogNumber in (:catalogNos)')
            ->setParameter('mpns', $mpns)
            ->setParameter('catalogNos', $catalogNos);
        return $this;
    }

    private function mapUnique(array $purchData, callable $func)
    {
        return array_filter(array_unique(array_map($func, $purchData)));
    }

    public function byOrderQty($qty)
    {
        $this->qb
            ->andWhere(':orderQty >= cost.minimumOrderQty')
            ->setParameter('orderQty', $qty);
        return $this;
    }

    public function canSupplyByDate(DateTime $neededBy)
    {
        $today = new DateTime('today');
        $diff = $neededBy->diff($today);
        $leadTime = $diff->days;
        // TODO: manufacturerLeadTime provides a worst-case estimate
        $this->qb
            ->andWhere('cost.manufacturerLeadTime <= :leadTime')
            ->setParameter('leadTime', $leadTime);
        return $this;
    }

    public function isPreferred()
    {
        $this->qb
            ->andWhere('pd.preferred > 0');
        return $this;
    }

    public function orderByPreferred()
    {
        $this->qb->orderBy('pd.preferred', 'DESC')
            ->addOrderBy('cost.unitCost', 'ASC')
            ->addOrderBy('cost.supplierLeadTime', 'ASC')
            ->addOrderBy('cost.manufacturerLeadTime', 'ASC');
        return $this;
    }

    public function orderBySku()
    {
        $this->qb->orderBy('item.stockCode', 'asc')
            ->addOrderBy('pd.preferred', 'desc');

        return $this;
    }

    public function orderBySupplier()
    {
        $this->qb
            ->orderBy('supplier.name', 'asc')
            ->addOrderBy('item.stockCode', 'asc');
        return $this;
    }

    public function orderByCatalogNo()
    {
        $this->qb
            ->orderBy('pd.catalogNumber', 'asc');
        return $this;
    }

    public function hasApi()
    {
        $this->qb
            ->leftJoin(SupplierApi::class, 'api', Join::WITH, 'api.supplier = supplier')
            ->andWhere("pd.manufacturerCode != ''")
            ->andWhere("supplier.website != ''")
            ->andWhere("api.serviceName != ''");
        return $this;
    }

    public function hasNoApi()
    {
        $this->qb
            ->leftJoin(SupplierApi::class, 'api', Join::WITH, 'api.supplier = supplier')
            ->andWhere("api.supplier IS NULL");
        return $this;
    }

    public function lastSyncBefore($syncBefore)
    {
        $this->qb->andWhere('pd.lastSync <= :syncBefore OR pd.lastSync IS NULL')
            ->setParameter('syncBefore', $syncBefore)
            ->orderBy('pd.lastSync', 'ASC');
        return $this;
    }

    public function isInvalid()
    {
        $this->qb->andWhere('pd.binSize = 0');
        return $this;
    }

    public function setLimit($limit)
    {
        $this->qb->setMaxResults($limit);
        return $this;
    }

    public function usedInGepettoBom()
    {
        $subquery = $this->qb->getEntityManager()->createQueryBuilder()
            ->select('cpd.id')
            ->from(BomItem::class, 'n')
            ->innerJoin('n.parent', 'iv')
            ->join(Item\StockItem::class, 'c', Join::WITH, 'n.component = c')
            ->join(PurchasingData::class, 'cpd', Join::WITH, 'cpd.stockItem = c')
            ->join(Item\StockItem::class, 'i', Join::WITH, 'iv.stockItem = i')
            ->andWhere('i.category = :moduleCategory')
            ->andWhere('iv.version = i.shippingVersion')
            ->andWhere('i.discontinued = 0');

        $this->qb
            ->andWhere("pd.id in ({$subquery->getDQL()})")
            ->setParameter('moduleCategory', StockCategory::MODULE);

        return $this;
    }

    public function notUsedInGeppettoBom()
    {
        $subquery = $this->qb->getEntityManager()->createQueryBuilder()
            ->select('cpd.id')
            ->from(BomItem::class, 'n')
            ->innerJoin('n.parent', 'iv')
            ->join(Item\StockItem::class, 'c', Join::WITH, 'n.component = c')
            ->join(PurchasingData::class, 'cpd', Join::WITH, 'cpd.stockItem = c')
            ->join(Item\StockItem::class, 'i', Join::WITH, 'iv.stockItem = i')
            ->andWhere('i.category = :moduleCategory')
            ->andWhere('iv.version = i.shippingVersion')
            ->andWhere('i.discontinued = 0');

        $this->qb
            ->andWhere("pd.id not in ({$subquery->getDQL()})")
            ->setParameter('moduleCategory', StockCategory::MODULE);

        return $this;
    }
}
