<?php

namespace Rialto\Shipping\Shipper\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Shipping\Shipper\Shipper;

class ShipperRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('s');
        return $builder->buildQuery($params);
    }

    /**
     * Case-insensitive.
     */
    public function findByName($name): Shipper
    {
        $qb = $this->createQueryBuilder('shipper');
        $qb->where('shipper.name like :name')
            ->setParameter('name', $name);
        return $qb->getQuery()->getSingleResult();
    }

    public function findHandCarried(): Shipper
    {
        return $this->findByName('hand%carried');
    }

    /**
     * @return Shipper[]
     */
    public function findActive()
    {
        return $this->findBy([
            'active' => 1
        ]);
    }

    /** @return QueryBuilder */
    public function queryActive()
    {
        $qb = $this->createQueryBuilder('s');
        $qb->where('s.active = 1');
        return $qb;
    }

    /**
     * @return Shipper|object|null
     */
    public function findDefault()
    {
        return $this->findOneBy([
            'name' => Shipper::DEFAULT_SHIPPER
        ]);
    }
}
