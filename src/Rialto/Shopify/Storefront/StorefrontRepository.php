<?php

namespace Rialto\Shopify\Storefront;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Security\User\User;


class StorefrontRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('sf');
        return $builder->buildQuery($params);
    }

    /**
     * @throws UnexpectedResultException If $user does not represent
     *  a Shopify storefront.
     */
    public function findByUser(User $user): Storefront
    {
        $qb = $this->queryByUser($user);
        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @return Storefront|null
     */
    public function findByUserIfExists(User $user)
    {
        $qb = $this->queryByUser($user);
        return $qb->getQuery()->getOneOrNullResult();
    }

    private function queryByUser(User $user): QueryBuilder
    {
        $qb = $this->createQueryBuilder('sf');
        $qb->where('sf.user = :user')
            ->setParameter('user', $user);
        return $qb;
    }
}
