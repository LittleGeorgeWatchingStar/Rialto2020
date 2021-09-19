<?php

namespace Rialto\Cms\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;

class CmsEntryRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('entry');
        $builder->add('module', function(QueryBuilder $qb, $module) {
            $qb->addWhere('entry.id like :module')
                ->setParameter('module', $module ."%");
        });

        return $builder->buildQuery($params);
    }
}
