<?php

namespace Rialto\Stock\Item;

use Rialto\Manufacturing\Bom\Bom;
use Rialto\Stock\Item\Version\Version;

/**
 * A composite stock item is one that is manufactured or assembled out of
 * other items.
 */
interface CompositeStockItem
{
    public function getBom(Version $version): Bom;

    public function bomExists(Version $version): bool;
}
