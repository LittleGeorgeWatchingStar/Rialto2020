<?php

namespace Rialto\Sales\Returns;

use Symfony\Component\EventDispatcher\Event;

class SalesReturnEvent extends Event
{
    private $rma;

    public function __construct(SalesReturn $rma)
    {
        $this->rma = $rma;
    }

    /** @return SalesReturn */
    public function getSalesReturn()
    {
        return $this->rma;
    }
}
