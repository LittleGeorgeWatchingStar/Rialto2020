<?php

namespace Rialto\Exception;

/**
 * Exception to throw when an assertion if false.
 */
class AssertionFailedException extends \LogicException
{
    /**
     * Factory method.
     *
     * @return AssertionFailedException
     */
    public static function fromBacktrace(array $backtrace, $depth=0)
    {
        $filename = $backtrace[$depth]['file'];
        $line = $backtrace[$depth]['line'];
        return new self("assertion failed at $filename line $line");
    }
}
