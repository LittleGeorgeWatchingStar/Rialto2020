<?php

namespace Rialto\Tax\Regime\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Tax\Regime\TaxRegime;

class TaxRegimeRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('regime');
        $builder->add('county', function (QueryBuilder $qb, $county) {
            $qb->andWhere('regime.county like :county')
                ->setParameter('county', "%$county%");
        });
        $builder->add('city', function (QueryBuilder $qb, $city) {
            $qb->andWhere('regime.city like :city')
                ->setParameter('city', "%$city%");
        });
        $builder->add('startDate', function (QueryBuilder $qb, $dateString) {
            $qb->andWhere('regime.startDate = :startDate')
                ->setParameter('startDate', $dateString);
        });

        $builder->add('_order', function (QueryBuilder $qb, $orderBy) {
            switch ($orderBy) {
                default:
                    $qb->orderBy('regime.county', 'asc');
                    $qb->addOrderBy('regime.city', 'asc');
                    $qb->addOrderBy('regime.acronym', 'asc');
                    $qb->addOrderBy('regime.startDate', 'desc');
                    break;
            }
        });

        $params['_limit'] = null; // TODO: Why?
        return $builder->buildQuery($params);
    }

    /**
     * @return TaxRegime[]
     */
    public function findMatches($county, $city, \DateTime $startDate = null)
    {
        $qb = $this->createQueryBuilder('regime');
        $qb->where("regime.county like :county")
            ->setParameter('county', $county)
            ->andWhere("regime.city like :city")
            ->setParameter('city', $city);
        if (null !== $startDate) {
            $qb->andWhere('regime.startDate = :date')
                ->setParameter('date', $startDate->format('Y-m-d'));
        }

        $query = $qb->getQuery();
        return $query->getResult();
    }

    /** @return TaxRegime[] */
    public function findByCountyAndCity($county, $city, \DateTime $date = null)
    {
        $qb = $this->queryByCountyAndCity($county, $city, $date);
        $qb->orderBy('regime.county', 'asc')
            ->addOrderBy('regime.city', 'asc');
        return $qb->getQuery()->getResult();
    }

    /** @return float */
    public function findTotalByCountyAndCity($county, $city, \DateTime $date = null)
    {
        $qb = $this->queryByCountyAndCity($county, $city, $date);
        $qb->select('sum(regime.taxRate)');
        $query = $qb->getQuery();
        return (float) $query->getSingleScalarResult();
    }

    /** @return QueryBuilder */
    private function queryByCountyAndCity($county, $city, \DateTime $date = null)
    {
        $county = strtolower($county);
        $city = strtolower($city);
        if (null === $date) $date = new \DateTime();
        $date->setTime(0, 0, 0);

        $qb = $this->createQueryBuilder('regime');
        $qb->where("regime.county in ('', :county)")
            ->setParameter('county', $county)
            ->andWhere("regime.city in ('', :city)")
            ->setParameter('city', $city)
            ->andWhere('regime.startDate <= :date')
            ->setParameter('date', $date)
            ->andWhere('(regime.endDate is null or regime.endDate >= :date)');
        return $qb;
    }

}
