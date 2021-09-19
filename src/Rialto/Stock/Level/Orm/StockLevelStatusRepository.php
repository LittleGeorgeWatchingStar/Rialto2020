<?php

namespace Rialto\Stock\Level\Orm;

use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\AssemblyStockItem;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Level\AssemblyStockLevel;
use Rialto\Stock\Level\StockLevelStatus;
use Rialto\Stock\Level\StockLevelUpdate;

class StockLevelStatusRepository extends RialtoRepositoryAbstract
{
    public function createBuilder()
    {
        return new StockLevelStatusQueryBuilder($this);
    }

    /** @return StockLevelStatus[] */
    public function findByItem(StockItem $item)
    {
        return $this->createBuilder()
            ->byItem($item)
            ->isActiveLocation()
            ->excludeSpecialLocations()
            ->getResult();
    }

    /** @return StockLevelStatus|object|null */
    public function findIfExists(PhysicalStockItem $item, Facility $location)
    {
        return $this->findOneBy([
            'stockItem' => $item->getSku(),
            'location' => $location->getId()
        ]);
    }

    /**
     * Ensures stock level records exist for the given item.
     */
    public function initializeStockLevels(PhysicalStockItem $stockItem)
    {
        $locationRepo = $this->_em->getRepository(Facility::class);
        $locations = $locationRepo->findAll();
        foreach ($locations as $location) {
            $this->findOrCreate($stockItem, $location);
        }
    }

    public function findOrCreate(PhysicalStockItem $item,
                                 Facility $location): StockLevelStatus
    {
        $status = $this->findIfExists($item, $location);
        if (!$status) {
            $status = new StockLevelStatus($item, $location);
            $this->_em->persist($status);
        }
        return $status;
    }

    public function getOrderPointSummary(array $filters)
    {
        $params = [
            'issueType' => SystemType::WORK_ORDER_ISSUE,
            'invoiceType' => SystemType::SALES_INVOICE,
            'excludeLocations' => Facility::IN_TRANSIT_ID,
        ];
        $conditions = [];
        foreach ($filters as $name => $value) {
            if (!$value) {
                continue;
            }
            switch ($name) {
                case 'location':
                    $conditions[] = 'and location.LocCode = :location';
                    $params['location'] = $value->getId();
                    break;
                case 'sku':
                    $conditions[] = 'and item.StockID like :sku';
                    $params['sku'] = "%$value%";
                    break;
                // no default
            }
        }
        $conditions = join(' ', $conditions);
        $sql = "select location.LocCode as locationID
            , location.LocationName as locationName
            , item.StockID as stockCode
            , item.StockID as sku
            , item.shippingVersion as version
            , item.Description as description
            , item.EOQ as eoq
            , st.orderPoint
            , round(ifnull(bins.qtyRemaining, 0)) as qtyRemaining
            , round(ifnull(moves.velocity, 0)) as velocity
            from StockMaster item
            join Locations location
            left join StockLevelStatus st
              on st.stockCode = item.StockID
              and st.locationID = location.LocCode
            left join (
              select sum(bin.Quantity) as qtyRemaining
              , bin.StockID
              , bin.LocCode
              from StockSerialItems bin
              group by bin.StockID, bin.LocCode
            ) as bins
              on bins.StockID = item.StockID
              and bins.LocCode = location.LocCode
            left join (
              select -sum(move.quantity) as velocity
              , move.stockCode
              , move.locationID
              from StockMove move
              where move.systemTypeID in (:issueType, :invoiceType)
              and move.dateMoved >= date_sub(date(now()), interval 3 month)
              group by move.stockCode, move.locationID
            ) as moves
              on moves.stockCode = item.StockID
              and moves.locationID = location.LocCode
            where item.MBflag in ('B', 'M')
            and item.Discontinued = 0
            and location.Active = 1
            and location.parentID is null
            and location.LocCode not in (:excludeLocations)
            $conditions
            order by stockCode, locationName";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * @return StockLevelUpdate[]
     */
    public function findStale(
        Facility $facility,
        int $categoryID = StockCategory::PRODUCT,
        int $limit = 10): array
    {
        return $this->createUpdateBuilder()
            ->byFacility($facility)
            ->isStale()
            ->byCategory($categoryID)
            ->itemIsActive()
            ->orderByDateUpdated()
            ->getStockUpdates();
    }

    private function createUpdateBuilder(): StockLevelQueryBuilder
    {
        return new StockLevelQueryBuilder($this->_em);
    }

    public function findUpdate(PhysicalStockItem $item,
                               Facility $location): StockLevelUpdate
    {
        $results = $this->createUpdateBuilder()
            ->byFacility($location)
            ->bySku($item->getSku())
            ->itemIsActive()
            ->orderByDateUpdated()
            ->getStockUpdates();

        assertion(count($results) == 1);
        return $results[0];
    }

    /** @return StockLevelUpdate[] */
    public function findAllUpdates(Facility $location,
                                   array $categoryIds): array
    {
        return $this->createUpdateBuilder()
            ->byFacility($location)
            ->byCategories($categoryIds)
            ->itemIsActive()
            ->orderByDateUpdated()
            ->getStockUpdates();
    }

    /**
     * @param string|Facility $facility
     */
    public function getAssemblyStockLevel(AssemblyStockItem $item,
                                          $facility): AssemblyStockLevel
    {
        $components = $this->createBuilder()
            ->componentsOfAssembly($item)
            ->byLocation($facility)
            ->getResult();
        return new AssemblyStockLevel($item, $components);
    }
}
