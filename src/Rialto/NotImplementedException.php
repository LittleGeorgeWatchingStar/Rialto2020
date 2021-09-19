<?php

namespace Rialto;

use LogicException;

/**
 * Thrown when you try to access something that hasn't been implemented yet.
 */
class NotImplementedException
extends LogicException
{
    public function __construct($message = 'Not implemented', $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}