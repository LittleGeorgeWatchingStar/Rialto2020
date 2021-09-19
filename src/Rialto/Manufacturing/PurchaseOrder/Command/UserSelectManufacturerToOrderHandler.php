<?php


namespace Rialto\Manufacturing\PurchaseOrder\Command;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
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
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Producer\StockProducerFactory;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Sku;
use Rialto\Stock\Transfer\Orm\TransferRepository;
use Rialto\Stock\Transfer\Transfer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Handler service for @see UserSelectManufacturerToOrderCommand
 */
final class UserSelectManufacturerToOrderHandler
{
    /** @var EntityManager */
    private $em;

    /** @var DbManager */
    private $dbm;

    /** @var PurchaseOrderRepository */
    private $poRepository;

    /** @var AllocationConfigurationRepository */
    private $allocatorConfigurationRepo;

    /** @var LoggerInterface */
    private $logger;

    /** @var ValidatorInterface */
    private $validator;

    /** @var StockProducerFactory */
    private $producerFactory;

    /** @var AllocationFactory */
    private $allocFactory;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger,
                                DbManager $dbManager, ValidatorInterface $validator,
                                StockProducerFactory $producerFactory, AllocationFactory $allocFactory)
    {
        $this->poRepository = $em->getRepository(PurchaseOrder::class);
        $this->allocatorConfigurationRepo = $em->getRepository(AllocationConfiguration::class);
        $this->logger = $logger;
        $this->em = $em;
        $this->dbm = $dbManager;
        $this->validator = $validator;
        $this->producerFactory = $producerFactory;
        $this->allocFactory = $allocFactory;
    }

    public function handle(UserSelectManufacturerToOrderCommand $command)
    {
        // Original OrderPartsCommand
        $poId = $command->getPurchaseOrderId();
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->poRepository->find($poId);
        if ($purchaseOrder === null) {
            throw new InvalidDataException("Purchase Order ". $poId . "could not be found.");
        }

        $list = WorkOrderCollection::fromPurchaseOrder($purchaseOrder);
        if ($list->isClosed()) {
            $this->warning("Cannot allocate to $purchaseOrder: it is closed.");
            return;
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

        $qtyAllocated = $this->allocate($allocator);
        $this->notice(sprintf("Allocated %s units for %s.",
            number_format($qtyAllocated),
            $purchaseOrder));
    }

    private function logValidationErrors(WorkOrderAllocator $allocator)
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
                if ($item->shouldAutoOrder()) {
                    $producer = $item->orderStock($this->producerFactory);
                    $this->logItemOrdered($item, $producer);
                }
            }
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
    }

    private function logItemOrdered(RequirementAllocator $item,
                                    StockProducer $producer = null)
    {
        if (! $producer) {
            return;
        }
        $msg = sprintf('%s units of %s added to %s.',
            number_format($item->getQtyToOrder()),
            $producer->getSku(),
            $producer->getSourceDescription()
        );
        $this->notice($msg);
    }

    private function allocate(WorkOrderAllocator $allocator)
    {
        $this->dbm->beginTransaction();
        try {
            $qtyAllocated = $allocator->allocate($this->allocFactory);
            $this->dbm->flushAndCommit();
            return $qtyAllocated;
        } catch (OptimisticLockException $ex) {
            $this->dbm->rollBack();
            $this->warning($ex->getMessage());
            return 0;
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
    }
}
