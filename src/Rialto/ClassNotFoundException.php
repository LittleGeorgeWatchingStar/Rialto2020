<?php

namespace Rialto;

/**
 * Throw this when a required class is not defined.
 */
class ClassNotFoundException
extends \LogicException
{
    private $className;

    public function __construct($className)
    {
        $this->className = $className;
        parent::__construct("Class $className does not exist");
    }
}
