<?php

namespace Rialto\Purchasing\Producer;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Rialto\Allocation\AllocationEvents;
use Rialto\Allocation\Consumer\StockConsumerEvent;
use Rialto\Manufacturing\Requirement\RequirementFactory;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkOrder\WorkOrderFamily;
use Rialto\Purchasing\Catalog\PurchasingDataSynchronizer;
use Rialto\Purchasing\Order\PurchaseOrder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Synchronizes the dependent records of a work order after the order has
 * been updated to ensure that everything is in a consistent state.
 */
class DependencyUpdater implements LoggerAwareInterface
{
    /** @var RequirementFactory */
    private $requirementFactory;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var PurchasingDataSynchronizer */
    private $synchronizer;

    /** @var LoggerInterface */
    private $log;

    public function __construct(RequirementFactory $factory,
                                EventDispatcherInterface $dispatcher,
                                PurchasingDataSynchronizer $sync)
    {
        $this->requirementFactory = $factory;
        $this->dispatcher = $dispatcher;
        $this->synchronizer = $sync;
        $this->log = new NullLogger();
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->log = $logger;
    }

    public function updateFamily(WorkOrderFamily $family)
    {
        foreach ($family as $wo) {
            $this->updateDependencies($wo);
        }
    }

    public function updatePurchaseOrder(PurchaseOrder $po)
    {
        foreach ($po->getItems() as $poItem) {
            $this->syncStockLevel($poItem);
            $poItem->roundQtyOrdered();
            $poItem->adjustAllocationsToMatchQtyRemaining();
            $poItem->deleteAllocationsForOtherLocations();
            if ($poItem instanceof WorkOrder) {
                $this->updateDependencies($poItem);
            }
        }
    }

    private function syncStockLevel(StockProducer $poItem)
    {
        $pd = $poItem->getPurchasingData();
        if ($pd) {
            $this->synchronizer->updateStockLevel($pd);
        }
    }

    public function updateDependencies(WorkOrder $wo)
    {
        if ($wo->isDirty(WorkOrder::DIRTY_REQUIREMENTS)) {
            $this->updateRequirements($wo);
        }
        if ($wo->isDirty(WorkOrder::DIRTY_ALLOCATIONS)) {
            $this->updateAllocations($wo);
        }
        $wo->setClean();
    }

    private function updateRequirements(WorkOrder $wo)
    {
        $this->requirementFactory->updateRequirements($wo);
        $this->log->notice("Requirements for $wo have been updated successfully.");
    }

    private function updateAllocations(WorkOrder $wo)
    {
        $event = new StockConsumerEvent([$wo]);
        $this->dispatcher->dispatch(AllocationEvents::STOCK_CONSUMER_CHANGE, $event);
    }
}
