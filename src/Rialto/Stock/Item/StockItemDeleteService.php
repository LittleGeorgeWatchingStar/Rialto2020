<?php

namespace Rialto\Stock\Item;

use Doctrine\ORM\EntityManagerInterface;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Sales\Price\Orm\ProductPriceRepository;
use Rialto\Sales\Price\ProductPrice;
use Rialto\Stock\Cost\Orm\StandardCostRepository;
use Rialto\Stock\Cost\StandardCost;
use Rialto\Stock\Level\Orm\StockLevelStatusRepository;
use Rialto\Stock\Level\StockLevelStatus;
use Rialto\Stock\Publication\Orm\PublicationRepository;
use Rialto\Stock\Publication\Publication;

class StockItemDeleteService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var PurchasingDataRepository */
    private $purchasingDataRepo;

    /** @var ProductPriceRepository */
    private $productPriceRepo;

    /** @var StandardCostRepository */
    private $standardCostRepo;

    /** @var PublicationRepository */
    private $publicationRepo;

    /** @var StockLevelStatusRepository */
    private $stockLevelRepo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->purchasingDataRepo = $this->em->getRepository(PurchasingData::class);
        $this->productPriceRepo = $this->em->getRepository(ProductPrice::class);
        $this->standardCostRepo = $this->em->getRepository(StandardCost::class);
        $this->publicationRepo = $this->em->getRepository(Publication::class);
        $this->stockLevelRepo = $this->em->getRepository(StockLevelStatus::class);
    }

    public function deleteStockItem(StockItem $stockItem): void
    {
        $this->em->transactional(function () use ($stockItem) {
            $publications = $this->publicationRepo->findAllByItem($stockItem);
            $this->removeItems($publications);

            $standardCosts = $this->standardCostRepo->findAllByItem($stockItem);
            $this->removeItems($standardCosts);

            $productPrices = $this->productPriceRepo->findByStockItem($stockItem);
            $this->removeItems($productPrices);

            $purchasingDatas = $this->purchasingDataRepo->findByItem($stockItem);
            $this->removeItems($purchasingDatas);

            $stockLevels = $this->stockLevelRepo->findBy(['stockItem' => $stockItem]);
            $this->removeItems($stockLevels);

            $this->em->remove($stockItem);
        });
    }

    public function deleteItemVersion(StockItem $stockItem, String $versionCode): void
    {
        $version = $stockItem->getVersion($versionCode);

        $this->em->transactional(function () use ($version) {
            $purchasingDatas = $this->purchasingDataRepo->findByItem($version);
            $this->removeItems($purchasingDatas);

            $this->em->remove($version);
        });
    }

    /**
     * @param object[] $items
     */
    private function removeItems(array $items): void
    {
        foreach ($items as $item) {
            $this->em->remove($item);
        }
    }
}
