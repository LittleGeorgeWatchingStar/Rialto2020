<?php

namespace Rialto\Allocation\Web;

use Rialto\Alert\BasicAlertMessage;
use Rialto\Allocation\Allocation\InvalidAllocationException;

/**
 * An on-screen alert to let the user know that the system has detected
 * an invalid stock allocation.
 */
class InvalidAllocationAlert extends BasicAlertMessage
{
    /** @return InvalidAllocationAlert|BasicAlertMessage */
    public static function fromInvalidAllocationException(
        InvalidAllocationException $ex )
    {
        $message = $ex->getMessage();
        $message .= '. '; // punctuation
        return static::createError($message);
    }
}
