<?php

namespace Rialto\Stock\Cost\Orm;

use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Stock\Cost\StandardCost;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;

class StandardCostRepository extends FilteringRepositoryAbstract
{
    public function findByFilters(array $params)
    {
        $query = $this->queryByFilters($params);
        return $query->getResult();
    }

    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('cost');
        $builder->join('cost.stockItem', 'item');
        $builder->add('mbFlag', function($qb, $mbFlag) {
            switch ( $mbFlag ) {
            case StockItem::MANUFACTURED:
                $qb->andWhere('item instance of Rialto\Stock\Item\ManufacturedStockItem');
                break;
            case StockItem::PURCHASED:
                $qb->andWhere('item instance of Rialto\Stock\Item\PurchasedStockItem');
                break;
            }
        });
        $builder->add('current', function($qb, $current) {
            if ( $current == 'yes' ) {
                $qb->leftJoin(StandardCost::class, 'next', 'WITH',
                        'next.stockItem = cost.stockItem and next.startDate > cost.startDate')
                    ->andWhere('next.id is null');
            }
        });
        $builder->add('discontinued', function ($qb, $disc) {
            if ( $disc == 'yes' ) {
                $qb->andWhere('item.discontinued > 0');
            }
            elseif ( $disc != 'any' ) {
                $qb->andWhere('item.discontinued = 0');
            }
        });
        $builder->add('matching', function($qb, $substring) {
            $pattern = "%$substring%";
            $qb->andWhere('item.stockCode like :pattern or item.description like :pattern')
                ->setParameter('pattern', $pattern);
        });

        /* Exclude discontinued items by default */
        if (! isset($params['discontinued']) ) {
            $params['discontinued'] = 'no';
        }

        return $builder->buildQuery($params);
    }

    /** @return StandardCost[] */
    public function findAll()
    {
        $qb = $this->createQueryBuilder('cost');
        $qb->join('cost.stockItem', 'item')
            ->orderBy('item.stockCode')
            ->addOrderBy('cost.startDate')
            ->addOrderBy('cost.id');
        $query = $qb->getQuery();
        return $query->getResult();
    }

    /** @return StandardCost|null */
    public function findCurrentByItem(Item $item)
    {
        $qb = $this->createQueryBuilder('cost');
        $qb->where('cost.stockItem = :item')
            ->setParameter('item', $item->getSku())
            ->orderBy('cost.startDate', 'desc')
            ->setMaxResults(1);
        $query = $qb->getQuery();
        return $query->getOneOrNullResult();
    }

    /** @return StandardCost[] */
    public function findAllByItem(Item $item)
    {
        $qb = $this->createQueryBuilder('cost');
        $qb->where('cost.stockItem = :item')
            ->setParameter('item', $item->getSku())
            ->orderBy('cost.startDate', 'asc');
        $query = $qb->getQuery();
        return $query->getResult();
    }

    /** @return StandardCost|null */
    public function findByItemAndDate(Item $item, \DateTime $date)
    {
        $qb = $this->createQueryBuilder('cost');
        $qb->where('cost.stockItem = :item')
            ->setParameter('item', $item->getSku())
            ->andWhere('cost.startDate <= :date')
            ->setParameter('date', $date)
            ->orderBy('cost.startDate', 'desc')
            ->addOrderBy('cost.id', 'desc')
            ->setMaxResults(1);
        $query = $qb->getQuery();
        return $query->getOneOrNullResult();
    }
}
