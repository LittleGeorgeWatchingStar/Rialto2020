<?php

namespace Rialto\Manufacturing\Allocation\Command;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Database\Orm\DbManager;
use Rialto\Exception\InvalidDataException;
use Rialto\Manufacturing\Allocation\AllocationConfiguration;
use Rialto\Manufacturing\Allocation\Orm\AllocationConfigurationRepository;
use Rialto\Manufacturing\Allocation\WorkOrderAllocator;
use Rialto\Manufacturing\WorkOrder\WorkOrderCollection;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\StockProducerFactory;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Handler service for @see AllocateCommand
 */
final class AllocateHandler
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

    public function handle(AllocateCommand $command)
    {
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
        $allocator->setUserSelectionSourcesIds($command->getUserSelectionSourcesIds());

        $allocationConfigurations = $this->allocatorConfigurationRepo->findAll();
        $allocator->setAllocationConfigurations($allocationConfigurations);

        if ($purchaseOrder->isAllocateFromCM()) {
            $allocator->addLocation($purchaseOrder->getBuildLocation());
            $allocator->setShareBins(true);
        }
        $allocator->addLocation(Facility::fetchHeadquarters($this->dbm));

        $allocator->createItems($this->dbm);
        $allocator->loadPurchasingData($this->dbm);
        $allocator->validate($this->validator);
        $this->logValidationErrors($allocator);

        $qtyAllocated = $this->allocate($allocator);
        $this->notice(sprintf("Allocated %s units for %s.",
            number_format($qtyAllocated),
            $purchaseOrder));
    }

    /**
     * @param WorkOrderAllocator $allocator
     * @return int
     * @throws \Exception
     */
    private function allocate(WorkOrderAllocator $allocator): int
    {
        $this->dbm->beginTransaction();
        try {
            $qtyAllocated = $allocator->allocate($this->allocFactory);
            $this->dbm->flushAndCommit();
            return $qtyAllocated;
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
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
}
