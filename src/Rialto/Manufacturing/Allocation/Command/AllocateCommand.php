<?php

namespace Rialto\Manufacturing\Allocation\Command;

use Rialto\Port\CommandBus\Command;

/**
 * Automatically allocate stock to a work order.
 */
class AllocateCommand implements Command
{
    /** @var int */
    private $poId;

    /** @var int[]|null */
    private $userSelectionSourcesIds = null;

    public function __construct(int $poId)
    {
        $this->poId = $poId;
    }

    /** @return int */
    public function getPurchaseOrderId(): int
    {
        return $this->poId;
    }

    /**
     * @param int[] $newArray
     */
    public function setUserSelectionSourcesIds(array $newArray)
    {
        $this->userSelectionSourcesIds = $newArray;
    }

    /** @return int[]|null */
    public function getUserSelectionSourcesIds()
    {
        return $this->userSelectionSourcesIds;
    }
}
