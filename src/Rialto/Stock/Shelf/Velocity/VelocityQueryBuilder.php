<?php

namespace Rialto\Stock\Shelf\Velocity;

use Doctrine\ORM\QueryBuilder;
use Gumstix\Time\DateRange;
use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Move\StockMove;
use Rialto\Stock\Shelf\Velocity;

class VelocityQueryBuilder
{
    /** @var QueryBuilder */
    private $qb;

    public function __construct(DbManager $dbm)
    {
        $this->qb = $dbm->createQueryBuilder();
        $this->qb->select('item.stockCode as sku')
            ->addSelect('facility.id as facilityId')
            ->addSelect('sum(abs(move.quantity)) as absQtyMoved')
            ->addSelect('count(distinct move.id) as numMoves')
            ->addSelect('max(move.date) as lastMovedOn')
            ->from(StockMove::class, 'move')
            ->join('move.facility', 'facility')
            ->join('move.stockBin', 'bin')
            ->join('bin.stockItem', 'item')
            ->groupBy('item.stockCode')
            ->addGroupBy('move.facility');
    }

    public function byDates(DateRange $range)
    {
        if ($range->hasStart()) {
            $this->qb->andWhere('move.date >= :startDate')
                ->setParameter('startDate', $range->getStart());
        }
        if ($range->hasEnd()) {
            $this->qb->andWhere('move.date <= : endDate')
                ->setParameter('endDate', $range->getEnd());
        }
        return $this;
    }

    public function byFacility(Facility $facility)
    {
        $this->qb->andWhere('move.facility = :facility')
            ->setParameter('facility', $facility);
        return $this;
    }

    public function byRack($rack)
    {
        $this->qb
            ->join('bin.shelfPosition', 'pos')
            ->join('pos.shelf', 'shelf')
            ->andWhere('shelf.rack = :rack')
            ->setParameter('rack', $rack);
        return $this;
    }

    public function byItem($item)
    {
        if ($item instanceof Item) {
            $item = $item->getSku();
        }
        $this->qb->andWhere('item.stockCode like :sku')
            ->setParameter('sku', $item);
        return $this;
    }

    public function orderByDateMoved()
    {
        $this->qb
            ->orderBy('lastMovedOn', 'desc')
            ->addOrderBy('numMoves', 'desc');
        return $this;
    }

    public function setMaxResults($limit)
    {
        $this->qb
            ->setMaxResults($limit);
        return $this;
    }

    /**
     * @return string
     */
    public function getDQL()
    {
        return $this->qb->getDQL();
    }

    private function getResult()
    {
        return $this->qb->getQuery()->getResult();
    }

    /**
     * @return Velocity[]
     */
    public function getIndexedResult()
    {
        $rows = $this->getResult();
        $index = [];
        foreach ($rows as $row) {
            $sku = $row['sku'];
            $fac = $row['facilityId'];
            $index[$sku][$fac] = $this->instantiate($row);
        }
        return $index;
    }

    /**
     * @return Velocity
     */
    public function getSingleResult()
    {
        $row = $this->qb->getQuery()->getOneOrNullResult();
        return $this->instantiate($row);
    }

    private function instantiate($row)
    {
        $lastMovedOn = $row ? $row['lastMovedOn'] : null;
        return Velocity::fromDate($lastMovedOn);
    }
}
