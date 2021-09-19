<?php


namespace Rialto\Sales\Order\Allocation\Command;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Database\Orm\DoctrineDbManager;
use Rialto\Exception\InvalidDataException;
use Rialto\Manufacturing\Requirement\Orm\RequirementRepository;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\PurchaseOrderFactory;
use Rialto\Purchasing\Order\SingleItemManualPurchaseOrder;
use Rialto\Purchasing\Producer\StockProducerFactory;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;

final class CreateStockItemOrderHandler
{
    /** @var PurchasingDataRepository */
    private $purchasingDataRepo;

    /** @var StockItemRepository */
    private $stockItemRepo;

    /** @var RequirementRepository */
    private $requirementRepo;

    /** @var PurchaseOrderFactory */
    private $purchaseOrderFactory;

    /** @var StockProducerFactory */
    private $stockProducerFactory;

    /** @var DoctrineDbManager */
    protected $dbm;

    public function __construct(EntityManagerInterface $em, PurchaseOrderFactory $purchaseOrderFactory, StockProducerFactory $producerFactory, DoctrineDbManager $dbm)
    {
        $this->purchasingDataRepo = $em->getRepository(PurchasingData::class);
        $this->stockItemRepo = $em->getRepository(StockItem::class);
        $this->requirementRepo = $em->getRepository(Requirement::class);
        $this->purchaseOrderFactory = $purchaseOrderFactory;
        $this->stockProducerFactory = $producerFactory;
        $this->dbm = $dbm;
    }

    public function handle(CreateStockItemOrderCommand $command)
    {
        $itemStockCode = $command->getStockItemStockCode();
        $stockItem = $this->stockItemRepo->findByStockCode($itemStockCode);
        $version = $stockItem->getVersion($command->getVersion());
        $orderQty = $command->getOrderQty();
        $pdId = $command->getPurchasingDataId();

        /** @var PurchasingData|null $poItem */
        $poItem = null;
        if ($pdId !== null) {
            /** @var PurchasingData $purchasingData */
            $purchasingData = $this->purchasingDataRepo->find($pdId);

            if ($purchasingData === null) {
                throw new InvalidDataException("Purchasing Data \'$pdId\' found.");
            }

            $singleItemManual = new SingleItemManualPurchaseOrder($stockItem, $version, $orderQty, $purchasingData);
            $poItem = $this->purchaseOrderFactory->forSingleItemManual($singleItemManual);
            $this->dbm->persist($poItem);
            $this->dbm->persist($poItem->getPurchaseOrder());
        }
        // can't create purchase order without $pdId

        $requirementId = $command->getRequirementId();

        if ($requirementId !== null) {
            $requirement = $this->requirementRepo->find($requirementId);
            if ($orderQty > 0) {
                if ($poItem !== null) {
                    $alloc = $requirement->createAllocation($poItem);
                } else {
                    $producer = $this->stockProducerFactory->create($requirement, $orderQty);
                    $this->dbm->persist($producer);
                    $alloc = $requirement->createAllocation($producer);
                }
                $alloc->adjustQuantity($requirement->getTotalQtyUnallocated());
                $this->dbm->persist($alloc);
            }
        }
        // can't allocate item withoud $requirementId

    }
}
