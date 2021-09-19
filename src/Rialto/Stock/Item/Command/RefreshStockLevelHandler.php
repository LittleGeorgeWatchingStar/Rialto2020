<?php


namespace Rialto\Stock\Item\Command;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Exception\InvalidDataException;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Level\Orm\StockLevelStatusRepository;
use Rialto\Stock\Level\StockLevelStatus;
use Rialto\Stock\Level\StockLevelSynchronizer;

final class RefreshStockLevelHandler
{
    /** @var StockItemRepository */
    private $stockItemRepo;

    /** @var StockLevelStatusRepository */
    private $stockLevelStatusRepo;

    /** @var FacilityRepository */
    private $facilityRepo;

    /** @var StockLevelSynchronizer */
    private $synchronizer;

    public function __construct(EntityManagerInterface $em,
                                StockLevelSynchronizer $synchronizer)
    {
        $this->stockLevelStatusRepo = $em->getRepository(StockLevelStatus::class);
        $this->stockItemRepo = $em->getRepository(StockItem::class);
        $this->facilityRepo = $em->getRepository(Facility::class);
        $this->synchronizer = $synchronizer;
    }

    public function handle(RefreshStockLevelCommand $command)
    {
        $sku = $command->getItemSku();

        /** @var PhysicalStockItem $stockItem */
        $stockItem = $this->stockItemRepo->find($sku);

        if ($stockItem === null) {
            throw new InvalidDataException("Stock Item with". $sku . "could not be found.");
        }

        $this->stockLevelStatusRepo->initializeStockLevels($stockItem);

        $location = $this->facilityRepo->getHeadquarters();
        $update = $this->synchronizer->loadUpdate($stockItem, $location);
        $this->synchronizer->synchronize($update);
    }
}
