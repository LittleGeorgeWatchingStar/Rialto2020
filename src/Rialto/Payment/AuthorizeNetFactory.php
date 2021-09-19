<?php

namespace Rialto\Payment;

use AuthorizeNetAIM;

/**
 * Decouples our AuthorizeNet class from the official SDK, for easier testing.
 */
class AuthorizeNetFactory
{
    /** @var string */
    private $login;

    /** @var string */
    private $transactionKey;

    public function __construct($login, $transKey)
    {
        $this->login = $login;
        $this->transactionKey = $transKey;
    }

    /** @return AuthorizeNetAIM */
    public function createAIM()
    {
        return new AuthorizeNetAIM($this->login, $this->transactionKey);
    }
}
