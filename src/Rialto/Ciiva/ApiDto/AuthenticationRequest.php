<?php


namespace Rialto\Ciiva\ApiDto;


use Symfony\Component\Serializer\Annotation\Groups;

/**
 * DTO to request a session ID from Ciiva to use for authentication in all API
 * operations.
 *
 * @see https://api.ciiva.com/api/json/metadata?op=Auth
 */
final class AuthenticationRequest implements RequestDto
{
    /** @var string */
    private $username;

    /** @var string */
    private $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function getEndpoint(): string
    {
        return '/auth/apikey';
    }

    public function responseClass(): string
    {
        return AuthenticationResponse::class;
    }

    /**
     * @Groups("payload")
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @Groups("payload")
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
