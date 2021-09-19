<?php

namespace Rialto\Stock\Item\Version;

use Rialto\Exception\InvalidDataException;
use Rialto\Stock\Item;

/**
 * Thrown when a problem is detected with a version.
 */
class VersionException extends InvalidDataException
{
    /** @var Item */
    private $item;

    public function __construct(Item $item, string $message)
    {
        parent::__construct($item->getSku() . " $message");
        $this->item = $item;
    }

    public function getSku(): string
    {
        return $this->item->getSku();
    }
}
