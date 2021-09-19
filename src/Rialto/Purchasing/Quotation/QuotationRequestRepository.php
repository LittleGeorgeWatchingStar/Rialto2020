<?php

namespace Rialto\Purchasing\Quotation;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gumstix\Time\DateRange;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Purchasing\Supplier\Supplier;


class QuotationRequestRepository extends FilteringRepositoryAbstract
{
    /** @return Query */
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('rfq');
        $builder->join('rfq.items', 'rItem');
        $builder->join('rItem.stockItem', 'stockItem');
        $builder->add('ids', function(QueryBuilder $qb, $ids) {
            $qb->andWhere('rfq.id in (:ids)')
                ->setParameter('ids', $ids);
        });
        $builder->add('sku', function(QueryBuilder $qb, $sku) {
            $qb->andWhere('stockItem.stockCode like :sku')
                ->setParameter('sku', "%$sku%");
        });
        $builder->add('sent', function(QueryBuilder $qb, $sent) {
            if ('yes' == $sent) {
                $qb->andWhere('rfq.dateSent is not null');
            } elseif ('no' == $sent) {
                $qb->andWhere('rfq.dateSent is null');
            }
        });
        $builder->add('received', function(QueryBuilder $qb, $received) {
            if ('yes' == $received) {
                $qb->andWhere('rfq.dateReceived is not null');
            } elseif ('no' == $received) {
                $qb->andWhere('rfq.dateReceived is null');
            }
        });

        $builder->add('supplier', function (QueryBuilder $qb, Supplier $supplier) {
            $qb->andWhere('rfq.supplier = :supplier')
                ->setParameter('supplier', $supplier);
        });
        $builder->add('dateSent', function (QueryBuilder $qb, DateRange $dateRange) {
            if ($dateRange->hasStart()) {
                $qb->andWhere('date_diff(rfq.dateSent, :dateOrderedStart) >= 0')
                    ->setParameter('dateOrderedStart', $dateRange->getStart());
            }
            if ($dateRange->hasEnd()) {
                $qb->andWhere('date_diff(rfq.dateSent, :dateOrderedEnd) <= 0')
                    ->setParameter('dateOrderedEnd', $dateRange->getEnd());
            };
        });

        $builder->add('_order', function (QueryBuilder $qb, $orderBy) {
            switch ($orderBy) {
                case 'recent':
                    $qb->orderBy('rfq.dateSent', 'desc');
                    break;
            }
        });

        return $builder->buildQuery($params);
    }
}
