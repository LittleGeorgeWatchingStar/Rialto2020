<?php

namespace Rialto\Ups;

/**
 * Stores UPS account credentials.
 */
class UpsAccount
{
    private $accessLicense;
    private $userId;
    private $password;

    public function __construct($accessLicense, $userId, $password)
    {
        $this->accessLicense = $accessLicense;
        $this->userId = $userId;
        $this->password = $password;
    }

    public function getAccessLicense()
    {
        return $this->accessLicense;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getPassword()
    {
        return $this->password;
    }
}
