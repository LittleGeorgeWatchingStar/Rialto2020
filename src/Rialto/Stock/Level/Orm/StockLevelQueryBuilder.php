<?php

namespace Rialto\Stock\Level\Orm;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Level\CompleteStockLevel;
use Rialto\Stock\Level\StockLevelStatus;
use Rialto\Stock\Level\StockLevelUpdate;

class StockLevelQueryBuilder
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var QueryBuilder */
    private $qb;

    private $params = [];

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->qb = $em->getConnection()->createQueryBuilder();

        $this->qb
            ->from('StockLevelStatus', 'status')
            ->join('status', 'StockMaster', 'item', 'status.stockCode = item.StockID')
            ->join('status', 'Locations', 'location', 'status.locationID = location.LocCode')
            ->leftJoin('status', 'StockSerialItems', 'bin',
                'status.stockCode = bin.StockID and status.locationID = bin.LocCode')
            ->leftJoin('bin', $this->getAllocationSubquery(), 'alloc',
                "bin.SerialNo = alloc.SourceNo")
            ->groupBy('status.stockCode')
            ->addGroupBy('status.LocationID');
    }

    /**
     * We have to sum the quantity allocated in a subquery, grouped by bin ID.
     * If we don't, then any bin which has more than one allocation will appear
     * in the result set multiple times, and its quantity will be multiplied
     * accordingly, giving the wrong result.
     */
    private function getAllocationSubquery(): string
    {
        return "
            (select sum(ifnull(Qty, 0)) as Qty
            , SourceNo
            from StockAllocation
            where SourceType = 'StockBin'
            group by SourceNo)
        ";
    }

    public function bySku(string $sku): self
    {
        $this->qb->andWhere('status.stockCode = :sku');
        return $this->setParameter('sku', $sku);
    }

    private function setParameter(string $name, $value): self
    {
        $this->params[$name] = $value;
        return $this;
    }

    public function bySkuSubstring(string $substring): self
    {
        $this->qb->andWhere('status.stockCode like :skuSubstring');
        return $this->setParameter('skuSubstring', "%$substring%");
    }

    public function byCategory(int $categoryId): self
    {
        $this->qb->andWhere('item.CategoryID = :category');
        return $this->setParameter('category', $categoryId);
    }

    public function byCategories(array $categoryIds): self
    {
        $this->qb->andWhere('item.CategoryID in (:categories)');
        return $this->setParameter('categories', $categoryIds);
    }

    public function byFacility(Facility $facility): self
    {
        $this->qb->andWhere('status.LocationID = :facility');
        return $this->setParameter('facility', $facility->getId());
    }

    public function isActiveLocation(): self
    {
        $this->qb->andWhere('location.Active = 1');
        return $this;
    }

    public function isInStock(): self
    {
        $this->qb->andHaving('currentInStock > 0');
        return $this;
    }

    public function itemIsActive(): self
    {
        $this->qb->andWhere('item.Discontinued = 0');
        return $this;
    }

    /**
     * Select those results whose StockLevelStatus is out-of-date.
     */
    public function isStale(): self
    {
        $this->qb->andHaving('
            status.qtyInStock != currentInStock or
            status.qtyAllocated != currentAllocated or
            status.dateUpdated is null');
        return $this;
    }

    public function groupByVersion(): self
    {
        $this->qb->addGroupBy('bin.Version');
        return $this;
    }

    public function orderBySku(): self
    {
        $this->qb->orderBy('sku');
        return $this;
    }

    public function orderByDateUpdated(): self
    {
        $this->qb
            ->orderBy('ifnull(status.dateUpdated, 0)', 'asc')
            ->addOrderBy('item.StockID', 'asc');
        return $this;
    }

    /** @return CompleteStockLevel[] */
    public function getStockLevels(): array
    {
        $this->qb->select(
            'status.stockCode as sku',
            'status.locationID',
            'sum(ifnull(bin.Quantity, 0)) as currentInStock',
            'sum(ifnull(alloc.Qty, 0)) as currentAllocated',
            'group_concat(distinct bin.Version) as version',
            'status.orderPoint',
            'status.dateUpdated');
        $this->qb->setParameters($this->params);
        $stmt = $this->qb->execute();
        return $this->instantiateAll($stmt->fetchAll());
    }

    private function instantiateAll(array $rows): array
    {
        $results = [];
        foreach ($rows as $row) {
            $version = $row['version'];
            $version = is_substring(',', $version)
                ? Version::any()
                : new Version($version);
            $facility = $this->em->find(Facility::class, $row['locationID']);

            $results[] = new CompleteStockLevel(
                $row['sku'],
                $version,
                $facility,
                $row['currentInStock'],
                $row['currentAllocated'],
                $row['orderPoint']);
        }
        return $results;
    }

    /**
     * @return StockLevelUpdate[]
     */
    public function getStockUpdates(): array
    {
        $this->qb->select('status.*')
            ->addSelect('sum(ifnull(bin.Quantity, 0)) as currentInStock')
            ->addSelect('sum(ifnull(alloc.Qty, 0)) as currentAllocated');

        $rsm = new ResultSetMappingBuilder($this->em);
        $rsm->addRootEntityFromClassMetadata(
            StockLevelStatus::class, 'previous'
        );
        $rsm->addScalarResult('currentInStock', 'currentInStock', 'integer');
        $rsm->addScalarResult('currentAllocated', 'currentAllocated', 'integer');
        $query = $this->em->createNativeQuery($this->qb->getSQL(), $rsm);
        $query->setParameters($this->params);

        return array_map(function (array $result) {
            return new StockLevelUpdate(
                $result[0],
                $result['currentInStock'],
                $result['currentAllocated']);
        }, $query->getResult());
    }
}
