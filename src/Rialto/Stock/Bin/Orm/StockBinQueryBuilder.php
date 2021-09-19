<?php

namespace Rialto\Stock\Bin\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Gumstix\Doctrine\HighLevelQueryBuilder;
use Rialto\Allocation\Requirement\RequirementInterface;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Move\StockMove;
use Rialto\Stock\Returns\ReturnedItem;
use Rialto\Stock\Shelf\Rack;
use Rialto\Stock\Shelf\Velocity;
use Rialto\Stock\VersionedItem;

class StockBinQueryBuilder extends HighLevelQueryBuilder
{
    public function __construct(StockBinRepository $repo)
    {
        parent::__construct($repo, 'bin');
        $this->qb->leftJoin('bin.stockItem', 'item');
        $this->qb->leftJoin('bin.shelfPosition', 'pos');
    }

    public function available()
    {
        $this->qb->andWhere('bin.quantity > 0');
        return $this;
    }

    public function allocatable()
    {
        $this->qb->andWhere('bin.allocatable = 1');
        return $this;
    }

    public function notUnresolved(): self
    {
        $ri = ReturnedItem::class;
        $subquery = "select ri.id from $ri ri where ri.bin = bin";
        $this->qb->andWhere("not exists ($subquery)");
        return $this;
    }

    public function excludeBin($stockBin)
    {
        $this->qb->andWhere('bin != :excludeBin')
            ->setParameter('excludeBin', $stockBin);
        return $this;
    }

    /**
     * @deprecated
     */
    public function atLocation(Facility $facility)
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->atFacility($facility);
    }

    public function atFacility(Facility $facility)
    {
        $this->qb->andWhere('bin.facility = :facility')
            ->setParameter('facility', $facility);
        return $this;
    }

    public function inTransit()
    {
        $this->qb->andWhere('bin.transfer is not null');
        return $this;
    }

    public function notInTransit()
    {
        $this->qb->andWhere('bin.transfer is null');
        return $this;
    }

    public function byRack(Rack $rack)
    {
        $this->qb
            ->join('pos.shelf', 'shelf')
            ->andWhere('shelf.rack = :rack')
            ->setParameter('rack', $rack);
        return $this;
    }

    public function isShelved()
    {
        $this->qb->andWhere('pos.id is not null');
        return $this;
    }

    public function isNotShelved()
    {
        $this->qb->andWhere('pos.id is null');
        return $this;
    }

    public function byItem(Item $item)
    {
        $this->qb->andWhere('bin.stockItem = :stockCode')
            ->setParameter('stockCode', $item->getSku());
        return $this;
    }

    public function bySku($sku)
    {
        $sku = str_replace('*', '%', $sku);
        $this->qb
            ->andWhere('item.stockCode like :sku')
            ->setParameter('sku', $sku);
        return $this;
    }

    public function byId($bin)
    {
        $binId = str_replace('*', '%', $bin);
        $this->qb
            ->andWhere('bin.id = :binId')
            ->setParameter('binId', $binId);
        return $this;
    }

    public function byFacility(Facility $facility)
    {
        $this->qb->andWhere('bin.facility = :location')
             ->setParameter('location', $facility->getId());

        return $this;
    }

    public function byBinStyles($styles)
    {
        if (count($styles) > 0) {
            $this->qb->andWhere('bin.binStyle in (:styles)')
                ->setParameter('styles', $styles);
        }
        return $this;
    }

    public function byVersionedItem(VersionedItem $item)
    {
        $this->byItem($item);

        $version = $item->getVersion();
        if ($version->isSpecified()) {
            $this->qb->andWhere('bin.version = :version')
                ->setParameter('version', $version->getVersionCode());
        }

        $cmz = $item->getCustomization();
        if ($cmz) {
            $this->qb->andWhere('bin.customization = :cmzId')
                ->setParameter('cmzId', $cmz->getId());
        } else {
            $this->qb->andWhere('bin.customization is null');
        }
        return $this;
    }

    public function byRequirement(VersionedItem $requirement)
    {
        $this->byVersionedItem($requirement);
        $this->qb->leftJoin('bin.facility', 'facility')
            ->leftJoin('bin.transfer', 'transfer')
            ->leftJoin('bin.allocations', 'alloc')
            ->leftJoin('alloc.requirement', 'competitor')
            ->addSelect(['facility', 'transfer', 'alloc', 'competitor'])
            ->orderBy('facility.name', 'asc')
            ->addOrderBy('bin.id', 'asc');
        return $this;
    }

    public function byRequirementLocation(RequirementInterface $requirement)
    {
        $this->qb->andWhere('bin.facility in (:facilities)')
            ->setParameter('facilities', [
                $requirement->getFacility(),
                Facility::HEADQUARTERS_ID
            ]);
        return $this;
    }

    public function byManufacturerCode($mpn)
    {
        $this->qb
            ->join(PurchasingData::class, 'purchData', Join::WITH,
                'purchData.stockItem = bin.stockItem')
            ->andWhere("REPLACE(purchData.manufacturerCode, '-', '') like :mpn")
            ->setParameter('mpn', str_replace('-', '', $mpn));

        return $this;
    }

    public function byVelocity(Velocity $velocity)
    {
        $range = $velocity->getDateRange();
        $this->qb
            ->join(StockBin::class, 'movedBin', 'WITH',
                'movedBin.stockItem = bin.stockItem')
            ->leftJoin(StockMove::class, 'move', 'WITH',
                'move.stockBin = movedBin and move.facility = bin.facility')
            ->groupBy('bin.id');
        if ($range->hasStart()) {
            $this->qb->andHaving('max(move.date) >= :start');
            $this->qb->setParameter('start', $range->getStart());
        }
        if ($range->hasEnd()) {
            /* Really low velocity items don't have any stock moves. */
            $this->qb->andHaving('(max(move.date) < :end or count(move.id) = 0)');
            $this->qb->setParameter('end', $range->getEnd());
        }
        return $this;
    }

    public function orderBySku()
    {
        $this->qb->addOrderBy('item.stockCode', 'asc');
        return $this;
    }

    public function orderByLocation()
    {
        $this->qb->leftJoin('bin.facility', 'facility')
            ->addOrderBy('facility.name', 'asc');
        return $this;
    }

    public function orderById()
    {
        $this->qb->addOrderBy('bin.id', 'asc');
        return $this;
    }
}
