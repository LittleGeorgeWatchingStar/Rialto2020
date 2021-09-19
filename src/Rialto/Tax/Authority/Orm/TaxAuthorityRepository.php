<?php

namespace Rialto\Tax\Authority\Orm;

use Rialto\Database\Orm\FilteringRepositoryAbstract;

class TaxAuthorityRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('a');
        return $builder->buildQuery($params);
    }
}
