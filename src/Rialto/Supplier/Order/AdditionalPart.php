<?php

namespace Rialto\Supplier\Order;

use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A request from the supplier to add an additional part to a work order.
 */
class AdditionalPart extends Event implements Item
{
    /** @var WorkOrder */
    private $workOrder;

    /**
     * @var StockItem
     * @Assert\NotNull(message="Please enter a valid stock code.")
     */
    private $component;

    /**
     * @var number
     * @Assert\Range(min=0)
     */
    private $unitQty = 0;

    /**
     * @var WorkType
     * @Assert\NotNull(message="Work type is required.")
     */
    private $workType;

    /**
     * @var number
     * @Assert\Range(min=0)
     */
    private $scrapCount;

    /**
     * @var string
     * @Assert\NotBlank(message="Please provide a reason.")
     */
    private $reason;

    public function __construct(WorkOrder $workOrder, WorkType $defaultType)
    {
        $this->workOrder = $workOrder;
        $this->workType = $defaultType;
    }

    /**
     * @return WorkOrder
     */
    public function getWorkOrder()
    {
        return $this->workOrder;
    }

    public function getComponent()
    {
        return $this->component;
    }

    public function setComponent(StockItem $stockItem)
    {
        $this->component = $stockItem;
    }

    public function getSku()
    {
        return $this->component->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function __toString()
    {
        return $this->getSku();
    }

    public function getUnitQty()
    {
        return $this->unitQty;
    }

    public function setUnitQty($unitQty)
    {
        $this->unitQty = (int) $unitQty;
    }

    /**
     * @return WorkType
     */
    public function getWorkType()
    {
        return $this->workType;
    }

    public function setWorkType(WorkType $workType)
    {
        $this->workType = $workType;
    }

    public function getScrapCount()
    {
        return $this->scrapCount;
    }

    public function setScrapCount($scrapCount)
    {
        $this->scrapCount = (int) $scrapCount;
    }

    /** @Assert\Callback */
    public function validateQuantityIsNotZero(ExecutionContextInterface $context)
    {
        if ( $this->unitQty + $this->scrapCount == 0 ) {
            $context->buildViolation('Please enter a quantity')
            ->atPath('scrapCount')
            ->addViolation();
        }
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function setReason($reason)
    {
        $this->reason = trim($reason);
    }

    /** @return Requirement */
    public function updateWorkOrder()
    {
        if ( $this->workOrder->hasRequirement($this->component) ) {
            $requirement = $this->workOrder->getRequirement($this->component);
            $requirement->addUnitQty((int) $this->unitQty);
        } else {
            $requirement = $this->workOrder->createRequirement(
                $this->component,
                (int) $this->unitQty,
                $this->workType);
        }
        $requirement->addScrapCount($this->scrapCount);
        return $requirement;
    }
}
