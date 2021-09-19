<?php

namespace Rialto\Purchasing\Catalog\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Purchasing\Catalog\PurchasingData;

class CostBreakRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('cost');

        $builder->add('purchasingData', function (QueryBuilder $qb, $purchDataId) {
            $qb->andWhere('cost.purchasingData = :purchDataId')
                ->setParameter('purchDataId', $purchDataId);
        });

        $builder->add('orderQty', function (QueryBuilder $qb, $orderQty) {
            $qb->andWhere(':orderQty >= cost.minimumOrderQty')
                ->setParameter('orderQty', $orderQty);
        });

        $builder->add('neededBy', function (QueryBuilder $qb, $neededBy) {
            $neededBy = new \DateTime($neededBy);
            $today = new \DateTime();
            $today->setTime(0, 0, 0);
            $diff = $neededBy->diff($today);
            $leadTime = $diff->days;
            // TODO: manufacturerLeadTime provides a worst-case estimate
            $qb->andWhere('cost.manufacturerLeadTime <= :leadTime')
                ->setParameter('leadTime', $leadTime);
        });

        $builder->add('_order', function (QueryBuilder $qb, $orderBy) {
            switch ($orderBy) {
                default:
                    $qb->orderBy('cost.unitCost', 'asc');
                    break;
            }
        });

        return $builder->buildQuery($params);
    }

    public function findByPurchasingData(PurchasingData $purchData)
    {
        return $this->findBy(
            ['purchasingData' => $purchData->getId()],
            ['cost' => 'ASC']
        );
    }

    public function deleteByPurchasingData(PurchasingData $purchData)
    {
        foreach ($this->findByPurchasingData($purchData) as $costBreak) {
            $this->_em->remove($costBreak);
        }
    }
}
