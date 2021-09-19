<?php

namespace Rialto\Security\Firewall;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Security\User\Orm\UserRepository;
use Rialto\Security\User\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Base UserProvider implementation for Rialto.
 */
abstract class UserProvider implements UserProviderInterface
{
    /** @var UserRepository */
    protected $repo;

    public function __construct(ObjectManager $om)
    {
        $this->repo = $om->getRepository(User::class);
    }

    public function loadUserByUsername($username)
    {
        $user = $this->findUserIfExists($username);
        if ($user) {
            return $user;
        }
        throw new UsernameNotFoundException("No user exists '$username'");
    }

    /** @return User|null */
    protected abstract function findUserIfExists(string $username);

    public function refreshUser(UserInterface $user)
    {
        return $this->repo->find($user->getUsername());
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}
