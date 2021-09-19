<?php

namespace Rialto\Sales\Discount\Orm;

use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Sales\Discount\DiscountGroup;
use Rialto\Stock\Item;

class DiscountGroupRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('g');
        return $builder->buildQuery($params);
    }

    /** @return DiscountGroup|null */
    public function findByItem(Item $item)
    {
        $select = $this->createQueryBuilder('g')
            ->where(':item member of g.items');
        $query = $select->getQuery();
        $query->setParameter('item', $item->getSku());
        return $query->getOneOrNullResult();
    }
}
