<?php

namespace Rialto\Purchasing\Order\Command;


use Rialto\Port\CommandBus\Command;

/**
 * Command representing a request to merge the line items and allocations of
 * two unsent purchase orders from the same vendor.
 *
 * The items from the secondary order will be merged into the primary order.
 */
final class MergePurchaseOrdersCommand implements Command
{
    /** @var string */
    private $primaryId;

    /** @var string */
    private $secondaryId;

    public function __construct(string $primaryId, string $secondaryId)
    {
        $this->primaryId = $primaryId;
        $this->secondaryId = $secondaryId;
    }

    public function getPrimaryId(): string
    {
        return $this->primaryId;
    }

    public function getSecondaryId(): string
    {
        return $this->secondaryId;
    }

}
