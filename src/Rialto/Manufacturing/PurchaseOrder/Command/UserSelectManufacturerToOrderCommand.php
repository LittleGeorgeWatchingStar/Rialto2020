<?php


namespace Rialto\Manufacturing\PurchaseOrder\Command;


use Rialto\Port\CommandBus\Command;

final class UserSelectManufacturerToOrderCommand implements Command
{
    /** @var int */
    private $poId;

    /** @var int[]|null */
    private $userSelectionSourcesIds = null;

    public function __construct(int $poId)
    {
        $this->poId = $poId;
    }

    public function getPurchaseOrderId(): string
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
