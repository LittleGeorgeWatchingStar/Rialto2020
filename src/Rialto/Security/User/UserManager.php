<?php


namespace Rialto\Security\User;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


/**
 * Hides the repetitive nuisance of dealing with two separate
 * services for auth concerns.
 */
interface UserManager extends AuthorizationCheckerInterface
{
    public function getUser(): User;

    /**
     * @return User|null
     */
    public function getUserOrNull();
}
