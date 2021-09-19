<?php

namespace Rialto\Stock\Move\Orm;

use Doctrine\ORM\QueryBuilder;
use Gumstix\Time\DateRange;
use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Sales\Invoice\SalesInvoiceItem;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Count\BinCount;
use Rialto\Stock\Item;
use Rialto\Stock\Move\StockMove;

class StockMoveRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('move');

        $builder->add('item', function(QueryBuilder $qb, $sku) {
            $qb->andWhere('move.stockItem = :exactSku')
                ->setParameter('exactSku', $sku);
        });

        $builder->add('sku', function(QueryBuilder $qb, $sku) {
            $qb->join('move.stockItem', 'item')
                ->andWhere('item.stockCode like :skuPattern')
                ->setParameter('skuPattern', "%$sku%");
        });

        $builder->add('location', function(QueryBuilder $qb, $facility) {
            $qb->andWhere('move.facility = :facility')
                ->setParameter('facility', $facility);
        });

        $builder->add('startDate', function(QueryBuilder $qb, $date) {
            $qb->andWhere('move.date >= :startDate')
                ->setParameter('startDate', $date);
        });

        $builder->add('endDate', function(QueryBuilder $qb, $date) {
            $qb->andWhere('move.date <= :endDate')
                ->setParameter('endDate', $date);
        });

        $builder->add('date', function (QueryBuilder $qb, DateRange $range) {
            if ($range->hasStart()) {
                $qb->andWhere('move.date >= :startDate')
                    ->setParameter('startDate', $range->getStart());
            }
            if ($range->hasEnd()) {
                $qb->andWhere('move.date <= :endDate')
                    ->setParameter('endDate', $range->getEnd());
            }
        });

        $builder->add('bin', function(QueryBuilder $qb, $binID) {
            if (in_array($binID, ['none', 'null'])) {
                $qb->andWhere('move.stockBin is null');
            } else {
                $qb->andWhere('move.stockBin = :binID')
                    ->setParameter('binID', $binID);
            }
        });

        $builder->add('reference', function(QueryBuilder $qb, $pattern) {
            $qb->andWhere('move.reference like :refPattern')
                ->setParameter('refPattern', "%$pattern%");
        });

        $builder->add('showTransit', function (QueryBuilder $qb, $show) {
            if ('yes' != $show) {
                $qb->andWhere('move.facility is not null');
            }
        });

        $builder->add('_order', function(QueryBuilder $qb, $sortBy) {
            switch ($sortBy) {
                default:
                    $qb->orderBy('move.date', 'asc')
                        ->addOrderBy('move.quantity', 'asc')
                        ->addOrderBy('move.id', 'asc');
                    break;
            }
        });

        if (empty($params['showTransit'])) {
            $params['showTransit'] = 'no';
        }

        return $builder->buildQuery($params);
    }

    /** @return StockMove[] */
    public function findByBin(StockBin $bin)
    {
        return $this->findBy(['stockBin' => $bin]);
    }

    /** @return StockMove[] */
    public function findByEvent(AccountingEvent $event)
    {
        return $this->findBySystemType(
            $event->getSystemType(),
            $event->getSystemTypeNumber());
    }

    public function findByType(SystemType $type, $transNo)
    {
        return $this->findBySystemType($type, $transNo);
    }

    /** @return StockMove[] */
    public function findBySystemType(SystemType $type, $transNo)
    {
        return $this->findBy([
            'systemType' => $type->getId(),
            'systemTypeNumber' => $transNo,
        ]);
    }

    /** @return StockMove[] */
    public function findByEventAndItem(AccountingEvent $event, Item $item)
    {
        $sysType = $event->getSystemType();
        return $this->findBy([
            'systemType' => $sysType->getId(),
            'systemTypeNumber' => $event->getSystemTypeNumber(),
            'stockItem' => $item->getSku()
        ]);
    }

    /**
     * @TODO mantis3009
     * @param SalesInvoiceItem $lineItem
     * @return StockMove[]
     */
    public function findByInvoiceLineItem(SalesInvoiceItem $lineItem)
    {
        $invoice = $lineItem->getInvoice();
        return $this->findBy([
            'systemType' => SystemType::SALES_INVOICE,
            'systemTypeNumber' => $invoice->getInvoiceNumber(),
            'stockItem' => $lineItem->getSku()
        ]);
    }

    public function findForBinCount(BinCount $binCount)
    {
        $qb = $this->createQueryBuilder('m');
        $qb->andWhere('m.stockBin = :binID')
            ->setParameter('binID', $binCount->getBin()->getId())
            ->andWhere('m.date >= :requestDate')
            ->setParameter('requestDate', $binCount->getDateRequested())
            ->andWhere('m.date <= CURRENT_TIMESTAMP()')
            ->orderBy('m.date', 'asc');
        return $qb->getQuery()->getResult();
    }
}
