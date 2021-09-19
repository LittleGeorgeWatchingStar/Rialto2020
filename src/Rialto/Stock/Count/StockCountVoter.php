<?php

namespace Rialto\Stock\Count;

use Rialto\Security\Role\Role;
use Rialto\Security\Role\RoleBasedVoter;
use Rialto\Security\User\User;

/**
 * Determines whether the current user is allowed to perform
 * operations on a StockCount.
 *
 * @see StockCount
 */
class StockCountVoter extends RoleBasedVoter
{
    /**
     * Can the user enter a stock count against this record?
     */
    const ENTRY = 'entry';

    protected function getSupportedAttributes()
    {
        return [self::ENTRY];
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass.
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return [StockCount::class];
    }

    /**
     * @param StockCount $count
     * @param string $attribute
     * @return bool
     */
    protected function isGranted($attribute, $count, $user = null)
    {
        assertion($count instanceof StockCount);

        if ( $this->hasRole(Role::STOCK, $user) ) {
            return true;
        }
        if (! $this->hasRole(Role::SUPPLIER_ADVANCED, $user) ) {
            return false;
        }
        $supplier = $count->getSupplier();
        if (! $supplier ) {
            return false;
        }
        if (! $user instanceof User) {
            return false;
        }
        return $supplier->equals($user->getSupplier());
    }

}
