<?php

namespace Rialto\Stock\Count\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;

class StockCountRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('sc');

        $builder->add('counted', function (QueryBuilder $qb, $counted) {
            if ('yes' == $counted) {
                $qb->join('sc.binCounts', 'counted')
                    ->andWhere('counted.reportedQty is not null');
            } elseif ('no' == $counted) {
                $qb->leftJoin('sc.binCounts', 'counted', Join::WITH,
                        'counted.reportedQty is not null')
                    ->andWhere('counted.id is null');
            }
        });

        $builder->add('approved', function (QueryBuilder $qb, $approved) {
            if ('yes' == $approved) {
                $qb->join('sc.binCounts', 'accepted')
                    ->andWhere('accepted.acceptedQty is not null');
            } elseif ('no' == $approved) {
                $qb->leftJoin('sc.binCounts', 'accepted', Join::WITH,
                        'accepted.acceptedQty is not null')
                    ->andWhere('accepted.id is null');
            }
        });

        $query = $builder->buildQuery($params);
        return $query;
    }

}
