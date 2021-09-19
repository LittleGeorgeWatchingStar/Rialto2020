<?php

namespace Rialto\Supplier;

use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Security\Role\RoleBasedVoter;
use Rialto\Security\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Decides whether the current user has privileges to view the
 * dashboard for a given supplier.
 */
class SupplierVoter extends RoleBasedVoter
{
    const DASHBOARD = 'dashboard';

    protected function getSupportedAttributes()
    {
        return [self::DASHBOARD];
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass.
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return [Supplier::class];
    }

    /**
     * @param string $attribute
     * @param Supplier $supplier
     * @param UserInterface|null $user
     * @return bool
     */
    protected function isGranted($attribute, $supplier, $user = null)
    {
        assertion($supplier instanceof Supplier);
        if ( $this->hasRole(Role::EMPLOYEE, $user) ) {
            return true;
        }
        if (! $this->hasRole(Role::SUPPLIER_SIMPLE, $user) ) {
            return false;
        }
        if (! $user instanceof User) {
            return false;
        }
        return $supplier->equals($user->getSupplier());
    }
}
