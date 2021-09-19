<?php

namespace Rialto\Email;

/**
 * Exception for errors when sending emails.
 */
class EmailException extends \RuntimeException
{
    public function __construct($message, $code, $previous)
    {
        $message = "Error sending email: $message";
        parent::__construct($message, $code, $previous);
    }

}
