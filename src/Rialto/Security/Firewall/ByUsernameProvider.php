<?php

namespace Rialto\Security\Firewall;

/**
 * UserProvider that looks up users by username.
 */
class ByUsernameProvider extends UserProvider
{
    protected function findUserIfExists(string $username)
    {
        return $this->repo->findOneBy(['id' => $username]);
    }
}
