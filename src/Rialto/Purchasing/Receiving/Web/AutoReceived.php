<?php

namespace Rialto\Purchasing\Receiving\Web;


/**
 *
 */
class AutoReceived
extends ItemReceived
{
    public $received = true;

    public function getTotalReceived()
    {
        return $this->received ? $this->poItem->getQtyRemaining() : 0;
    }
}
