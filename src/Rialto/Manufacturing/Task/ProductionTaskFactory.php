<?php

namespace Rialto\Manufacturing\Task;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Allocation\Status\SimpleAllocationStatus;
use Rialto\Manufacturing\ManufacturingEvents;
use Rialto\Manufacturing\Task\Orm\ProductionTaskRepository;
use Rialto\Manufacturing\Task\ProductionTask as Task;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkOrder\WorkOrderCollection;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Transfer\Orm\TransferRepository;
use Rialto\Stock\Transfer\Transfer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Creates lists of tasks that need to be done to complete a work order PO.
 */
class ProductionTaskFactory
{
    /** @var ObjectManager */
    private $om;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var ProductionTaskRepository */
    private $taskRepo;

    /** @var TransferRepository */
    private $transferRepo;

    /** @var PurchaseOrder */
    private $po;

    /** @var WorkOrderCollection */
    private $workOrders;

    /** @var SimpleAllocationStatus */
    private $status;

    /** @var Transfer[] */
    private $pendingTransfers;

    /** @var Transfer[] */
    private $sentTransfers;

    public function __construct(ObjectManager $om,
                                EventDispatcherInterface $dispatcher)
    {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->taskRepo = $om->getRepository(Task::class);
        $this->transferRepo = $om->getRepository(Transfer::class);
    }

    /**
     * Make sure the tasks that need to be done for $po are up-to-date.
     *
     * @return TaskList
     */
    public function refreshTasks(PurchaseOrder $po)
    {
        $this->loadData($po);
        $tasks = $this->getAllTasks();
        $this->taskRepo->resetTasks($tasks);
        return $tasks;
    }

    private function loadData(PurchaseOrder $po)
    {
        $this->po = $po;
        $this->workOrders = WorkOrderCollection::fromPurchaseOrder($po);
        $this->status = $this->po->getAllocationStatus();
        $this->pendingTransfers = $this->transferRepo->createBuilder()
            ->forPurchaseOrder($this->po)
            ->kitted()
            ->notSent()
            ->getResult();
        $this->sentTransfers = $this->transferRepo->createBuilder()
            ->forPurchaseOrder($this->po)
            ->sent()
            ->notReceived()
            ->getResult();
    }

    /** @return TaskList */
    private function getAllTasks()
    {
        $tasks = $this->getCompanyTasks();
        $tasks->merge($this->getSupplierTasks());
        $this->dispatcher->dispatch(
            ManufacturingEvents::ADD_PRODUCTION_TASKS,
            new ProductionTaskEvent($tasks));
        return $tasks;
    }

    /**
     * Get the tasks that we need to do.
     *
     * @return TaskList
     */
    private function getCompanyTasks()
    {
        $tasks = new TaskList($this->po);
        if ($this->po->isCompleted()) {
            return $tasks;
        }

        $tasks->add($this->requirementsTask());
        $tasks->add($this->allocateTask());
        $tasks->addAll($this->companyStockTasks());
        $tasks->add($this->pickupTransferTask());
        $tasks->add($this->missingItemsTask());
        $tasks->add($this->orderTask());
        $tasks->add($this->orderPartsTask());
        $tasks->add($this->receiveTask());
        return $tasks;
    }

    private function requirementsTask()
    {
        foreach ($this->workOrders as $wo) {
            /** @var $wo WorkOrder */
            if (!$wo->hasRequirements()) {
                return Task::create('Requirements')
                    ->setRoute('work_order_view', [
                        'order' => $wo->getId(),
                    ])
                    ->addRoles([
                        Role::PURCHASING,
                        Role::ENGINEER
                    ]);
            }
        }
        return null;
    }

    private function orderTask()
    {
        if (!$this->status->isFullyAllocated()) {
            return null;
        }
        if (!$this->po->hasItems()) {
            return null;
        }
        if (!$this->po->hasSupplier()) {
            return null;
        }
        if (!$this->workOrders->hasRequirements()) {
            return null;
        }

        if (!$this->po->isSent()) {
            return Task::create('Send PO')
                ->setRoute('purchase_order_view', [
                    'order' => $this->po->getId()
                ])->addRoles([
                    Role::PURCHASING
                ]);
        } elseif ($this->po->isRejected()) {
            return Task::create('Resubmit')
                ->setRoute('purchase_order_view', [
                    'order' => $this->po->getId()
                ])->addRoles([
                    Role::PURCHASING,
                    Role::ENGINEER,
                ]);
        }
        return null;
    }

    private function orderPartsTask()
    {
        $pos = $this->workOrders->getUnsentPOs();
        foreach ($pos as $po) {
            return Task::create('Order parts')
                ->setRoute('purchase_order_view', [
                    'order' => $po->getId()
                ])->addRoles([
                    Role::PURCHASING
                ]);
        }
        return null;
    }

