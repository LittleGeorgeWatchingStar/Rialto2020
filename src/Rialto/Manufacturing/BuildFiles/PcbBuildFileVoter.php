<?php

namespace Rialto\Manufacturing\BuildFiles;


use Rialto\Security\Role\Role;
use Rialto\Security\Role\RoleBasedVoter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class PcbBuildFileVoter extends RoleBasedVoter
{
    const VIEW = 'view';

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        parent::__construct($roleHierarchy);
    }

    protected function getSupportedAttributes()
    {
        return [self::VIEW];
    }

    protected function getSupportedClasses()
    {
        return [];
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, $this->getSupportedAttributes())
            && $this->isValidFilename($subject);
    }

    private function isValidFilename($filename): bool
    {
        if (!is_string($filename)) {
            return false;
        }

        return in_array($filename, PcbBuildFiles::getSupplierAccessibleFilenames())
            || in_array($filename, PcbBuildFiles::getInternalFilenames());
    }

    protected function isGranted($attribute, $object, $user = null)
    {
        switch ($attribute) {
            case self::VIEW:
                return $this->canView($object, $user);
            default:
                throw new \InvalidArgumentException("Unsupported attribute $attribute");
        }
    }

    private function canView(string $filename, UserInterface $user = null): bool
    {
        if (in_array($filename, PcbBuildFiles::getSupplierAccessibleFilenames())) {
            return $this->hasRole(Role::SUPPLIER_ADVANCED, $user)
                || $this->hasRole(Role::EMPLOYEE, $user);
        }

        if (in_array($filename, PcbBuildFiles::getInternalFilenames())) {
            return $this->hasRole(Role::EMPLOYEE, $user)
                || $this->hasRole(Role::SUPPLIER_INTERNAL, $user);
        }

        return false;
    }
}
