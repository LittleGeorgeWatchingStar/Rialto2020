<?php

namespace Rialto\Manufacturing\WorkOrder;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Rialto\Port\CommandBus\CommandQueue;
use Rialto\Purchasing\Catalog\Command\RefreshPurchasingDataStockLevelCommand;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for work order events and automatically refresh purchasing data
 * stock level for all active purchasing data
 */
class WorkOrderSubscriber implements EventSubscriberInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var PurchasingDataRepository */
    private $purchasingDataRepo;

    /** @var CommandQueue */
    private $commandQueue;

    public static function getSubscribedEvents()
    {
        return [
            WorkOrderEvents::WORK_ORDER_CREATED => 'newWorkOrderCreated',
        ];
    }

    public function __construct(
        EntityManagerInterface $em,
        CommandQueue $commandQueue)
    {
        $this->em = $em;
        $this->purchasingDataRepo = $this->em->getRepository(PurchasingData::class);
        $this->commandQueue = $commandQueue;
    }

    public function newWorkOrderCreated(WorkOrderCreatedEvent $event)
    {
        $workOrder = $event->getWorkOrder();
        $requirements = $workOrder->getRequirements();
        foreach ($requirements as $requirement) {
            $stockItem = $requirement->getStockItem();

            /** @var PurchasingData[] $allPurchasingData */
            $allPurchasingData = $this->purchasingDataRepo->findAllPurchasingDataBySku($stockItem->getSku());

            foreach ($allPurchasingData as $purchasingData) {
                try {
                    $command = new RefreshPurchasingDataStockLevelCommand($purchasingData->getId());
                    $this->commandQueue->queue($command, false);
                } catch (Exception $e) {
                    $this->em->rollback();
                    return $e->getMessage();
                }
            }
        }
        $this->em->flush();

        return 0;
    }
}