    private function missingItemsTask()
    {
        if ($this->hasMissingItems()) {
            return Task::create('Missing')
                ->setRoute('Stock_Transfer_missingItems', [
                    'purchaseOrder' => $this->po->getId(),
                ])->addRoles([
                    Role::PURCHASING,
                    Role::ENGINEER,
                    Role::STOCK,
                    Role::WAREHOUSE,
                ]);
        }
        return null;
    }

    private function allocateTask()
    {
        if ($this->workOrders->isFullyIssued()) {
            return null;
        }
        if ($this->status->isFullyAllocated()) {
            return null;
        }

        return Task::create('Allocate')
            ->setRoute('purchase_order_allocate', [
                'id' => $this->po->getId(),
                'fromCM' => $this->po->isAllocateFromCM(),
            ])->addRoles([
                Role::PURCHASING,
                Role::ENGINEER,
                Role::STOCK,
            ]);
    }

    /**
     * Company stock tasks are determined on a per-work order basis so that
     * we don't delay an SMT run on account of held-up packaging items.
     *
     * @return Task[]
     */
    private function companyStockTasks()
    {
        $tasks = [];
        foreach ($this->po->getWorkOrders() as $wo) {
            $tasks[] = $this->companyStockTask($wo);
        }
        return $tasks;
    }

    /** @return Task|null */
    private function companyStockTask(WorkOrder $wo)
    {
        if ($wo->isFullyIssued()) {
            return null;
        }
        if (!$wo->isSent()) {
            /* Don't send kits until we've notified the supplier. */
            return null;
        }

        $collection = WorkOrderCollection::fromWorkOrder($wo);
        $status = $collection->getAllocationStatus();
        if (!$status->isStartedToBeAllocated()) {
            return null;
        }

        $location = $wo->getLocation();
        $hq = Facility::fetchHeadquarters($this->om);
        if (count($collection->getOutstandingPrepWork($hq))) {
            return Task::create('Prep')
                ->setRoute('manufacturing_kit_create', [
                    'id' => $location->getId(),
                    'po' => $this->po->getId(),
                ])->addRoles([
                    Role::PURCHASING,
                    Role::ENGINEER,
                    Role::STOCK,
                    Role::WAREHOUSE,
                ]);
        }
        if ($status->isEnRoute()) {
            return null;
        }
        if ($status->isStartedToBeAllocated()) {
            return Task::create('Send kit')
                ->setRoute('manufacturing_kit_create', [
                    'id' => $location->getId(),
                    'po' => $this->po->getId(),
                ])->addRoles([
                    Role::PURCHASING,
                    Role::ENGINEER,
                    Role::STOCK,
                    Role::WAREHOUSE,
                ]);
        }
        return null;
    }

    private function pickupTransferTask()
    {
        if (count($this->pendingTransfers) > 0) {
            return Task::create('Awaiting pickup')
                ->setWaiting()
                ->setRoute('warehouse_dashboard')
                ->addRoles([
                    Role::ENGINEER,
                    Role::STOCK,
                    Role::WAREHOUSE,
                ]);
        }
        return null;
    }

    private function hasMissingItems()
    {
        return $this->transferRepo->hasMissingTransferItems($this->po);
    }

    private function receiveTask()
    {
        $hq = Facility::fetchHeadquarters($this->om);

        $task = Task::create()
            ->setWaiting()
            ->addRoles([
                Role::PURCHASING,
                Role::ENGINEER,
                Role::STOCK,
                Role::WAREHOUSE,
            ]);
        if (count($this->workOrders->getOutstandingPOs($hq)) > 0) {
            $task->setName('Recv parts')
                ->setRoute('purchase_order_allocate', [
                    'id' => $this->po->getId(),
                    'fromCM' => $this->po->isAllocateFromCM(),
                ]);
            return $task;
        }
        if ($this->status->isKitComplete()) {
            $task->setName('Receive')
                ->setRoute('receive_po', [
                    'id' => $this->po->getId()
                ]);
            return $task;
        }
        return null;
    }

    /**
     * Get the tasks that the supplier/manufacturer needs to do.
     *
     * @return TaskList
     */
    private function getSupplierTasks()
    {
        $tasks = new TaskList($this->po, [Role::EMPLOYEE]);
        if ($this->po->isCompleted()) {
            return $tasks;
        }
        if (!$this->po->hasSupplier()) {
            return $tasks;
        }

        $tasks->add($this->productLabelTask());
        $tasks->add($this->docsTask());
        $tasks->add($this->receiveTransferTask());
        $tasks->add($this->ordersToManufacturerTask());
        $tasks->add($this->scrapTask());
        $tasks->add($this->auditTask());
        $tasks->add($this->commitmentTask());
        return $tasks;
    }

    private function productLabelTask()
    {
        if (!$this->po->isSent()) {
            return null;
        }

        $params = ['id' => $this->po->getId()];
        $task = Task::create('Product Labels')
            ->setRoute('stock_item_pdf_product_label', $params)
            ->addRole(Role::SUPPLIER_SIMPLE);

        if ($this->po->isPendingApproval()) {
            $task->setRequired();
        } elseif ($this->po->isRejected()) {
            $task->setWaiting();
        } else {
            $task->setOptional();
        }
        return $task;
    }

