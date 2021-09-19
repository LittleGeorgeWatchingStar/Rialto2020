<?php

namespace Rialto\Stock\Item\Version;

use Rialto\Stock\VersionedItem;
use Rialto\Web\Serializer\ListableFacade;

class VersionedItemSummary
{
    use ListableFacade;

    /** @var VersionedItem */
    private $item;

    public function __construct(VersionedItem $item)
    {
        $this->item = $item;
    }

    public function getSku(): string
    {
        return $this->item->getSku();
    }

    public function getVersion(): string
    {
        return (string) $this->item->getVersion();
    }

    public function getFullSku(): string
    {
        return $this->item->getFullSku();
    }
}
