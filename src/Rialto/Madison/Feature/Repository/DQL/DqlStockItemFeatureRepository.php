<?php


namespace Rialto\Madison\Feature\Repository\DQL;


use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rialto\Madison\Feature\Repository\StockItemFeatureRepository;
use Rialto\Madison\Feature\StockItemFeature;
use Rialto\Stock\Item;

/**
 * A DQL implementation of @see StockItemFeatureRepository with a doctrine backend.
 */
final class DqlStockItemFeatureRepository implements StockItemFeatureRepository
{
    /** @var ObjectRepository */
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(StockItemFeature::class);
    }

    /**
     * @inheritDoc
     */
    public function findBySku(string $sku): array
    {
        return $this->repository->findBy([
            'stockItem' => $sku,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findByItem(Item $stockItem): array
    {
        return $this->findBySku($stockItem->getSku());
    }

    /**
     * @inheritDoc
     */
    public function findByFeatureCode(string $featureCode): array
    {
        return $this->repository->findBy([
            'featureCode' => $featureCode,
        ]);
    }
}
