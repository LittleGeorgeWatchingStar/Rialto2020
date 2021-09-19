<?php

namespace Rialto\Manufacturing\Requirement\Web;

use Rialto\Allocation\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Item\PhysicalStockItem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Template for creating a Requirement.
 *
 * @see Requirement
 */
class CreateRequirement
{
    /** @var WorkOrder */
    private $workOrder;

    public function __construct(WorkOrder $workOrder)
    {
        $this->workOrder = $workOrder;
    }

    /**
     * @var PhysicalStockItem
     * @Assert\NotNull(message="Component is required.")
     */
    public $component;

    /**
     * @var int
     * @Assert\NotBlank
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0.0001, minMessage="Unit quantity must be positive.")
     */
    public $unitQty;

    /**
     * @var int
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0, minMessage="Scrap count cannot be negative.")
     */
    public $scrapCount = 0;

    public $designators = [];

    /**
     * @var WorkType
     * @Assert\NotNull(message="Work type is required.")
     */
    public $workType;

    /** @Assert\Callback */
    public function validatePhysicalItem(ExecutionContextInterface $context)
    {
        if (! $this->component instanceof PhysicalStockItem) {
            $context->buildViolation(
                "{$this->component} is not a physical stock item.")
                ->atPath('component')
                ->addViolation();
        }
    }

    /** @Assert\Callback */
    public function validateNoDuplicates(ExecutionContextInterface $context)
    {
        if (! $this->component) {
            return; // The NotBlank validator will catch this.
        }
        if ($this->workOrder->hasRequirement($this->component)) {
            $context->buildViolation(
                "Work order already requires {$this->component}.")
                ->atPath('component')
                ->addViolation();
        }
    }

    /** @Assert\Callback */
    public function validateDesignators(ExecutionContextInterface $context)
    {
        $numDes = count($this->designators);
        if ($numDes > 0 && ($numDes != $this->unitQty)) {
            $context->buildViolation(
                "Number of designators ($numDes) does not match unit quantity ({$this->unitQty}).")
                ->atPath('designators')
                ->addViolation();
        }
    }
}
