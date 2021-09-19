<?php

namespace Rialto\Stock\Item\Version\Web;

use Rialto\Stock\Item\Version\VersionedItemSummary;

class ItemVersionSummary extends VersionedItemSummary
{
    public function getId()
    {
        return $this->getFullSku();
    }
}
