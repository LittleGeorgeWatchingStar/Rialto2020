<?php

namespace Rialto\Shipping\Export\Orm;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Shipping\Export\HarmonizationCode;

class HarmonizationCodeRepository extends FilteringRepositoryAbstract
{
    /** @return Query */
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('code');
        $builder->add('matching', function(QueryBuilder $qb, $pattern) {
            $qb->andWhere('code.id like :pattern' .
                'or code.name like :pattern' .
                'or code.description like :pattern')
                ->setParameter('pattern', "%$pattern%");
        });

        return $builder->buildQuery($params);
    }

    /** @return HarmonizationCode[] indexed by code */
    public function fetchIndex()
    {
        $index = [];
        foreach ( $this->findAll() as $code ) {
            /** @var $code HarmonizationCode */
            $index[$code->getId()] = $code;
        }
        return $index;
    }

    /** @return QueryBuilder */
    public function queryActive()
    {
        return $this->createQueryBuilder('code')
            ->andWhere('code.active = 1');
    }
}
