<?php

namespace Rialto\Stock\Item\Version;

use Rialto\Stock\Item;

/**
 * Thrown when a specified version is needed but not found.
 */
class UnspecifiedVersionException extends VersionException
{
    public function __construct(Item $item, $message = 'has no specified version')
    {
        parent::__construct($item, $message);
    }
}
