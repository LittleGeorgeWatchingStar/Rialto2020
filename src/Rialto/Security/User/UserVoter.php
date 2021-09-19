<?php

namespace Rialto\Security\User;


use Rialto\Security\Privilege;
use Rialto\Security\Role\Role;
use Rialto\Security\Role\RoleBasedVoter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends RoleBasedVoter
{
    /**
     * Return an array of supported classes. This will be called by supportsClass.
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return [User::class];
    }

    /**
     * Return an array of supported attributes. This will be called by supportsAttribute.
     *
     * @return array an array of supported attributes, i.e. array('CREATE', 'READ')
     */
    protected function getSupportedAttributes()
    {
        return [Privilege::EDIT];
    }

    /**
     * Perform a single access check operation on a given attribute, object and (optionally) user
     * It is safe to assume that $attribute and $object's class pass supportsAttribute/supportsClass
     * $user can be one of the following:
     *   a UserInterface object (fully authenticated user)
     *   a string               (anonymously authenticated user).
     *
     * @param string $attribute
     * @param User $object
     * @param UserInterface|string $user
     *
     * @return bool
     */
    protected function isGranted($attribute, $object, $user = null)
    {
        return $user && (
            $object->isEqualTo($user)
            || $this->hasRole(Role::ADMIN, $user)
        );
    }

}
