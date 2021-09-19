<?php


namespace Rialto\Madison\Feature;


use Countable;

/**
 * A collection of @see StockItemFeature objects that doesn't allow overriding.
 */
final class StockItemFeatureSet implements Countable
{
    /** @var StockItemFeature[] */
    private $features = [];

    private function __construct()
    {
    }

    public static function empty(): self
    {
        return new self();
    }

    /**
     * Construct a set from a sequence of features.
     *
     * If two or more features with the same code appear in the sequence, the
     * first one will be used in the set and the remaining features with that
     * code will be dropped.
     */
    public static function fromFeatures(StockItemFeature ...$features): self
    {
        $set = new self();

        foreach ($features as $feature) {
            $set->addFeature($feature);
        }

        return $set;
    }

    /**
     * Merge this feature set with a child feature set.
     * Children sets are not allowed to override features in the parent set.
     */
    public function mergeChildSet(StockItemFeatureSet $child): StockItemFeatureSet
    {
        return self::fromFeatures(
            ...$this->getFeatures(),
            ...$child->getFeatures()
        );
    }

    public function count()
    {
        return count($this->features);
    }

    /**
     * @return StockItemFeature[]
     */
    public function getFeatures(): array
    {
        return array_values($this->features);
    }

    private function addFeature(StockItemFeature $feature)
    {
        $code = $feature->getFeatureCode();
        if (!$this->featureExists($feature)) {
            $this->features[$code] = $feature;
        }
    }

    private function featureExists(StockItemFeature $feature): bool
    {
        return (key_exists($feature->getFeatureCode(), $this->features));
    }

}
