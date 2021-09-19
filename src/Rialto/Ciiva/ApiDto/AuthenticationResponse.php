<?php


namespace Rialto\Ciiva\ApiDto;


final class AuthenticationResponse
{
    /** @var string */
    private $sessionId;

    public function __construct(string $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}
