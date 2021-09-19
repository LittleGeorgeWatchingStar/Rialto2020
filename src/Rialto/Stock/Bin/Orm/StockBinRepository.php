<?php

namespace Rialto\Stock\Bin\Orm;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gumstix\Doctrine\HighLevelFilter;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Manufacturing\Requirement\MissingStockRequirement;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Sales\Returns\SalesReturnItem;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Web\AllStockReport;
use Rialto\Stock\Item;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Move\StockMove;
use Rialto\Stock\Shelf\Velocity;
use Rialto\Stock\VersionedItem;

/**
 * Database mapper between StockSerialItems table and StockBin class.
 */
class StockBinRepository extends FilteringRepositoryAbstract
{
    /** @return StockBinQueryBuilder */
    public function createBuilder()
    {
        return new StockBinQueryBuilder($this);
    }

    /** @return Query */
    public function queryByFilters(array $params)
    {
        $filter = new HighLevelFilter($this->createBuilder());
        $filter->add('stockItem', function (StockBinQueryBuilder $qb, $item) {
            $qb->byItem($item);
        });
        $filter->add('sku', function (StockBinQueryBuilder $qb, $sku) {
            $qb->bySku($sku);
        });
        $filter->add('facility', function (StockBinQueryBuilder $qb, $facility) {
            $qb->atFacility($facility);
        });
        $filter->add('rack', function (StockBinQueryBuilder $qb, $rack) {
            $qb->byRack($rack);
        });
        $filter->add('isShelved', function (StockBinQueryBuilder $qb, $shelved) {
            if ($shelved == 'yes') {
                $qb->isShelved();
            } elseif ($shelved == 'no') {
                $qb->isNotShelved();
            }
        });
        $filter->add('styles', function (StockBinQueryBuilder $qb, $styles) {
            $qb->byBinStyles($styles);
        });
        $filter->add('velocity', function (StockBinQueryBuilder $qb, Velocity $velocity) {
            $qb->byVelocity($velocity);
        });
        $filter->add('empty', function (StockBinQueryBuilder $qb, $empty) {
            if ('yes' != $empty) {
                $qb->available();
            }
        });
        $filter->add('inTransit', function (StockBinQueryBuilder $qb, $inTransit) {
            if ('yes' == $inTransit) {
                $qb->inTransit();
            } elseif ('no' == $inTransit) {
                $qb->notInTransit();
            }
        });
        $filter->add('_order', function (StockBinQueryBuilder $qb, $orderBy) {
            $qb->orderBySku()
                ->orderByLocation()
                ->orderById();
        });
        if (empty($params['empty'])) {
            $params['empty'] = 'no';
        }
        if (empty($params['_order'])) {
            $params['_order'] = 'location';
        }
        return $filter->buildQuery($params);
    }


    public function findById(string $id): ?StockBin
    {
        return $this->findOneBy([
            'id' => $id,
        ]);
    }

    /**
     * @return StockBin[]
     */
    public function findByItem(
        StockItem $item,
        Version $version,
        $getEmptyBins = false)
    {
        $qb = $this->queryByItem($item, $version, $getEmptyBins);
        return $qb->getQuery()->getResult();
    }

    /** @return QueryBuilder */
    public function queryByItem(
        Item $item,
        Version $version = null,
        $getEmptyBins = false)
    {
        if (null === $version) {
            $version = Version::any();
        }

        $qb = $this->createQueryBuilder('bin')
            ->andWhere('bin.stockItem = :item')
            ->setParameter('item', $item->getSku());
        if (!$getEmptyBins) {
            $qb->andWhere('bin.quantity > 0');
        }
        if ($version->isSpecified()) {
            $qb->andWhere('bin.version = :version')
                ->setParameter('version', (string) $version);
        }
        return $qb;
    }

