<?php


namespace Rialto\Madison\Feature\Repository;


use Rialto\Madison\Feature\StockItemFeature;
use Rialto\Madison\Feature\StockItemFeatureCalculator;
use Rialto\Stock\Item;

/**
 * A repository for fetching @see StockItemFeature objects from a generic store.
 */
interface StockItemFeatureRepository
{
    /**
     * Find all explicit features assigned to an Item with the given SKU.
     * @see StockItemFeatureCalculator to include inherrited features.
     * @return StockItemFeature[]
     */
    public function findBySku(string $sku): array;

    /**
     * Find all explicit features assigned to an Item.
     * @see StockItemFeatureCalculator to include inherrited features.
     * @return StockItemFeature[]
     */
    public function findByItem(Item $stockItem): array;

    /**
     * Find all features for a given code, e.g. `manufacturer`.
     * @return StockItemFeature[]
     */
    public function findByFeatureCode(string $featureCode): array;
}
