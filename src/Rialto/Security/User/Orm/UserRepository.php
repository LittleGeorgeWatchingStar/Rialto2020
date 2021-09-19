<?php

namespace Rialto\Security\User\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Email\Subscription\UserSubscription;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;

class UserRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('u');

        $builder->add('active', function(QueryBuilder $qb, $active) {
            if ( $active == 'yes' ) {
                $qb->andWhere('size(u.roles) > 0');
            } elseif ( $active == 'no' ) {
                $qb->andWhere('size(u.roles) = 0');
            }
        });

        $builder->add('matching', function(QueryBuilder $qb, $matching) {
            $qb->andWhere('(u.id like :matching or u.name like :matching or u.email like :matching)')
                ->setParameter('matching', "%$matching%");
        });

        $builder->add('role', function(QueryBuilder $qb, $rolePattern) {
            $qb->join('u.roles', 'role')
                ->andWhere('role.name like :role')
                ->setParameter('role', "%$rolePattern%");
        });

        $builder->add('facility', function (QueryBuilder $qb, $facility) {
            $qb->andWhere('u.defaultLocation = :facility')
                ->setParameter('facility', $facility);
        });

        $builder->add('_order', function(QueryBuilder $qb, $sortBy) {
            switch ($sortBy) {
                default:
                    $qb->orderBy('u.name', 'asc');
                    break;
            }
        });

        return $builder->buildQuery($params);
    }

    /** @return User[] */
    public function findByRole($role)
    {
        $qb = $this->queryByRole($role);
        return $qb->getQuery()->getResult();
    }

    /** @return QueryBuilder */
    public function queryByRole($role)
    {
        $qb = $this->createQueryBuilder('u');
        $this->selectByRole($qb, $role);
        return $qb;
    }

    private function selectByRole(QueryBuilder $qb, $role)
    {
        $qb->distinct()
            ->join('u.roles', 'r')
            ->andWhere('r.name = :roleName')
            ->setParameter('roleName', $role);
    }

    /** @return QueryBuilder */
    public function queryMailable()
    {
        $qb = $this->queryActive();
        $qb->andWhere("u.email != ''")
            ->orderBy('u.name');
        return $qb;
    }

    /** @return QueryBuilder */
    public function queryMailableByRole($role)
    {
        $qb = $this->queryMailable();
        $this->selectByRole($qb, $role);
        return $qb;
    }

    /**
     * Users with the given role who have a valid email address.
     * @return User[]
     */
    public function findMailableByRole($role)
    {
        $qb = $this->queryMailableByRole($role);
        return $qb->getQuery()->getResult();
    }

    /** @return User[] */
    public function findByManufacturer(Facility $cm)
    {
        $supplier = $cm->getSupplier();
        return $this->findBy(['supplier' => $supplier->getId()]);
    }

    /** @return User[] */
    public function findBySubscriptionTopic($topic)
    {
        $qb = $this->createQueryBuilder('u')
            ->join(UserSubscription::class, 's', Join::WITH,
                's.user = u')
            ->andWhere('s.topic = :topic')
            ->setParameter('topic', $topic)
            ->andWhere("u.email != ''");
        return $qb->getQuery()->getResult();
    }

    /** @return QueryBuilder */
    public function queryActive()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->andWhere('size(u.roles) > 0');
        $qb->orderBy('u.name', 'asc');
        return $qb;
    }

    /** @return User[] */
    public function findByLocation(Facility $location)
    {
        return $this->findBy(['defaultLocation' => $location]);
    }
}
