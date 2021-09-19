<?php

namespace Rialto\Purchasing\Producer;

use InvalidArgumentException;
use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Allocation\Requirement\RequirementInterface;
use Rialto\Allocation\Status\RequirementStatus;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkOrder\WorkOrderCreation;
use Rialto\Manufacturing\WorkOrder\WorkOrderFactory;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseInitiator;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Order\PurchaseOrderFactory;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Sales\Order\Allocation\SalesOrderDetailAllocator;
use Rialto\Sales\Order\Allocation\SalesOrderDetailAllocatorManufactured;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use UnexpectedValueException;


/**
 * Creates a new stock producer for the given stock consumer.
 * @see StockProducer
 * @see stockConsumer
 */
class StockProducerFactory implements PurchaseInitiator
{
    const INITIATOR_CODE = 'PO System';

    /** @var DbManager */
    private $dbm;

    /** @var PurchaseOrderFactory */
    private $poFactory;

    /** @var WorkOrderFactory */
    private $woFactory;

    public function __construct(
        DbManager $dbm,
        PurchaseOrderFactory $poFactory,
        WorkOrderFactory $woFactory)
    {
        $this->dbm = $dbm;
        $this->poFactory = $poFactory;
        $this->woFactory = $woFactory;
    }

    public function getInitiatorCode()
    {
        return self::INITIATOR_CODE;
    }

    /** @return StockProducer */
    public function create(RequirementInterface $requirement, $qtyToOrder = null)
    {
        $item = $requirement->getStockItem();
        assertion($item->isPhysicalPart());

        if (null === $qtyToOrder) {
            $qtyToOrder = $this->getQtyToOrder($requirement);
        }
        $this->checkQuantity($qtyToOrder);

        if ($item->isPurchased()) {
            return $this->createPurchaseOrderItem($requirement, $qtyToOrder);
        } elseif ($item->isManufactured()) {
            return $this->createWorkOrder($requirement, $qtyToOrder);
        }
        throw new UnexpectedValueException("Invalid item type");
    }

    public static function getQtyToOrder(RequirementInterface $requirement): int
    {
        $status = new RequirementStatus($requirement->getFacility());
        $status->addRequirement($requirement);
        $qtyLeft = $status->getQtyNeeded() - $status->getQtyAtLocation();
        if ($qtyLeft == 0) {
            return 0;
        }
        $stockItem = $requirement->getStockItem();
        $orderQty = $stockItem->getEconomicOrderQty();
        return max($qtyLeft, $orderQty);
    }

    public static function getFabQtyToOrder(RequirementInterface $requirement): int
    {
        $status = new RequirementStatus($requirement->getFacility());
        $status->addRequirement($requirement);
        $qtyLeft = $status->getQtyNeeded() - $status->getQtyAtLocation();
        if ($qtyLeft == 0) {
            return 0;
        }
        $stockItem = $requirement->getStockItem();
        $orderQty = $stockItem->getEconomicOrderQty();
        return max($qtyLeft, $orderQty);
    }

    private function checkQuantity($qtyToOrder)
    {
        if ($qtyToOrder <= 0) {
            throw new InvalidArgumentException("Consumer is fully allocated");
        }
    }

    /** @return PurchaseOrderItem */
    private function createPurchaseOrderItem(RequirementInterface $requirement, $qtyToOrder)
    {
        $purchData = $this->loadPurchasingData($requirement);
        $po = $this->findOpenPurchaseOrder($requirement, $purchData);
        if (! $po) {
            $po = $this->createNewPurchaseOrder($purchData);
            $this->dbm->persist($po);
            $this->dbm->flush();
        }

        $poItem = $this->addItemToPurchaseOrder($requirement, $po, $purchData, $qtyToOrder);
        $this->dbm->persist($poItem);
        $this->dbm->flush();
        return $poItem;
    }

    private function loadPurchasingData(RequirementInterface $requirement)
    {
        /** @var $repo PurchasingDataRepository */
        $repo = $this->dbm->getRepository(PurchasingData::class);
        return $repo->findPreferredForRequirement($requirement);
    }

