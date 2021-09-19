<?php

namespace Rialto\Security\User;


use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UnexpectedValueException;

/**
 * The default implemention of @see UserManager.
 */
class SymfonyUserManager implements UserManager
{
    /** @var TokenStorageInterface */
    private $tokens;

    /** @var AuthorizationCheckerInterface */
    private $auth;

    public function __construct(TokenStorageInterface $tokens,
                                AuthorizationCheckerInterface $auth)
    {
        $this->tokens = $tokens;
        $this->auth = $auth;
    }

    public function getUser(): User
    {
        $user = $this->getUserOrNull();
        if ($user) {
            return $user;
        }
        throw new UnexpectedValueException("There is no current user");
    }

    /**
     * @return User|null
     */
    public function getUserOrNull()
    {
        $token = $this->tokens->getToken();
        return $token ? $token->getUser() : null;
    }

    public function isGranted($attributes, $object = null): bool
    {
        return $this->auth->isGranted($attributes, $object);
    }

}
