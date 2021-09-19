<?php

namespace Rialto\Manufacturing\Log;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProductionLogProcessor
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokens)
    {
        $this->tokenStorage = $tokens;
    }

    public function processRecord($record)
    {
        $token = $this->tokenStorage->getToken();
        $record['user'] = $token ? $token->getUsername() : null;
        return $record;
    }
}
