<?php

namespace Rialto\Email\Mailable;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Any contact-type object that can receive or send an email.
 */
interface Mailable
{
    /**
     * @return string
     * @Assert\Email(message="Invalid email address.")
     */
    public function getEmail();

    /** @return string */
    public function getName();
}
