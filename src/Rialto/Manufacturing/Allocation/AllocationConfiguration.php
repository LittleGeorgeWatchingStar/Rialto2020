<?php

namespace Rialto\Manufacturing\Allocation;

use Rialto\Allocation\Requirement\ConsolidatedRequirement;
use Rialto\Manufacturing\Requirement\Validator\VersionIsActive;

/**
 * Controls stock allocation for a work order requirement.
 *
 * @VersionIsActive
 */
class AllocationConfiguration extends ConsolidatedRequirement
{
    const TYPE_WAREHOUSE_STOCK = "Warehouse Stock";
    const TYPE_PO_ITEMS = "Purchase Order Items";
    const TYPE_CONTRACT_MANUFACTURER_STOCK = "Contract Manufacturer Stock";

    /** @var string */
    private $id;

    /** @var string */
    private $type = '';

    /** @var int */
    private $priority = 0;

    /** @var bool */
    private $disabled = false;

    public function __construct(string $type, int $priority, bool $disabled)
    {
        $this->type = $type;
        $this->priority = $priority;
        $this->disabled = $disabled;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /** @param string $newType */
    public function setType($newType)
    {
        $this->type = $newType;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    /** @param int $priority */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /** @param bool $disabled */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }
}
