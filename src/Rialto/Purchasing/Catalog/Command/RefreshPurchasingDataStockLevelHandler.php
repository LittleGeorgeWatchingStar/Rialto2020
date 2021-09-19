<?php


namespace Rialto\Purchasing\Catalog\Command;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Exception\InvalidDataException;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Catalog\PurchasingDataSynchronizer;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Level\Orm\StockLevelStatusRepository;
use Rialto\Stock\Level\StockLevelStatus;

final class RefreshPurchasingDataStockLevelHandler
{
    /** @var PurchasingDataRepository */
    private $purchasingDataRepo;

    /** @var StockLevelStatusRepository */
    private $stockLevelStatusRepo;

    /** @var PurchasingDataSynchronizer */
    private $synchronizer;

    public function __construct(EntityManagerInterface $em, PurchasingDataSynchronizer $synchronizer)
    {
        $this->stockLevelStatusRepo = $em->getRepository(StockLevelStatus::class);
        $this->purchasingDataRepo = $em->getRepository(PurchasingData::class);
        $this->synchronizer = $synchronizer;
    }

    public function handle(RefreshPurchasingDataStockLevelCommand $command)
    {
        $pdid = $command->getPurchasingDataId();

        $updateStockLevelOnly = $command->getUpdateStockLevelOnly();

        /** @var PurchasingData|null $purchasingData */
        $purchasingData = $this->purchasingDataRepo->find($pdid);

        if ($purchasingData === null) {
            throw new InvalidDataException("Purchasing Data \'$pdid\' found.");
        }

        /**
         * TODO: Formalize this a bit better for future maintenance, we do not
         * want to automatically update cost breakdowns for Digikey as Octopart
         * does not know about the favourable rates we have with them.
         */
        if ($updateStockLevelOnly || $purchasingData->getSupplierName() === 'Digikey') {
            $message = $this->synchronizer->updateStockLevel($purchasingData);
        } else {
            $message = $this->synchronizer->updateAllFields($purchasingData);
        }

        /*
         * TODO: This shouldn't be handled by a response string, but it's the
         * easiest way of exposing this data to Sentry when run asynchronously
         * right now.
         */
        if ($message) {
            throw new \RuntimeException($message);
        }
    }
}
