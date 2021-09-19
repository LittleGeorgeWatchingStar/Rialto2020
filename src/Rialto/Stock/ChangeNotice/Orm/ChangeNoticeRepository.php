<?php

namespace Rialto\Stock\ChangeNotice\Orm;

use Rialto\Database\Orm\FilteringRepositoryAbstract;

class ChangeNoticeRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('notice');
        $builder->add('stockItem', function($qb, $stockCode) {
            $qb->join('notice.items', 'ni')
                ->andWhere('ni.stockItem = :stockCode')
                ->setParameter('stockCode', $stockCode);
        });

        $builder->add('description', function($qb, $desc) {
            $qb->andWhere('notice.description like :desc')
                ->setParameter('desc', "%$desc%");
        });

        return $builder->buildQuery($params);
    }
}