    /**
     * @return PurchaseOrder|null
     */
    private function findOpenPurchaseOrder(
        RequirementInterface $requirement,
        PurchasingData $purchData)
    {
        // PCBs should always get a new PO.
        if ($requirement->isCategory(StockCategory::PCB)) {
            return null;
        }
        /** @var $repo PurchaseOrderRepository */
        $repo = $this->dbm->getRepository(PurchaseOrder::class);
        return $repo->findOpenOrderThatCanSupplyItem(
            $requirement->getStockItem(),
            $requirement->getVersion(),
            $purchData->getSupplier(),
            $this->getDeliveryLocation()
        );
    }

    /** @return Facility */
    private function getDeliveryLocation()
    {
        /** @var FacilityRepository $repo */
        $repo = $this->dbm->getRepository(Facility::class);
        return $repo->getHeadquarters();
    }

    private function createNewPurchaseOrder(PurchasingData $purchData)
    {
        $po = $this->poFactory->create($this);
        $supplier = $purchData->getSupplier();
        $po->setSupplier($supplier);
        $po->setDeliveryLocation($this->getDeliveryLocation());
        return $po;
    }

    /** @return PurchaseOrderItem */
    private function addItemToPurchaseOrder(
        RequirementInterface $requirement,
        PurchaseOrder $po,
        PurchasingData $purchData,
        $qtyToOrder)
    {
        $stockItem = $requirement->getStockItem();
        $version = $requirement->getVersion();
        $poItem = $po->getLineItemIfExists($stockItem, $version);
        if ($poItem) {
            $poItem->setQtyOrdered($poItem->getQtyOrdered() + $qtyToOrder);
        } else {
            $minimumOrderQty = $purchData->getMinimumOrderQty();
            $orderQty = max($minimumOrderQty, $qtyToOrder);
            $poItem = $po->addItemFromPurchasingData($purchData);
            $poItem->setQtyOrdered($orderQty);
            $version = $this->getVersionToOrder($stockItem, $version, $purchData);
            if ($version) {
                $poItem->setVersion($version);
            }

            $poItem->resetUnitCost();
        }
        $poItem->roundQtyOrdered();
        return $poItem;
    }

    private function getVersionToOrder(
        StockItem $item,
        Version $version,
        PurchasingData $purchData)
    {
        if (! $item->isVersioned()) {
            return null;
        }
        if (! $version->isSpecified()) {
            $version = $purchData->getVersion();
        }
        if (! $version->isSpecified()) {
            $version = $item->getAutoBuildVersion();
        }
        return $version;
    }

    private function createWorkOrder(RequirementInterface $requirement, $qtyToOrder)
    {
        $creation = $this->createWorkOrderTemplate($requirement, $qtyToOrder);
        return $this->createWorkOrderFromTemplate($creation);
    }

    private function createWorkOrderTemplate(RequirementInterface $requirement,
                                             int $qtyToOrder): WorkOrderCreation
    {
        $item = $requirement->getStockItem();
        $version = $requirement->getVersion();
        $customization = $requirement->getCustomization();

        $creation = new WorkOrderCreation($item, $version);
        $creation->loadDefaultValues($this->dbm);
        $creation->setQtyOrdered($qtyToOrder);

        if ($version->isSpecified()) {
            $creation->setVersion($version);
        }
        $creation->setCustomization($customization);

        return $creation;
    }

    private function createWorkOrderFromTemplate(
        WorkOrderCreation $creation): WorkOrder
    {
        $workOrder = $this->woFactory->create($creation);
        $workOrder->setOpenForAllocation(true);
        $this->dbm->persist($workOrder);
        return $workOrder;
    }

    public function createForSalesOrderDetail(
        SalesOrderDetailAllocator $allocator): StockProducer
    {
        $requirement = $allocator->getRequirement();
        if ($allocator->isManufactured()) {
            /** @var SalesOrderDetailAllocatorManufactured $allocator */
            $creation = $this->createWorkOrderTemplate($requirement, $allocator->getQtyToOrder());
            $creation->setPurchasingData($allocator->getPurchasingData());
            $createChild = $creation->hasChild() && $allocator->isCreateChild();
            $creation->setCreateChild($createChild);
            return $this->createWorkOrderFromTemplate($creation);
        } else {
            return $this->create($requirement);
        }
    }
}
