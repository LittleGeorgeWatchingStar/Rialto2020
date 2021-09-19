<?php

namespace Rialto\Magento2\Storefront;

use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;

class StorefrontRepository extends RialtoRepositoryAbstract
{
    /**
     * @return Storefront|null
     */
    public function findByUserIfExists(User $user)
    {
        $qb = $this->queryByUser($user);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /** @return QueryBuilder */
    private function queryByUser(User $user)
    {
        $qb = $this->createQueryBuilder('sf');
        $qb->where('sf.user = :user')
            ->setParameter('user', $user);
        return $qb;
    }

    /** @return Storefront[] */
    public function findByStockLocation(Facility $location)
    {
        return $this->findBy(['shipFromFacility' => $location]);
    }
}
