<?php

namespace Rialto\Accounting\Bank\Statement\Orm;

use Rialto\Database\Orm\FilteringRepositoryAbstract;

class BankStatementPatternRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('p');
        return $builder->buildQuery($params);
    }

    public function findAll()
    {
        $qb = $this->createQueryBuilder('pattern')
            ->orderBy('pattern.sortOrder', 'asc');
        return $qb->getQuery()->getResult();
    }
}
