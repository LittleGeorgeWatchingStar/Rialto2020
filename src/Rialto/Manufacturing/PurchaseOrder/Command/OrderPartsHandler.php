<?php

namespace Rialto\Manufacturing\PurchaseOrder\Command;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Database\Orm\DbManager;
use Rialto\Exception\InvalidDataException;
use Rialto\Manufacturing\Allocation\AllocationConfiguration;
use Rialto\Manufacturing\Allocation\AllocatorIndex;
use Rialto\Manufacturing\Allocation\Orm\AllocationConfigurationRepository;
use Rialto\Manufacturing\Allocation\RequirementAllocator;
use Rialto\Manufacturing\Allocation\WorkOrderAllocator;
use Rialto\Manufacturing\WorkOrder\WorkOrderCollection;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Producer\StockProducerFactory;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Handler service for @see OrderPartsCommand
 */
final class OrderPartsHandler
{
    /** @var EntityManager */
    private $em;

    /** @var DbManager */
    private $dbm;

    /** @var PurchaseOrderRepository */
    private $poRepository;

    /** @var LoggerInterface */
    private $logger;

    /** @var ValidatorInterface */
    private $validator;

    /** @var StockProducerFactory */
    private $producerFactory;

    /** @var AllocationFactory */
    private $allocFactory;

    /** @var AllocationConfigurationRepository */
    private $allocatorConfigurationRepo;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger,
                                DbManager $dbManager, ValidatorInterface $validator,
                                StockProducerFactory $producerFactory, AllocationFactory $allocFactory)
    {
        $this->poRepository = $em->getRepository(PurchaseOrder::class);
        $this->logger = $logger;
        $this->em = $em;
        $this->dbm = $dbManager;
        $this->validator = $validator;
        $this->producerFactory = $producerFactory;
        $this->allocFactory = $allocFactory;
        $this->allocatorConfigurationRepo = $em->getRepository(AllocationConfiguration::class);
    }

    public function handle(OrderPartsCommand $command)
    {
        $poId = $command->getPurchaseOrderId();
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->poRepository->find($poId);
        if ($purchaseOrder === null) {
            throw new InvalidDataException("Purchase Order ". $poId . "could not be found.");
        }

        $list = WorkOrderCollection::fromPurchaseOrder($purchaseOrder);
        if ($list->isClosed()) {
            throw new InvalidDataException();
        }

        $allocator = new WorkOrderAllocator($list);
        $allocationConfigurations = $this->allocatorConfigurationRepo->findAll();
        $allocator->setAllocationConfigurations($allocationConfigurations);

        $cm = $purchaseOrder->getBuildLocation();
        if ($cm->isAllocateFromCM()) {
            $allocator->addLocation($cm);
            $allocator->setShareBins(true);
        }
        $allocator->addLocation(Facility::fetchHeadquarters($this->dbm));

        $allocator->createItems($this->dbm);
        $allocator->loadPurchasingData($this->dbm);
        $allocator->validate($this->validator);
        $this->logValidationErrors($allocator);

        $this->orderStockIfNeeded($allocator);
    }

    /**
     * @param WorkOrderAllocator $allocator
     * @throws \Exception
     */
    private function orderStockIfNeeded(WorkOrderAllocator $allocator)
    {
        $index = new AllocatorIndex($allocator);
        $toOrder = $index->getGroupToOrder();
        $this->dbm->beginTransaction();
        try {
            foreach ($toOrder->getItems() as $item) {

                $shouldOrder = true;

                if (!$item->hasPurchasingData()) {
                    $shouldOrder = false;
                } else {
                    foreach ($item->getCandidateSources() as $sourceCollection) {
                        foreach ($sourceCollection->getSources() as $basicStockSource) {
                            if ($basicStockSource instanceof PurchaseOrderItem &&
                                $basicStockSource->getQtyAvailableTo($item) > 0) {
                                $shouldOrder = false;
                            }
                        }
                    }
                }
                if ($shouldOrder) {
                    if ($item->getQtyToOrder() > 0 && $item->shouldAutoOrder()) {
                        $producer = $item->orderStock($this->producerFactory);
                        $this->logItemOrdered($item, $producer);
                    }
                } else {
                    $this->logItemNotOrdered($item);
                }

            }
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
    }

    private function logItemOrdered(RequirementAllocator $item,
                                    ?StockProducer $producer = null): void
    {
        if (!$producer) {
            return;
        }
        $msg = sprintf('%s units of %s added to %s.',
            number_format($item->getQtyToOrder()),
            $producer->getSku(),
            $producer->getSourceDescription()
        );
        $this->notice($msg);
    }

    private function logItemNotOrdered(RequirementAllocator $item): void
    {
        $msg = sprintf('Not ordering for %s, since there is already enough 
        available on purchase order',
            $item->getSku()
        );
        $this->notice($msg);
    }


    private function logValidationErrors(WorkOrderAllocator $allocator): void
    {
        foreach ($allocator->getItems() as $item) {
            if ($item->hasErrors()) {
                $this->warning(sprintf('%s has the following errors: %s',
                    $item->getSku(),
                    join(', ', $item->getErrors())));
            }
        }
    }

    protected function warning($msg, array $context = [])
    {
        $this->logger->warning($msg, $this->prepContext($context));
    }

    protected function notice($msg, array $context = [])
    {
        $this->logger->notice($msg, $this->prepContext($context));
    }

    private function prepContext(array $context)
    {
        $context['command'] = get_class($this);
        return $context;
    }
}