    /**
     * @return StockBin[]
     */
    public function findBySku(
        $sku,
        Version $version = null,
        $getEmptyBins = false)
    {
        if (null === $version) {
            $version = Version::any();
        }

        $qb = $this->createQueryBuilder('bin')
            ->andWhere('bin.stockItem = :item')
            ->setParameter('item', $sku);
        if (!$getEmptyBins) {
            $qb->andWhere('bin.quantity > 0');
        }
        if ($version->isSpecified()) {
            $qb->andWhere('bin.version = :version')
                ->setParameter('version', (string) $version);
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * @return StockBin[]
     */
    public function findByLocationAndItem(
        Facility $location,
        Item $item,
        Version $version = null,
        $getEmptyBins = false)
    {
        $qb = $this->queryByLocationAndItem($location, $item, $version, $getEmptyBins);
        return $qb->getQuery()->getResult();
    }

    /** @return QueryBuilder */
    public function queryByLocationAndItem(
        Facility $facility,
        Item $item,
        Version $version = null,
        $getEmptyBins = false)
    {
        $qb = $this->queryByItem($item, $version, $getEmptyBins);
        $qb->andWhere('bin.facility = :loc')
            ->setParameter('loc', $facility)
            ->orderBy('bin.quantity', 'ASC');

        return $qb;
    }

    /** @return StockBin[] */
    public function findByParentLocationAndItem(Facility $facility,
                                                Item $item)
    {
        $version = ($item instanceof VersionedItem)
            ? $item->getVersion()
            : null;
        $qb = $this->queryByItem($item, $version);
        $qb->join('bin.facility', 'binLoc')
            ->andWhere('binLoc = :loc or binLoc.parentLocation = :loc')
            ->setParameter('loc', $facility);
        return $qb->getQuery()->getResult();
    }

    /**
     * @return StockBin[]
     */
    public function findForStockReport(AllStockReport $report)
    {
        $qb = $this->createQueryBuilder('bin');

        $qb->join('bin.stockItem', 'item')
            ->join('bin.binStyle', 'style')
            ->andWhere('bin.quantity > 0')
            ->orderBy('item.stockCode')
            ->addOrderBy('bin.id');
        if ($report->location) {
            $qb->andWhere('bin.facility = :loc')
                ->setParameter('loc', $report->location);
        }
        if ($report->sellable == 'yes') {
            $qb->andWhere('item.category in (:sellable)')
                ->setParameter('sellable', StockCategory::getSellableIds());
        } elseif ($report->sellable == 'no') {
            $qb->andWhere('item.category not in (:sellable)')
                ->setParameter('sellable', StockCategory::getSellableIds());
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * Returns all bins moved in the stock move whose type and typeno are given.
     *
     * @param SystemType $type
     * @param int $typeNo
     * @return StockBin[]
     */
    public function findBySystemType(SystemType $type, $typeNo)
    {
        $qb = $this->createQueryBuilder('bin')
            ->from(StockMove::class, 'move')
            ->where('move.stockBin = bin')
            ->andWhere('move.systemType = :sysType')
            ->andWhere('move.systemTypeNumber = :typeNo');
        $qb->setParameters([
            'sysType' => $type->getId(),
            'typeNo' => $typeNo
        ]);
        return $qb->getQuery()->getResult();
    }

    /**
     * @return QueryBuilder
     */
    public function queryForSalesReturnItem(SalesReturnItem $item)
    {
        $version = $item->getVersion();

        $qb = $this->createQueryBuilder('bin');
        $qb->andWhere('bin.stockItem = :item')
            ->andWhere('bin.facility = :location')
            ->andWhere('bin.quantity > 0');

        $params = [
            'item' => $item->getSku(),
            'location' => Facility::TESTING_ID,
        ];

        if ($version->isSpecified()) {
            $qb->andWhere('bin.version = :version');
        }
        $params['version'] = $version->getVersionCode();

        $qb->setParameters($params);

        return $qb;
    }

    /** @return float */
    public function getQtyInStock(
        Facility $facility,
        PhysicalStockItem $item,
        Version $version = null)
    {
        $qb = $this->queryQtyInStock($item, $version);
        $qb->andWhere('bin.facility = :location')
            ->setParameter('location', $facility);
        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    /** @return float */
    public function getTotalQtyInStock(PhysicalStockItem $item, Version $version = null)
    {
        $qb = $this->queryQtyInStock($item, $version);
        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    /** @return QueryBuilder */
    private function queryQtyInStock(PhysicalStockItem $item, Version $version = null)
    {
        if (!$version) $version = Version::any();
        $qb = $this->createQueryBuilder('bin')
            ->select('sum(bin.quantity)')
            ->where('bin.stockItem = :item')
            ->setParameter('item', $item->getSku());
        if ($version->isSpecified()) {
            $qb->andWhere('bin.version = :version')
                ->setParameter('version', $version->getVersionCode());
        }
        $qb->setMaxResults(1);
        return $qb;
    }

    /**
     * @return StockBin[]
     */
    public function findByLocationAndManufacturerCode(
        Facility $facility,
        $code)
    {
        $qb = $this->createQueryBuilder('bin');
        $qb->andWhere('bin.facility = :location')
            ->setParameter('location', $facility)
            ->andWhere('bin.manufacturerCode like :code')
            ->setParameter('code', "%$code%");
        return $qb->getQuery()->getResult();
    }

    /** @return StockBin[] */
    public function findMissingFromSupplier(Supplier $supplier)
    {
        $facility = $supplier->getFacility();
        assertion($facility !== null);
        $qb = $this->createQueryBuilder('bin');
        $qb->join('bin.allocations', 'alloc')
            ->join('alloc.requirement', 'req')
            ->andWhere('req instance of ' . MissingStockRequirement::class)
            ->andWhere('bin.facility = :location')
            ->setParameter('location', $facility)
            ->orderBy('bin.stockItem', 'asc')
            ->addOrderBy('bin.id', 'asc');
        return $qb->getQuery()->getResult();
    }

    /** @return StockBin[] */
    public function findNeededAtSupplier(Supplier $supplier)
    {
        $facility = $supplier->getFacility();
        assertion($facility !== null);
        $qb = $this->createQueryBuilder('bin');
        $qb->join('bin.allocations', 'alloc')
            ->join('alloc.requirement', 'req')
            ->andWhere('req NOT INSTANCE OF ' . MissingStockRequirement::class)
            ->andWhere('bin.facility = :location')
            ->setParameter('location', $facility)
            ->orderBy('bin.stockItem', 'asc')
            ->addOrderBy('bin.id', 'asc');
        return $qb->getQuery()->getResult();
    }
}
