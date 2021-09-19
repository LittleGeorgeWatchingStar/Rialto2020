<?php

namespace Rialto\Stock\Item;
use Rialto\Stock\Item\Version\ItemVersion;

/**
 * Finds which container is the closest fit for an ItemVersion.
 */
class FitFinder
{
    const DEFAULT_TOLERANCE = 1.0;

    private $tolerance;
    private $dimensions;

    public function __construct($tolerance = self::DEFAULT_TOLERANCE, $dimensions = 3)
    {
        $this->tolerance = $tolerance;
        $this->dimensions = $dimensions;
    }

    /**
     * @param ItemVersion[] $containers
     * @param ItemVersion $containee
     * @return ItemVersion
     */
    public function findClosestFit(array $containers, ItemVersion $containee)
    {
        $best = null;
        $bestScore = new Score(null);
        foreach ($containers as $container) {
            if ($container->canContain($containee, $this->tolerance, $this->dimensions)) {
                $score = $this->getScore($container, $containee);
                if ($score->isBetterThan($bestScore)) {
                    $best = $container;
                    $bestScore = $score;
                }
            }
        }
        return $best;
    }

    /** @return Score */
    private function getScore(ItemVersion $container, ItemVersion $containee)
    {
        return new Score(
            $container->getVolume() - $containee->getVolume());
    }
}

class Score
{
    private $score;

    public function __construct($score)
    {
        $this->score = $score;
    }

    public function isBetterThan(Score $other)
    {
        if ( $this->score === null ) { return false; }
        if ( $other->score === null ) { return true; }
        return $this->score < $other->score;
    }
}