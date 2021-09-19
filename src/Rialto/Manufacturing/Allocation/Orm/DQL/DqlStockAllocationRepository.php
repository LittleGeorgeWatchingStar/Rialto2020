<?php


namespace Rialto\Manufacturing\Allocation\Orm\DQL;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Allocation\Allocation\BinAllocation;
use Rialto\Allocation\Allocation\Orm\StockAllocationRepository;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Manufacturing\Allocation\NonFrozenStockAllocationAndWorkOrder;
use Rialto\Manufacturing\Allocation\WorkOrderAllocator;
use Rialto\Manufacturing\WorkOrder\WorkOrder;

/**
 * A DQL implementation of a BankAccountRepository with a Doctrine EntityManager
 * backend.
 */
final class DqlStockAllocationRepository
{
    private $repo;

    /** @var StockAllocationRepository */
    private $allocRepo;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repo = $entityManager->getRepository(WorkOrder::class);
        $this->allocRepo = $entityManager->getRepository(StockAllocation::class);
    }

    public function getOtherWorkOrdersWithDateAndStockAllocations(WorkOrderAllocator $workOrderAllocator)
    {
        /** @var NonFrozenStockAllocationAndWorkOrder[] */
        $nonFrozenStockAllocationAndWorkOrders = [];
        $requirementAllocators = $workOrderAllocator->getItems();
        foreach ($requirementAllocators as $requirementAllocator) {
            $sku = $requirementAllocator->getFullSku();
            $nonFrozenStockAllocationAndWorkOrders[$sku] = [];
            $requirementAllocator->getStockItem();
            /** @var StockAllocation[] $allocs */
            $allocs = $this->allocRepo->createQueryBuilder('a')
                ->andWhere('a.stockItem = :stockId')
                ->setParameter('stockId', $sku)
                ->getQuery()
                ->getResult();
            foreach ($allocs as $stockAllocation) {
                if ($stockAllocation instanceof BinAllocation
                    && $stockAllocation->isNotFrozen()
                    && $stockAllocation->isNeededAtLocation($requirementAllocator->getFacility())) {
                    $wo = $stockAllocation->getConsumer();
                    if ($wo instanceof WorkOrder) {
                        $requestedDate = $wo->getPurchaseOrder()->getRequestedDate();
                        $qty = $stockAllocation->getQtyAllocated();
//                        $nonFrozenStockAllocationAndWorkOrder = new NonFrozenStockAllocationAndWorkOrder($wo, $qty, $requestedDate, $stockAllocation);
                        $nonFrozenStockAllocationAndWorkOrder = [
                            'workOrderId' => $wo->getId(),
                            'location' => $wo->getLocation()->getName(),
                            'qty' => $qty,
                            'date' => $requestedDate ? $requestedDate->format(\DateTime::ISO8601) : '',
                            'allocId' => $stockAllocation->getId(),
                            'purchaseOrderId' => $wo->getPurchaseOrder()->getId(),
                            'fullSku' => $stockAllocation->getFullSku(),
                        ];
                        $nonFrozenStockAllocationAndWorkOrders[$sku][] = $nonFrozenStockAllocationAndWorkOrder;
                    }
                }
            }
        }
        return $nonFrozenStockAllocationAndWorkOrders;
    }
}
