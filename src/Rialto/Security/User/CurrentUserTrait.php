<?php

namespace Rialto\Security\User;

use Rialto\IllegalStateException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * For services that need to access the current user.
 */
trait CurrentUserTrait
{
    /** @var TokenStorageInterface */
    private $tokens;

    public function setTokenStorage(TokenStorageInterface $tokens)
    {
        $this->tokens = $tokens;
    }

    /** @return User|null */
    protected function getCurrentUserOrNull()
    {
        $token = $this->tokens->getToken();
        return $token ? $token->getUser() : null;
    }

    /**
     * @return User The current logged-in user
     * @throws IllegalStateException if there is no current user
     */
    protected function getCurrentUser()
    {
        $user = $this->getCurrentUserOrNull();
        if ($user) {
            return $user;
        }
        throw new IllegalStateException("there is no current user");
    }

}
