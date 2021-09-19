<?php

namespace Rialto\Stock\Shelf\Position;


use Doctrine\Common\Persistence\ObjectManager;
use Gumstix\Doctrine\HighLevelQueryBuilder;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Shelf\ShelfPosition;

/**
 * For querying ShelfPositions.
 */
class PositionQueryBuilder extends HighLevelQueryBuilder
{
    public function __construct(ObjectManager $om)
    {
        $repo = $om->getRepository(ShelfPosition::class);
        parent::__construct($repo, 'pos');
        $this->qb
            ->join('pos.shelf', 'shelf')
            ->join('shelf.rack', 'rack')
            ->leftJoin('shelf.binStyles', 'style');
    }

    public function canAccomodateBin(StockBin $bin)
    {
        $this->byFacility($bin->getFacility());
        $this->byBinStyle($bin->getBinStyle());
        return $this;
    }

    public function byFacility($facility)
    {
        $this->qb
            ->andWhere('rack.facility = :facility')
            ->setParameter('facility', $facility);
        return $this;
    }

    public function byBinStyle($binStyle)
    {
        $this->qb
            ->andWhere('style = :style')
            ->setParameter('style', $binStyle);
        return $this;
    }

    public function byVelocity($velocity)
    {
        $this->qb
            ->andWhere('shelf.velocity = :velocity')
            ->setParameter('velocity', $velocity);
        return $this;
    }

    public function isUnoccupied()
    {
        $this->qb->andWhere('pos.bin is null');
        return $this;
    }

    public function orderByCoordinates()
    {
        $this->qb
            ->orderBy('rack.name', 'asc')
            ->orderBy('shelf.indexNo', 'asc')
            ->addOrderBy('pos.x', 'asc')
            ->addOrderBy('pos.y', 'asc')
            ->addOrderBy('pos.z', 'asc');
        return $this;
    }
}
