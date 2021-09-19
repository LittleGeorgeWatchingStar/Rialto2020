<?php

namespace Rialto\Stock\Facility\Orm;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Facility\Facility;

class FacilityRepository extends FilteringRepositoryAbstract
{
    /** @return Query */
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('loc');
        $builder->add('active', function (QueryBuilder $qb, $active) {
            if ($active == 'yes') {
                $qb->andWhere('loc.active = 1');
            } elseif ($active == 'no') {
                $qb->andWhere('loc.active = 0');
            }
        });
        $builder->add('facility', function (QueryBuilder $qb, $facility) {
            $qb->andWhere('loc.id = :facility')
                ->setParameter('facility', $facility);
        });
        return $builder->buildQuery($params);
    }

    /**
     * @return Facility|object
     */
    public function getHeadquarters()
    {
        return $this->find(Facility::HEADQUARTERS_ID);
    }

    /** @return Facility[] */
    public function findActive()
    {
        return $this->findBy(
            ['active' => 1],
            ['name' => 'ASC']
        );
    }

    /** @return QueryBuilder */
    public function queryActive()
    {
        $qb = $this->createQueryBuilder('l');
        $qb->andWhere('l.active = 1')
            ->orderBy('l.name', 'asc');
        return $qb;
    }

    /** @return QueryBuilder */
    public function queryActiveManufacturers()
    {
        $qb = $this->queryActive();
        $qb->andWhere('l.id not in (:ids)')
            ->setParameter('ids', [
                Facility::HEADQUARTERS_ID,
                Facility::TESTING_ID,
            ])
            ->join('l.supplier', 's');
        return $qb;
    }

    /** @return Facility[] */
    public function findValidDestinations()
    {
        $qb = $this->queryValidDestinations();
        return $qb->getQuery()->getResult();
    }

    /** @return QueryBuilder */
    public function queryValidDestinations()
    {
        $qb = $this->queryActive();
        $qb->andWhere('l.address is not null');
        return $qb;
    }

    public function findBySupplier(Supplier $supp)
    {
        return $this->findOneBy([
            'supplier' => $supp->getId()
        ]);
    }
}
