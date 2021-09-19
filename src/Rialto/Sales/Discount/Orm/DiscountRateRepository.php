<?php

namespace Rialto\Sales\Discount\Orm;

use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Sales\Discount\DiscountGroup;

class DiscountRateRepository extends RialtoRepositoryAbstract
{
    public function findByGroup(DiscountGroup $group)
    {
        return $this->findBy([
            'discountGroup' => $group->getId()
        ]);
    }

    public function findByGroupAndQtyOrdered(DiscountGroup $group, $qtyOrdered)
    {
        $qb = $this->createQueryBuilder('rate')
            ->where('rate.discountGroup = :group')
            ->andWhere('rate.threshold <= :qty')
            ->orderBy('rate.threshold', 'DESC')
            ->setParameters([
                'group' => $group->getId(),
                'qty' => $qtyOrdered,
            ])
            ->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
