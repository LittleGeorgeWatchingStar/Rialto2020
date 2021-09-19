<?php

namespace Rialto\Allocation\Requirement\RequirementTask;

use Rialto\Allocation\EstimatedArrivalDate\EstimatedArrivalDateGenerator;
use Rialto\Manufacturing\Audit\AuditItem;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Sales\Order\SalesOrder;

class RequirementTaskFactory
{
    /** @var EstimatedArrivalDateGenerator */
    private $estimatedArrivalDateGenerator;

    public function __construct(EstimatedArrivalDateGenerator $expectedDateGenerator)
    {
        $this->estimatedArrivalDateGenerator = $expectedDateGenerator;
    }

    /**
     * @return RequirementTask[]
     */
    public function getSalesOrderRequirementTasks(SalesOrder $salesOrder): array
    {
        /** @var RequirementTask[] $tasks */
        $tasks = [];

        foreach ($salesOrder->getAllocationStatus()->getProducers() as $producer) {
            $purchaseOrder = $producer->getPurchaseOrder();
            $tasks = array_merge($tasks, $this->getPurchaseOrderRequirementTasks($purchaseOrder));
        }

        $this->sortRequirementTasks($tasks);
        return $tasks;
    }

    /**
     * @return RequirementTask[]
     */
    public function getPurchaseOrderRequirementTasks(PurchaseOrder $purchaseOrder): array
    {
        /** @var AuditItem[] $items */
        $items = [];

        foreach ($purchaseOrder->getWorkOrders() as $workOrder) {
            foreach ($workOrder->getRequirements() as $requirement) {
                if ($requirement->isProvidedByChild()) {
                    continue;
                }
                $key = $requirement->getFullSku();
                if (!isset($items[$key])) {
                    $items[$key] = new AuditItem($purchaseOrder->getBuildLocation());
                }
                $items[$key]->addRequirement($requirement);
            }
        }

        $tasks = $this->getRequirementTasksFromAuditItems($items);

        $this->sortRequirementTasks($tasks);
        return $tasks;
    }

    /**
     * @param AuditItem[] $auditItems
     * @return RequirementTask[]
     */
    private function getRequirementTasksFromAuditItems(array $auditItems): array
    {
        /** @var RequirementTask[] $tasks */
        $tasks = [];
        foreach ($auditItems as $item) {
            foreach ($item->getConsolidatedAllocations() as $allocation) {

                if ($allocation->isWhereNeeded()) {
                    continue;
                }

                $tasks[] = new RequirementTask($item->getFullSku(),
                    $item->getPurchaseOrder(),
                    $allocation->getQtyAllocated(),
                    $allocation->getSource(),
                    $allocation->getLocationWhereNeeded(),
                    $this->estimatedArrivalDateGenerator->generate($allocation));
            }

            if ($item->getTotalQtyUnallocated() > 0) {
                $tasks[] = new RequirementTask($item->getFullSku(),
                    $item->getPurchaseOrder(),
                    $item->getTotalQtyUnallocated(),
                    null,
                    $item->getBuildLocation(),
                    null);
            }
        }

        return $tasks;
    }

    /**
     * @param RequirementTask[] $tasks
     */
    private function sortRequirementTasks(array &$tasks)
    {
        usort($tasks, function (RequirementTask $a, RequirementTask $b) {
            $dateA = $a->getCommitmentDate() ?: $a->getRequestedDate() ?: $a->getEstimatedArrivalDate();
            $dateB = $b->getCommitmentDate() ?: $b->getRequestedDate() ?: $b->getEstimatedArrivalDate();
            if (!$a->getSource() XOR !$b->getSource()) {
                return !$a->getSource() ? -1 : 1;
            }
            if ($dateA !== $dateB) {
                return $dateA < $dateB ? -1 : 1;
            }
            return strcmp($a->getFullSku(), $b->getFullSku());
        });
    }
}