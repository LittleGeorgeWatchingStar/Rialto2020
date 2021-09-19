<?php

namespace Rialto\Allocation\Requirement;

use Rialto\Stock\Facility\Facility;
use Rialto\Stock\VersionedItem;

/**
 * A collection of Requirements for which to allocate.
 */
interface RequirementCollection extends VersionedItem
{
    /**
     * @return bool
     *  True if these consumers can share bins with other consumers at
     *  the same location. (Stock consumers will never share bins with
     *  consumers from other locations.)
     */
    public function isShareBins();

    /**
     * @return Requirement[] The requirements in this collection.
     */
    public function getRequirements();

    /**
     * @return Facility The location where stock is required.
     */
    public function getFacility();

    /**
     * @deprecated use getFacility() instead
     */
    public function getLocation();
}
