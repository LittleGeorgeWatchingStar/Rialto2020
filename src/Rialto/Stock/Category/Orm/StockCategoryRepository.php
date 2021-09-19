<?php

namespace Rialto\Stock\Category\Orm;

use Rialto\Database\Orm\FilteringRepositoryAbstract;

class StockCategoryRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('c');
        return $builder->buildQuery($params);
    }
}
