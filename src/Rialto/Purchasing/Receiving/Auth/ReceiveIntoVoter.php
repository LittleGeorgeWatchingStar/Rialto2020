<?php

namespace Rialto\Purchasing\Receiving\Auth;

use Rialto\Security\Role\Role;
use Rialto\Security\Role\RoleBasedVoter;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;

/**
 * Determines whether the user can receive POs into the given facility.
 *
 * Generally, warehouse staff and CMs should only be able to receive into
 * their default facility to prevent mistakes.
 */
class ReceiveIntoVoter extends RoleBasedVoter
{
    const RECEIVE_INTO = 'receive_into';

    protected function getSupportedAttributes()
    {
        return [self::RECEIVE_INTO];
    }

    protected function getSupportedClasses()
    {
        return [Facility::class];
    }

    /**
     * @param Facility $object
     * @param User|null $user
     * @return bool
     */
    protected function isGranted($attribute, $object, $user = null)
    {
        if ($this->hasRole(Role::PURCHASING, $user)) {
            return true;
        }
        return $user && $object->equals($user->getDefaultLocation());
    }

}