    private function docsTask()
    {
        if (!$this->po->isSent()) {
            return null;
        }

        $params = ['id' => $this->po->getId()];
        $task = Task::create('Docs')
            ->setRoute('supplier_order_approve', $params)
            ->addRole(Role::SUPPLIER_SIMPLE);

        if ($this->po->isPendingApproval()) {
            $task->setRequired();
        } elseif ($this->needsSupplierRmaNumber()) {
            $task->setName('RMA #')
                ->setRoute('supplier_order_reference', $params)
                ->setRequired();
        } elseif ($this->po->isRejected()) {
            $task->setWaiting();
        } else {
            $task->setOptional();
        }
        return $task;
    }

    private function needsSupplierRmaNumber()
    {
        if (!$this->workOrders->hasReworkOrder()) {
            return false;
        }
        if (!$this->po) {
            return false;
        }
        return $this->po->getSupplierReference() == '';
    }

    private function scrapTask()
    {
        if (!$this->po->isSent()) {
            return null;
        }
        if ($this->po->isPendingApproval()) {
            return null;
        }

        if ($this->workOrders->isIssued()) {
            $toScrap = $this->calculateQtyToScrap();
            $label = sprintf('Scrap %s pcs', number_format($toScrap));
            $task = Task::create($label)
                ->setRoute('supplier_order_scrap', [
                    'id' => $this->po->getId(),
                ])
                ->addRole(Role::SUPPLIER_SIMPLE)
                ->setOptional();
            return $task;
        }

        return null;
    }

    private function calculateQtyToScrap()
    {
        $total = 0;
        foreach ($this->po->getWorkOrders() as $wo) {
            $total += $wo->hasChild() ? 0 : $wo->getQtyRemaining();
        }
        return $total;
    }

    /**
     * Purchase orders that are being shipped directly to the manufacturer.
     * @return Task
     */
    private function ordersToManufacturerTask()
    {
        if (!$this->po->isSent()) {
            return null;
        }

        $cm = $this->po->getBuildLocation();
        $directPOs = $this->workOrders->getOutstandingPOs($cm);
        $task = Task::create('Recv PO')
            ->addRole(Role::SUPPLIER_ADVANCED)
            ->setWaiting();
        if (count($directPOs) > 1) {
            $task->setRoute('supplier_incoming_select', [
                'id' => $this->po->getId(),
            ]);
            return $task;
        } elseif (count($directPOs) == 1) {
            $po = reset($directPOs);
            $task->setRoute('supplier_incoming_receive', [
                'id' => $po->getId(),
            ]);
            return $task;
        }
        return null;
    }

    private function auditTask()
    {
        if ($this->workOrders->isFullyIssued()) {
            return null;
        }
        if (!$this->isAuditable()) {
            return null;
        }
        if ($this->status->isKitComplete()) {
            $label = $this->workOrders->hasTurnkeyBuild()
                ? 'Turnkey' : 'Kit complete';
            $task = Task::create($label)
                ->setRoute('supplier_auditBuild', [
                    'id' => $this->po->getId(),
                ]);
        } else {
            $task = Task::create('Shortages')
                ->setRoute('supplier_shortages', [
                    'orderId' => $this->po->getId(),
                ]);
        }

        $task->addRole(Role::SUPPLIER_SIMPLE)
            ->setOptional();
        return $task;
    }

    /**
     * @return bool True if the work order(s) can be audited
     *   to ensure that they are kit complete.
     */
    private function isAuditable()
    {
        return $this->po->isSent() && $this->workOrders->hasRequirements();
    }

    private function receiveTransferTask()
    {
        $sent = $this->sentTransfers;
        if (count($sent) == 0) {
            return null;
        }

        $task = Task::create('Receive')
            ->addRole(Role::SUPPLIER_ADVANCED);
        if (count($sent) == 1) {
            $transfer = reset($sent);
            $task->setRoute('supplier_transfer_receive', [
                'id' => $transfer->getId(),
            ]);
        } else {
            $task->setRoute('supplier_selectTransfer', [
                'id' => $this->po->getId(),
            ]);
        }
        return $task;
    }

    private function commitmentTask()
    {
        if ($this->po->isCompleted()) {
            return null;
        } elseif (!$this->po->isSent()) {
            return null;
        }
        $date = $this->workOrders->getNextOutstandingCommitmentDate();
        $label = $date ? Task::DUE_TASK_LABEL : Task::COMMITMENT_TASK_LABEL;

        $task = Task::create($label)
            ->setRoute('supplier_order_commitment', [
                'id' => $this->po->getId()
            ])
            ->addRole(Role::SUPPLIER_SIMPLE);
        if ($date || (!$this->status->isKitComplete())) {
            $task->setOptional();
        }
        return $task;
    }
}
