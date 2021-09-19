<?php

namespace Rialto\Security\Firewall;

/**
 * UserProvider that looks up users by UUID.
 */
class ByUuidProvider extends UserProvider
{
    protected function findUserIfExists(string $uuid)
    {
        $qb = $this->repo->createQueryBuilder('user');
        $qb->join('user.ssoLinks', 'link')
            ->where('link.uuid = :uuid')
            ->setParameter('uuid', $uuid);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
