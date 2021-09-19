<?php

namespace Rialto\Security\User;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Allows a many-to-one relationship between SSO users and Rialto users.
 *
 * @UniqueEntity(fields="uuid", message="That UUID is already in use.")
 */
class SsoLink
{
    /**
     * @var string
     * @Assert\NotBlank(message="UUID cannot be blank.")
     */
    private $uuid;

    /**
     * @var User
     * @Assert\NotNull
     */
    private $user;

    public function __construct($uuid, User $user)
    {
        $this->uuid = $uuid;
        $this->user = $user;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function __toString()
    {
        return $this->uuid;
    }

    public function isUuid($uuid)
    {
        return $this->uuid == $uuid;
    }

    public function getUsername()
    {
        return $this->user->getUsername();
    }
}
