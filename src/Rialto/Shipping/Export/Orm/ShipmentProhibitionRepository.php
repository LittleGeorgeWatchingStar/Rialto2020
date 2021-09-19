<?php

namespace Rialto\Shipping\Export\Orm;

use Doctrine\ORM\QueryBuilder;
use Gumstix\GeographyBundle\Model\Country;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Stock\Item\StockItem;

class ShipmentProhibitionRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('p');

        $builder->add('_order', function(QueryBuilder $qb, $orderBy) {
            switch ($orderBy) {
            default:
                $qb->orderBy('p.prohibitedCountry', 'asc');
                $qb->addOrderBy('p.id', 'asc');
                break;
            }
        });
        return $builder->buildQuery($params);
    }

    public function isProhibited(StockItem $item, Country $country)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select($qb->expr()->count('p.id'))
            ->where('p.prohibitedCountry = :country')
            ->andWhere('p.stockItem = :stockId
                or p.stockCategory = :categoryId
                or p.eccnCode like :eccn')
            ->setParameters([
                'country' => $country->getCode(),
                'stockId' => $item->getSku(),
                'categoryId' => $item->getCategory()->getId(),
                'eccn' => trim($item->getEccnCode()),
            ]);
        $query = $qb->getQuery();
        return $query->getSingleScalarResult();
    }
}
