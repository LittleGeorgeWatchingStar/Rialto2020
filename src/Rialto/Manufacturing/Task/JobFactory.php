<?php

namespace Rialto\Manufacturing\Task;

use Doctrine\Common\Persistence\ObjectManager;
use JMS\JobQueueBundle\Entity\Job;
use Rialto\Manufacturing\Allocation\Command\AllocateCommand;
use Rialto\Manufacturing\PurchaseOrder\Command\OrderPartsCommand;
use Rialto\Manufacturing\Task\Cli\TasksCommand;
use Rialto\Purchasing\Order\PurchaseOrder;


/**
 * Creates scheduled manufacturing jobs.
 *
 * These are asynchronous jobs that are executed by a daemon process, rather
 * than as part of the main HTTP request.
 */
class JobFactory
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Creates required jobs for $po, if any are needed.
     */
    public function forPurchaseOrder(PurchaseOrder $po)
    {
        // TODO: Auto-Allocate and Auto-Order disabled until they are fixed.
        $task = new Job(TasksCommand::NAME, [$po->getId()]);
        $this->om->persist($task);

        return;

//        $previousJob = $this->scheduleAllocationJobs($po);
//
//        if ($previousJob) {
//            /* Since the PO might have changed, refresh the task list. */
//            $tasks = new Job(TasksCommand::NAME, [$po->getId()]);
//            $tasks->addDependency($previousJob);
//            $this->om->persist($tasks);
//        }
    }
    // this is not used, previously for auto allocator
//    private function scheduleAllocationJobs(PurchaseOrder $po)
//    {
//        $status = $po->getAllocationStatus();
//        if ($status->isFullyAllocated()) {
//            return null;
//        }
//        if ($po->hasReworkOrder()) {
//            /* Allocations for rework orders are completely custom: we need
//             * to allocate from the broken part(s). */
//            return null;
//        }
//
//        /* Order any parts that we don't have enough of in stock. */
//        $orderParts = new Job(OrderPartsCommand::NAME, [$po->getId()]);
//        $this->om->persist($orderParts);
//
//        if ($po->allWorkOrdersHaveRequestedDate()) {
//            /* Allocate from stock and orders. */
//            $allocate = new Job(AllocateCommand::NAME, [$po->getId()]);
////            $allocate = new AllocateCommand($po->getId());
////            $this->commandBus->handle($allocate);
//            $allocate->addDependency($orderParts);
//            $this->om->persist($allocate);
//
//            return $allocate;
//        } else {
//            return $orderParts;
//        }
//
//    }
}
