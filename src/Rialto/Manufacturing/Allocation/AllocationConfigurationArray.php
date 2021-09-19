<?php

namespace Rialto\Manufacturing\Allocation;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Allocation\InvalidAllocationException;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Requirement\ConsolidatedRequirement;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Source\SourceCollection;
use Rialto\Allocation\Status\AllocationStatus;
use Rialto\Allocation\Status\RequirementStatus;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Requirement\Validator\VersionIsActive;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Producer\StockProducerFactory;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Controls stock allocation for a work order requirement.
 *
 * @VersionIsActive
 */
class AllocationConfigurationArray extends ConsolidatedRequirement
{
    /** @var AllocationConfiguration[] */
    private $allocationConfigurations = [];

    /** @var string */
    private $warehouseType = '';

    /** @var int */
    private $warehousePriority = 0;

    /** @var bool */
    private $warehouseDisabled = false;

    /** @var string */
    private $purchaseOrderType = '';

    /** @var int */
    private $purchaseOrderPriority = 0;

    /** @var bool */
    private $purchaseOrderDisabled = false;

    /** @var string */
    private $cmType = '';

    /** @var int */
    private $cmPriority = 0;

    /** @var bool */
    private $cmDisabled = false;

    public function __construct(array $allocationConfigurations)
    {
        $this->allocationConfigurations = $allocationConfigurations;
        foreach ($this->allocationConfigurations as $allocConfig) {
            /** @var AllocationConfiguration $allocConfig */
            if ($allocConfig->getType() === AllocationConfiguration::TYPE_WAREHOUSE_STOCK) {
                $this->warehouseType = $allocConfig->getType();
                $this->warehousePriority = $allocConfig->getPriority();
                $this->warehouseDisabled = $allocConfig->isDisabled();
            } else if ($allocConfig->getType() === AllocationConfiguration::TYPE_PO_ITEMS) {
                $this->purchaseOrderType = $allocConfig->getType();
                $this->purchaseOrderPriority = $allocConfig->getPriority();
                $this->purchaseOrderDisabled = $allocConfig->isDisabled();
            } else if ($allocConfig->getType() === AllocationConfiguration::TYPE_CONTRACT_MANUFACTURER_STOCK) {
                $this->cmType = $allocConfig->getType();
                $this->cmPriority = $allocConfig->getPriority();
                $this->cmDisabled = $allocConfig->isDisabled();
            }
        }
    }

    /**
     * @return string
     */
    public function getWarehouseType()
    {
        return $this->warehouseType;
    }

    public function getWarehousePriority()
    {
        return $this->warehousePriority;
    }

    /** @param int $priority */
    public function setWarehousePriority($priority)
    {
        $this->warehousePriority = $priority;
    }

    public function getWarehouseDisabled()
    {
        return $this->warehouseDisabled;
    }

    /** @param bool $disabled */
    public function setWarehouseDisabled($disabled)
    {
        $this->warehouseDisabled = $disabled;
    }

    /**
     * @return string
     */
    public function getPurchaseOrderType()
    {
        return $this->purchaseOrderType;
    }

    public function getPurchaseOrderPriority()
    {
        return $this->purchaseOrderPriority;
    }

    /** @param int $priority */
    public function setPurchaseOrderPriority($priority)
    {
        $this->purchaseOrderPriority = $priority;
    }

    public function getPurchaseOrderDisabled()
    {
        return $this->purchaseOrderDisabled;
    }

    /** @param bool $disabled */
    public function setPurchaseOrderDisabled($disabled)
    {
        $this->purchaseOrderDisabled = $disabled;
    }

    /**
     * @return string
     */
    public function getCmType()
    {
        return $this->cmType;
    }

    public function getCmPriority()
    {
        return $this->cmPriority;
    }

    /** @param int $priority */
    public function setCmPriority($priority)
    {
        $this->cmPriority = $priority;
    }

    public function getCmDisabled()
    {
        return $this->cmDisabled;
    }

    /** @param bool $disabled */
    public function setCmDisabled($disabled)
    {
        $this->cmDisabled = $disabled;
    }
}
