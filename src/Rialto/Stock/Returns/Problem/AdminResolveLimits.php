<?php

namespace Rialto\Stock\Returns\Problem;

use Rialto\Stock\Returns\ReturnedItem;
use Rialto\Stock\Transfer\Transfer;

/**
 * Administrators can resolve most types of problems with returned bins.
 */
class AdminResolveLimits implements ItemResolverLimits
{
    /**
     * @return bool
     */
    public function canBeAdjusted(ReturnedItem $item)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canBeReceived(ReturnedItem $item, Transfer $transfer)
    {
        return true;
    }
}
