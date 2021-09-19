<?php


namespace Rialto\Security\Role;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * A Voter that uses the role hierarchy to determine privileges.
 */
abstract class RoleBasedVoter extends Voter
{
    /** @var RoleHierarchyInterface */
    protected $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, $this->getSupportedAttributes())
        && $this->supportsClass($subject);
    }

    protected abstract function getSupportedAttributes();

    public function supportsClass($object)
    {
        foreach ($this->getSupportedClasses() as $class) {
            if ($object instanceof $class) {
                return true;
            }
        }
        return false;
    }

    protected abstract function getSupportedClasses();

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->isGranted($attribute, $subject, $token->getUser());
    }

    protected abstract function isGranted($attribute, $object, $user = null);

    /**
     * @param string $role
     * @return boolean
     */
    protected function hasRole($role, UserInterface $user = null)
    {
        if (null == $user) {
            return false;
        }
        $roles = $this->getReachableRoles($user);
        $result = in_array($role, $roles);
        return $result;
    }

    /** @return string[] */
    private function getReachableRoles(UserInterface $user)
    {
        $roles = $this->roleHierarchy->getReachableRoles($user->getRoles());
        return array_map(function(RoleInterface $role) {
            return $role->getRole(); // convert to string
        }, $roles);
    }
}
