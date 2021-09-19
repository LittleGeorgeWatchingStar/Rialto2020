<?php

namespace Rialto\Madison\Feature;

use Rialto\Madison\Feature\Repository\StockItemFeatureRepository;
use Rialto\Manufacturing\Bom\BomException;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Customizer;
use Rialto\Stock\Item\CompositeStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Item\Version\VersionException;
use Rialto\Stock\VersionedItem;


/**
 * Calculates the feature set for the given stock item.
 *
 * The feature set is calculated by taking the union of the feature
 * sets of all subcomponents.
 */
final class StockItemFeatureCalculator
{
    /** @var StockItemFeatureRepository */
    private $repo;

    /** @var Customizer */
    private $customizer;

    public function __construct(StockItemFeatureRepository $repo,
                                Customizer $customizer)
    {
        $this->repo = $repo;
        $this->customizer = $customizer;
    }

    /**
     * @param Version $version Defaults to item shipping version if non-specific.
     * @return StockItemFeature[]
     * @throws VersionException
     */
    public function getFeatures(StockItem $item, Version $version): array
    {
        $set = $this->getFeaturesRecursively($item, $version, Customization::empty());
        return $set->getFeatures();
    }

    /**
     * @param Version $version Defaults to item shipping version if non-specific.
     * @return StockItemFeature[]
     * @throws VersionException
     */
    public function getFeaturesWithCode(string $code,
                                        StockItem $item,
                                        Version $version): array
    {
        return array_values(array_filter($this->getFeatures($item, $version),
            function (StockItemFeature $feature) use ($code) {
                return $feature->getFeatureCode() === $code;
            }));
    }

    private function getFeaturesRecursively(StockItem $item,
                                            Version $version,
                                            Customization $customization
    ): StockItemFeatureSet
    {
        $featureSet = StockItemFeatureSet::fromFeatures(
            ...$this->repo->findBySku($item->getSku()));
        return $featureSet->mergeChildSet(
            $this->getInheritedFeaturesRecursively($item, $version, $customization));
    }

    private function getInheritedFeaturesRecursively(StockItem $item,
                                                     Version $version,
                                                     Customization $customization
    ): StockItemFeatureSet
    {
        if (!$version->isSpecified()) {
            $version = $item->getShippingVersion();
        }

        if (!($item instanceof CompositeStockItem)) {
            return StockItemFeatureSet::empty();
        }

            $bom = $this->customizer->generateCustomizedBom(
                $item->getBom($version), $customization);
            if ($bom->isEmpty()) {
                throw new BomException($bom, "$bom is empty");
            }

        try {
            return array_reduce($bom->toArray(),
                function (StockItemFeatureSet $carry, VersionedItem $item) {
                    return $carry->mergeChildSet($this->getFeaturesFromVersionedItem($item));
                }, StockItemFeatureSet::empty());
        } catch (VersionException $ex) {
            throw new BomException($bom, $ex->getMessage(), $ex);
        }
    }

    private function getFeaturesFromVersionedItem(VersionedItem $item): StockItemFeatureSet
    {
        return $this->getFeaturesRecursively($item->getStockItem(),
            $item->getVersion(),
            $item->getCustomization() ?: Customization::empty());
    }

    /**
     * @param Version $version Defaults to item shipping version if non-specific.
     * @return StockItemFeature[]
     * @throws VersionException
     */
    public function getInheritedFeatures(StockItem $item,
                                         Version $version): array
    {

        $set = $this->getInheritedFeaturesRecursively($item,
            $version, Customization::empty());
        return $set->getFeatures();
    }
}
