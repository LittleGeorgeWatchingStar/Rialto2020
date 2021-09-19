<?php

namespace Rialto\Manufacturing\PurchaseOrder\Command;


use Rialto\Port\CommandBus\Command;

class OrderPartsCommand implements Command
{
    /** @var int */
    private $poId;

    public function __construct(int $poId)
    {
        $this->poId = $poId;
    }

    /** @return int */
    public function getPurchaseOrderId(): int
    {
        return $this->poId;
    }
}
