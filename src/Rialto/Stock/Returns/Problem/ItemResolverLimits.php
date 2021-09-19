<?php

namespace Rialto\Stock\Returns\Problem;

use Rialto\Stock\Returns\ReturnedItem;
use Rialto\Stock\Transfer\Transfer;

/**
 * Puts limits on what sorts of returned item problems can be resolved.
 */
interface ItemResolverLimits
{
    /**
     * @return bool
     */
    public function canBeAdjusted(ReturnedItem $item);

    /**
     * @return bool
     */
    public function canBeReceived(ReturnedItem $item, Transfer $transfer);
}
