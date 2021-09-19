<?php

namespace Rialto\Email\Mailable;

/**
 * A basic implementation of Mailable.
 */
class GenericMailable implements Mailable
{
    /** @var string */
    private $email;

    /** @var string */
    private $name;

    public function __construct($email, $name)
    {
        $this->email = $email;
        $this->name = $name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name;
    }
}
