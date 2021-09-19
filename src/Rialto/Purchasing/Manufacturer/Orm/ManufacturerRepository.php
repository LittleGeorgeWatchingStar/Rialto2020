<?php

namespace Rialto\Purchasing\Manufacturer\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Manufacturer\Manufacturer;

class ManufacturerRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('mf');
        $builder->add('name', function(QueryBuilder $qb, $name) {
            $qb->andWhere('mf.name like :name')
                ->setParameter('name', $name);
        });

        $builder->add('matching', function(QueryBuilder $qb, $pattern) {
            $qb->andWhere('mf.name like :pattern')
                ->setParameter('pattern', "%$pattern%");
        });

        $builder->add('_order', function(QueryBuilder $qb, $orderBy) {
            switch ($orderBy) {
                default:
                    $qb->orderBy('mf.name');
                    break;
            }
        });

        return $builder->buildQuery($params);
    }

    /**
     * @return Manufacturer|null
     */
    public function findByName($name)
    {
        $name = trim($name);
        if (!$name) {
            throw new \InvalidArgumentException("Argument 'name' is required");
        }
        $name = preg_replace('/\W/', '%', $name);
        $qb = $this->createQueryBuilder('man');
        $qb->where('man.name like :name')
            ->setParameter('name', "$name%")
            ->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return Manufacturer
     */
    public function findByNameOrCreate($name)
    {
        $manufacturer = $this->findByName($name);
        if (!$manufacturer) {
            $manufacturer = new Manufacturer();
            $manufacturer->setName($name);
            $this->_em->persist($manufacturer);
        }
        return $manufacturer;
    }

    /**
     * @return bool True if $man is in use and cannot be deleted.
     */
    public function isInUse(Manufacturer $man)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('count(pd.id)')
            ->from(PurchasingData::class, 'pd')
            ->where('pd.manufacturer = :man')
            ->setParameter('man', $man);
        $count = $qb->getQuery()->getSingleScalarResult();
        return $count > 0;
    }
}
