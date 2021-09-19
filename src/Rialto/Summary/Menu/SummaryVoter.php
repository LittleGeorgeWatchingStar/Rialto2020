<?php

namespace Rialto\Summary\Menu;


use Rialto\Security\Privilege;
use Rialto\Security\Role\RoleBasedVoter;
use Rialto\Security\User\User;

class SummaryVoter extends RoleBasedVoter
{
    protected function getSupportedAttributes()
    {
        return [Privilege::VIEW];
    }

    protected function getSupportedClasses()
    {
        return [Summary::class];
    }

    protected function isGranted($attribute, $object, $user = null)
    {
        return $this->userCanView($object, $user);
    }

    private function userCanView(Summary $summary, User $user = null)
    {
        foreach ($summary->getAllowedRoles() as $role) {
            if ($this->hasRole($role, $user)) {
                return true;
            }
        }
        return false;
    }

}
