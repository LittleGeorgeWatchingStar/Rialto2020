<?php

namespace Rialto;

/**
 * Thrown when an unexpected class or type is received.
 */
class UnexpectedClassException extends \InvalidArgumentException
{
    public function __construct($object, $message = "unexpected class")
    {
        $class = is_object($object) ? get_class($object) : gettype($object);
        $message = "$message: $class";
        parent::__construct($message);
    }
}
