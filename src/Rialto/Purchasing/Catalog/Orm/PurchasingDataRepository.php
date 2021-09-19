<?php

namespace Rialto\Purchasing\Catalog\Orm;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use Gumstix\Doctrine\HighLevelFilter;
use Rialto\Allocation\Requirement\RequirementInterface;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\SingleItemPurchaseOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;

class PurchasingDataRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $qb = $this->createBuilder()
            ->prefetchItem()
            ->prefetchBreaks();

        $filter = $this->createFilter($qb);
        return $filter->buildQuery($params);
    }

    private function createFilter(PurchasingDataQueryBuilder $qb)
    {
        $filter = new HighLevelFilter($qb);
        $filter->add('stockItem', function (PurchasingDataQueryBuilder $qb, $item) {
            $qb->bySku($item);
        });
        $filter->add('supplier', function (PurchasingDataQueryBuilder $qb, $supplier) {
            $qb->bySupplier($supplier);
        });
        $filter->add('manufacturer', function (PurchasingDataQueryBuilder $qb, $manufacturer) {
            $qb->byManufacturer($manufacturer);
        });
        $filter->add('hasManufacturer', function (PurchasingDataQueryBuilder $qb, $value) {
            if ('no' == $value) {
                $qb->doesNotHaveManufacturer();
            } elseif ('yes' == $value) {
                $qb->hasManufacturer();
            }
        });
        $filter->add('version', function (PurchasingDataQueryBuilder $qb, $version) {
            $qb->byVersion($version);
        });
        $filter->add('orderQty', function (PurchasingDataQueryBuilder $qb, $orderQty) {
            $qb->byOrderQty($orderQty);
        });
        $filter->add('neededBy', function (PurchasingDataQueryBuilder $qb, $neededBy) {
            $qb->canSupplyByDate($neededBy);
        });
        $filter->add('preferred', function (PurchasingDataQueryBuilder $qb, $preferred) {
            if ($preferred == 'yes') {
                $qb->isPreferred();
            }
        });
        $filter->add('canSync', function (PurchasingDataQueryBuilder $qb, $canSync) {
            if ($canSync == 'yes') {
                $qb->hasApi();
            } elseif ( $canSync == 'no') {
                $qb->hasNoApi();
            }
        });
        $filter->add('inGeppettoBom', function (PurchasingDataQueryBuilder $qb, $inModule) {
            if ($inModule == 'yes') {
                $qb->usedInGepettoBom();
            } elseif ($inModule == 'no') {
                $qb->notUsedInGeppettoBom();
            }
        });
        $filter->add('matching', function (PurchasingDataQueryBuilder $qb, $pattern) {
            $qb->matches($pattern);
        });
        $filter->add('active', function (PurchasingDataQueryBuilder $qb, $active) {
            if ('yes' == $active) {
                $qb->isActive();
            } elseif ('no' == $active) {
                $qb->isInactive();
            }
        });
        $filter->add('_order', function (PurchasingDataQueryBuilder $qb, $orderBy) {
            switch ($orderBy) {
                case 'supplier':
                    $qb->orderBySupplier();
                    break;
                case 'catalogNo':
                    $qb->orderByCatalogNo();
                    break;
                default:
                    $qb->orderBySku();
                    break;
            }
        });
        return $filter;
    }

    /** @return PurchasingDataQueryBuilder */
    public function createBuilder()
    {
        return new PurchasingDataQueryBuilder($this);
    }

    /** @return PurchasingData[] */
    public function findActive(ManufacturedStockItem $item)
    {
        $qb = $this->queryActive($item);
        return $qb->getQuery()->getResult();
    }

    /** @return QueryBuilder */
    public function queryActive(ManufacturedStockItem $item)
    {
        $orderQty = max($item->getEconomicOrderQty(), 1);
        return $this->queryPreferredByItem($item, $orderQty)
            ->byActiveLocations()
            ->getQueryBuilder();
    }

    /** @return PurchasingData|object|null */
    public function findPreferredByLocationAndVersion(
        Facility $location,
        Item $item,
        Version $version,
        $orderQty = 1,
        $leadTime = null)
    {
        return $this->queryPreferredByItem($item, $orderQty, $leadTime)
            ->byLocation($location)
            ->byVersion($version)
            ->getFirstResultOrNull();
    }

    /**
     * Fetches the preferred PurchasingData for the given stock item
     * that has all of the fields required for manufacturing.
     *
     * @param StockItem $item
     * @return PurchasingData|object|null
     */
    public function findPreferredForManufacturing(Item $item)
    {
        return $this->queryPreferredByItem($item)
            ->hasManufacturerCode()
            ->getFirstResultOrNull();
    }

    /**
     * Returns the PurchasingData record for the preferred supplier that
     * is the best match for the given order quantity and lead time.
     *
     * @return PurchasingData|object|null
     *  The preferred PurchasingData record, or null if there is no match.
     */
    public function findPreferred(Item $item, $orderQty = 1, $leadTime = null)
    {
        return $this->createBuilder()
            ->isActive()
            ->byItem($item, $orderQty, $leadTime)
            ->orderByPreferred()
            ->getFirstResultOrNull();
    }

    /**
     * @return PurchasingDataQueryBuilder
     */
    private function queryPreferredByItem(Item $item, $orderQty = 1, $leadTime = null)
    {
        return $this->createBuilder()
            ->isActive()
            ->byItem($item, $orderQty, $leadTime)
            ->orderByPreferred();
    }

    /**
     * @return PurchasingData|object|null
     */
    public function queryPreferredByItemSku(string $sku)
    {
        return $this->createBuilder()
            ->isActive()
            ->bySku($sku)
            ->orderByPreferred()
            ->getFirstResultOrNull();
    }

    /** @return PurchasingData[] */
    public function findAllPurchasingDataBySku(string $sku)
    {
        $qb = $this->queryAllByItemSku($sku);
        return $qb->getResult();
    }

    /** @return PurchasingData[] */
    public function findAllActivePurchasingData()
    {
        $qb = $this->queryAllActive();
        return $qb->getResult();
    }

    /**
     * @param DateTime $dateTime
     * @return PurchasingData[]
     */
    public function findAllActiveAncientPurchasingData(DateTime $dateTime)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('pd')
            ->from(PurchasingData::class, 'pd')
            ->join('pd.stockItem', 'item')
            ->andWhere('item.discontinued = 0')
            ->andWhere('pd.endOfLife is null or pd.endOfLife > :today')
            ->setParameter('today', date('Y-m-d'))
            ->andWhere('pd.lastSync < :lastSyncSince')
            ->setParameter('lastSyncSince', $dateTime);
        return $qb->getQuery()->getResult();
    }

    /**
     * @return PurchasingData|object|null
     */
    public function queryAllActive()
    {
        return $this->createBuilder()
                    ->isActive();
    }

    /**
     * @return PurchasingData|object|null
     */
    public function queryAllByItemSku(string $sku)
    {
        return $this->createBuilder()
            ->isActive()
            ->bySku($sku);
    }

    /**
     * Makes $pd the preferred purchasing data for its stock item.
     */
    public function setPreferred(PurchasingData $pd)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->update(PurchasingData::class, 'pd')
            ->set('pd.preferred', 'if(pd.id = :id, 1, 0)')
            ->setParameter('id', $pd->getId())
            ->where('pd.stockItem = :item')
            ->setParameter('item', $pd->getSku());
        $query = $qb->getQuery();
        $query->execute();
        $this->_em->clear(PurchasingData::class);
    }

    /**
     * @return PurchasingData|object|null
     */
    public function findPreferredByVersion(
        Item $item,
        Version $version,
        $orderQty = 1,
        $leadTime = null)
    {
        return $this->queryPreferredByItem($item, $orderQty, $leadTime)
            ->byVersion($version)
            ->getFirstResultOrNull();
    }

    public function findPreferredForRequirement(RequirementInterface $requirement)
    {
        $item = $requirement->getStockItem();
        $orderQty = max(
            $requirement->getTotalQtyUndelivered(),
            $item->getEconomicOrderQty()
        );
        return $this->findPreferredByVersion(
            $item,
            $requirement->getVersion(),
            $orderQty
        );
    }

    /** @return PurchasingData|object|null */
    public function findPreferredBySupplier(
        Supplier $supplier,
        Item $item,
        $orderQty = 1,
        $leadTime = null)
    {
        return $this->queryPreferredByItem($item, $orderQty, $leadTime)
            ->bySupplier($supplier)
            ->getFirstResultOrNull();
    }

    /** @return PurchasingData[] */
    public function findBySupplier(
        Supplier $supplier,
        Item $item,
        $orderQty = 1,
        $leadTime = null)
    {
        return $this->queryPreferredByItem($item, $orderQty, $leadTime)
            ->bySupplier($supplier)
            ->getResult();
    }

    /**
     * @return boolean True if a purchasing data record for the given supplier,
     *   item, and exact version already exists.
     */
    public function exists(
        Supplier $supplier,
        Item $item,
        Version $version,
        $orderQty = 1,
        $leadTime = null)
    {
        $result = $this->queryPreferredByItem($item, $orderQty, $leadTime)
            ->bySupplier($supplier)
            ->byExactVersion($version)
            ->getCount();
        return $result > 0;
    }

    /** @return PurchasingData|object|null */
    public function findPreferredBySupplierAndVersion(
        Supplier $supplier,
        Item $item,
        Version $version,
        $orderQty = 1,
        $leadTime = null)
    {
        return $this->queryPreferredByItem($item, $orderQty, $leadTime)
            ->bySupplier($supplier)
            ->byVersion($version)
            ->getFirstResultOrNull();
    }

    /** @return PurchasingData[] */
    public function findByItem(Item $item)
    {
        $qb = $this->queryPreferredByItem($item);
        return $qb->getResult();
    }

    private function calculateLeadTime($date)
    {
        if (! $date) return null;
        if (is_string($date)) $date = new DateTime($date);
        $today = new DateTime(date('Y-m-d'));
        $diff = $date->diff($today);
        return $diff->days;
    }

    /** @return PurchasingData|object|null */
    public function findPreferredForSingleItemPO(SingleItemPurchaseOrder $sipo)
    {
        $date = $sipo->getRequestedDate();
        $leadTime = $this->calculateLeadTime($date);
        $qb = $this->queryPreferredByItem(
            $sipo->getStockItem(),
            $sipo->getOrderQty(),
            $leadTime
        );
        return $qb->byVersion($sipo->getVersion())
            ->getFirstResultOrNull();
    }

    /** @return PurchasingData|object|null */
    public function findUnique(Supplier $supplier, $catalogNo, $quotationNo)
    {
        return $this->findOneBy([
            'supplier' => $supplier->getId(),
            'catalogNumber' => $catalogNo,
            'quotationNumber' => $quotationNo,
        ]);
    }

    /** @return PurchasingData|object|null */
    public function findUniqueByLocation(Facility $location, $catalogNo, $quotationNo)
    {
        return $this->findOneBy([
            'location' => $location->getId(),
            'catalogNumber' => $catalogNo,
            'quotationNumber' => $quotationNo,
        ]);
    }

    /**
     * @return PurchasingData[]
     */
    public function findManufacturingExpenses(Supplier $supplier)
    {
        static $expenses = ['STENCIL', 'PROGRAMMING'];
        $qb = $this->createQueryBuilder('pd');
        $qb->where('pd.supplier = :supplierId')
            ->andWhere('pd.stockItem in (:expenses)')
            ->setParameters([
                'supplierId' => $supplier->getId(),
                'expenses' => $expenses,
            ]);

        return $qb->getQuery()->getResult();
    }

    public function findMinimumLotCharge(Supplier $supplier): ?PurchasingData
    {
        static $minLotSku = 'MINIMUMLOT';
        $qb = $this->createQueryBuilder('pd');
        $qb->where('pd.supplier = :supplierId')
            ->andWhere('pd.stockItem = :minLotSku')
            ->setParameters([
                'supplierId' => $supplier->getId(),
                'minLotSku' => $minLotSku,
            ]);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return PurchasingData[]
     *  All purchasing data records for active products from the
     *  given supplier.
     */
    public function findAllActiveBySupplier(Supplier $supplier)
    {
        $qb = $this->queryAllActiveBySupplier($supplier);
        return $qb->getQuery()->getResult();
    }

    /** @return QueryBuilder */
    public function queryAllActiveBySupplier(Supplier $supplier)
    {
        return $this->createBuilder()
            ->isActive()
            ->bySupplier($supplier)
            ->orderBySku()
            ->getQueryBuilder();
    }

    public function sellsCategories(Supplier $supplier, array $categories)
    {
        $qb = $this->queryAllActiveBySupplier($supplier);
        $qb->andWhere('item.category in (:categories)')
            ->setParameter('categories', $categories);
        $qb->select('count(pd.id)');
        $count = (int) $qb->getQuery()->getSingleScalarResult();
        return $count > 0;
    }

    /**
     * True if $purchData has been used in any purchases.
     *
     * If it has been used, it cannot be deleted.
     * @return boolean
     */
    public function hasBeenUsed(PurchasingData $purchData)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(poItem.id)')
            ->from(StockProducer::class, 'poItem')
            ->where('poItem.purchasingData = :pd')
            ->setParameter('pd', $purchData->getId());
        $count = $qb->getQuery()->getSingleScalarResult();
        return $count > 0;
    }
}
