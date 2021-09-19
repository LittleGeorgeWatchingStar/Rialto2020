<?php

namespace Rialto\Allocation\Source;

use Rialto\Manufacturing\Customization\Customization;
use Rialto\Stock\VersionedItem;

/**
 * Checks to see that a StockSource is compatible with a versioned item
 * for allocation purposes.
 *
 * @see StockSource
 */
class CompatibilityChecker
{
    /**
     * Returns true if the two items are compatible for allocation purposes.
     *
     * Note that compatibility does not guarantee that allocations can
     * be created. The source might be compatible but have no available
     * stock left.
     *
     * @return boolean
     */
    public static function areCompatible(VersionedItem $first, VersionedItem $second)
    {
        $version = $second->getVersion();
        if (! $version->matches($first->getVersion())) {
            return false;
        }

        $theirs = $second->getCustomization();
        $mine = $first->getCustomization();
        return Customization::areEqual($theirs, $mine);
    }
}
