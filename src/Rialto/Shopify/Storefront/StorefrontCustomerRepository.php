<?php

namespace Rialto\Shopify\Storefront;

use Doctrine\ORM\EntityRepository;

/**
 * StorefrontCustomerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StorefrontCustomerRepository extends EntityRepository
{
    /**
     * @return StorefrontRepository|null
     */
    public function findIfExists(Storefront $storefront, $remoteID)
    {
        return $this->findOneBy([
            'storefront' => $storefront,
            'remoteID' => $remoteID,
        ]);
    }
}