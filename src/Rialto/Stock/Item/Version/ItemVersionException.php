<?php

namespace Rialto\Stock\Item\Version;

/**
 * For problems with a specific, existing version of a stock item.
 */
class ItemVersionException extends VersionException
{
    /** @var ItemVersion */
    private $version;

    public function __construct(ItemVersion $version, string $message)
    {
        parent::__construct($version, $message);
        $this->version = $version;
    }

    public function getVersionCode(): string
    {
        return $this->version->getVersionCode();
    }
}
