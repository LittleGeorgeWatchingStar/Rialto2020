<?php

namespace Rialto\Manufacturing\WorkOrder;

use InvalidArgumentException;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\PurchaseOrder\ManufacturingExpense;
use Rialto\Manufacturing\Requirement\RequirementFactory;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Purchasing\Order\PurchaseOrderFactory;
use Rialto\Sales\Returns\SalesReturnItem;

/**
 * Creates new work orders.
 */
class WorkOrderFactory
{
    const REWORK_UNIT_COST = 0.01;

    /** @var DbManager */
    private $dbm;

    /** @var PurchaseOrderFactory */
    private $poFactory;

    /** @var RequirementFactory */
    private $requirementFactory;

    public function __construct(
        DbManager $dbm,
        PurchaseOrderFactory $poFactory,
        RequirementFactory $reqFactory)
    {
        $this->dbm = $dbm;
        $this->poFactory = $poFactory;
        $this->requirementFactory = $reqFactory;
    }

    /** @return WorkOrder */
    public function create(WorkOrderCreation $creation)
    {
        /* Be careful here: the order of operations is important! */
        $po = $this->poFactory->forWorkOrder($creation);

        $parent = $creation->createParentOrder($po);
        $this->dbm->persist($parent);
        $this->dbm->flush();
        $this->requirementFactory->updateRequirements($parent);

        if ($creation->isCreateChild()) {
            $child = $creation->createChildOrder($po, $this->dbm);
            $this->dbm->persist($child);
            $this->requirementFactory->updateRequirements($child);
            $this->linkParentAndChild($parent, $child);
            $this->dbm->flush();
        }
        $expenses = new ManufacturingExpense($this->dbm);
        $expenses->addManufacturingExpensesIfNeeded($po);
        $this->dbm->flush();

        return $parent;
    }

    private function linkParentAndChild(WorkOrder $parent, WorkOrder $child)
    {
        $boardReq = $parent->getRequirement($child);
        $boardReq->setVersion($child->getVersion());
        $child->setParent($parent);
    }

    /** @return WorkOrder */
    public function createRework(WorkOrderCreation $creation)
    {
        assertion(! $creation->isCreateChild());
        $po = $this->poFactory->forWorkOrder($creation);

        $workOrder = $creation->createParentOrder($po);
        $workOrder->initializeUnitCost(self::REWORK_UNIT_COST);

        $this->dbm->persist($workOrder);
        $this->dbm->flush();
        $workOrder->setRework(true);

        /* Here's the tricky bit: the defective product itself is the first
         * requirement for the rework order. */
        $woReq = $workOrder->createRequirement(
            $workOrder->getStockItem(),
            1,
            WorkType::fetchRework($this->dbm));
        $woReq->setVersion($workOrder->getVersion());
        $woReq->setCustomization($workOrder->getCustomization());

        return $workOrder;
    }

    /**
     * Creates a rework order to repair the returned item.
     *
     * @param SalesReturnItem $rmaItem
     * @return WorkOrder
     * @throws InvalidArgumentException
     */
    public function forSalesReturn(SalesReturnItem $rmaItem, $qty)
    {
        if (! $rmaItem->isManufactured()) {
            throw new InvalidArgumentException(
                'Cannot create a work order for a purchased item'
            );
        }

        $template = new WorkOrderCreation($rmaItem->getStockItem());
        $template->setCustomization($rmaItem->getCustomization());
        $template->setCreateChild(false);
        $template->setQtyOrdered($qty);

        $originalWo = $rmaItem->getOriginalWorkOrder();
        assertion(null != $originalWo);
        $purchData = $originalWo->getPurchasingData();
        $template->setPurchasingData($purchData);

        $version = $rmaItem->getVersion();
        assertion($version->isSpecified());
        $template->setVersion($version);

        return $this->createRework($template);
    }
}
