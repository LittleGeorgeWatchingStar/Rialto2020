<?php

namespace Rialto\PcbNg\Command;


use Doctrine\ORM\EntityManager;
use Rialto\PcbNg\Service\PcbNgClient;
use Rialto\PcbNg\Service\PcbNgPurchasingDataFactory;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\VersionException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;

class CreateManufacturedStockItemPcbNgPurchasingDataHandler
{
    /** @var EntityManager */
    private $em;

    /** @var StockItemRepository */
    private $stockItemRepo;

    /** @var PurchasingDataRepository */
    private $purchDataRepo;

    /** @var PcbNgPurchasingDataFactory */
    private $pcbNgPurchDataFactory;

    /** @var PcbNgClient */
    private $pcbNgClient;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(EntityManager $em,
                                PcbNgPurchasingDataFactory $pcbNgPurchDataFactory,
                                PcbNgClient $pcbNgClient,
                                ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->stockItemRepo = $em->getRepository(StockItem::class);
        $this->purchDataRepo = $em->getRepository(PurchasingData::class);
        $this->pcbNgPurchDataFactory = $pcbNgPurchDataFactory;
        $this->pcbNgClient = $pcbNgClient;
        $this->validator = $validator;
    }

    /**
     * @return PurchasingData[]
     * @throws InvalidArgumentException
     */
    public function handle(CreateManufacturedStockItemPcbNgPurchasingDataCommand $command): array
    {
        $stockItem = $this->stockItemRepo->find($command->getManufacturedStockItemSku());
        if (!$stockItem || !$stockItem instanceof ManufacturedStockItem) {
            throw new \InvalidArgumentException("Manufactured stock item Not Found.");
        }

        try {
            $itemVersion = $stockItem->getVersion($command->getVersion());
        } catch (VersionException $exception) {
            throw new \InvalidArgumentException("Item version Not Found.");
        }

        $purchDataArray = $this->pcbNgPurchDataFactory->createForBoard($stockItem, $itemVersion);

        foreach ($purchDataArray as $purchasingData) {
            $errors = $this->validator->validate($purchasingData);
            if (count($errors) > 0) {
                throw new \Exception(
                    "Purchasing data created for $stockItem is invalid: $errors");
            }

            $this->em->persist($purchasingData);
        }
        return $purchDataArray;
    }
}
