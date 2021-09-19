<?php

namespace Rialto\Manufacturing\Allocation\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Manufacturing\Allocation\AllocationConfiguration;

/**
 * Database mapper for AllocationConfiguration class.
 */
class AllocationConfigurationRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('allocConfig');

        $builder->add('id', function(QueryBuilder $qb, $allocConfigId) {
            $qb->where('allocConfig.id = :$allocConfigId');
            $qb->setParameter('$allocConfigId', $allocConfigId);
            return true;
        });

        $builder->add('type', function(QueryBuilder $qb, $allocConfigType) {
            $qb->andWhere('allocConfig.type = :allocConfigType');
            $qb->setParameter('allocConfigType', $allocConfigType);
        });

        $builder->add('disabled', function (QueryBuilder $qb, $disabled) {
            if ( $disabled === 'yes' ) {
                $qb->andWhere('item.discontinued > 0');
            } elseif ( $disabled === 'no' ) {
                $qb->andWhere('item.discontinued = 0');
            }
        });

        return $builder->buildQuery($params);
    }

    /** @return AllocationConfiguration[] */
    public function findAllNotDisabled()
    {
        $qb = $this->createQueryBuilder('ac')
            ->where('ac.disabled > 0 ');
        $query = $qb->getQuery();
        return $query->getResult();
    }
}
