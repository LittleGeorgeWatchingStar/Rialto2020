<?php

namespace Rialto\Stock\Returns\Problem;

use Rialto\Stock\Returns\ReturnedItem;
use Rialto\Stock\Transfer\Transfer;

/**
 * Defines the limits of what problems can be automatically resolved
 * when a returned item is received.
 */
class AutoResolveLimits implements ItemResolverLimits
{
    const MAX_STD_COST_DIFF = 100.00; // dollars

    /**
     * Allow adjusting the stock level if the standard cost difference
     * is small enough.
     *
     * @return bool
     */
    public function canBeAdjusted(ReturnedItem $item)
    {
        $diff = abs($item->getStandardCostDifference());
        return $diff <= self::MAX_STD_COST_DIFF;
    }

    /**
     * Allow the transfer to be completed if this is the only bin in it.
     *
     * @return bool
     */
    public function canBeReceived(ReturnedItem $item, Transfer $transfer)
    {
        return (count($transfer->getLineItems()) == 1)
            && $transfer->hasBin($item->getBin());
    }

}
