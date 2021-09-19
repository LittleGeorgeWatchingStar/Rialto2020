<?php

namespace Rialto\Allocation\Allocation;

use Rialto\Exception\InvalidDataException;


/**
 * Throws when an invalid stock allocation is detected.
 */
class InvalidAllocationException extends InvalidDataException
{
    /** @var StockAllocation */
    private $allocation;

    public function __construct(StockAllocation $alloc, $messages = [])
    {
        $this->allocation = $alloc;

        $msg = sprintf('Allocation %s is invalid: %s',
            $alloc->getId(),
            join('; ', $messages));
        parent::__construct($msg);
    }

    public function getAllocation()
    {
        return $this->allocation;
    }
}
