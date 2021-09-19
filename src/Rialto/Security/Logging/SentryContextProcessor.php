<?php

namespace Rialto\Security\Logging;

use Rialto\Security\User\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Formats log messages for Sentry, our error logging service.
 */
class SentryContextProcessor
{
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function processRecord($record)
    {
        $record['context']['user'] = $this->getUserInfo();
        return $record;
    }

    private function getUserInfo()
    {
        $token = $this->tokenStorage->getToken();
        $info = [];

        if ($token !== null) {
            $user = $token->getUser();
            if ($user instanceof UserInterface) {
                $info = [
                    'username' => $user->getUsername(),
                ];
            }
            if ($user instanceof User) {
                $info['name'] = $user->getName();
                $info['email'] = $user->getEmail();
            }
        }
        return $info;
    }
}
