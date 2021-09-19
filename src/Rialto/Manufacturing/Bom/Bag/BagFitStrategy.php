<?php

namespace Rialto\Manufacturing\Bom\Bag;

use Rialto\Measurement\Dimensions;
use Rialto\Stock\Item\Version\ItemVersion;

/**
 * A strategy for finding a bag into which an item will fit.
 */
class BagFitStrategy
{
    /* How much bigger than the board must the bag be in each dimension? */
    const FIT_TOLERANCE = 1.0;

    /**
     * Finds the bag that is the best fit for the given dimensions.
     *
     * Returns null if none of the bags fit.
     *
     * @param ItemVersion[] $possibleBags
     * @param Dimensions $boardDim
     * @return ItemVersion|null
     */
    public function findClosestFit(array $possibleBags, Dimensions $boardDim)
    {
        $bestScore = PHP_INT_MAX; // lower is better
        $bestBag = null;
        foreach ($possibleBags as $bag) {
            $score = $this->getFitScore($bag->getDimensions(), $boardDim);
            if ($score < $bestScore) {
                $bestScore = $score;
                $bestBag = $bag;
            }
        }
        return $bestBag;
    }

    /**
     * The score indicates how well the bag fits the board.
     * @return int|float
     */
    private function getFitScore(Dimensions $bag, Dimensions $board)
    {
        /* Test all possible rotations of the board */
        $bestScore = PHP_INT_MAX; // lower is better

        /* For bags, we only care about the x and y axes. */
        /* Loop over all valid permutations */
        foreach ([true, false] as $reversed) {
            $xAxis = $reversed ? 'x' : 'y';
            $yAxis = $reversed ? 'y' : 'x';

            $dx = $bag->x - $board->$xAxis - self::FIT_TOLERANCE;
            if ($dx < 0) continue; // too small

            $dy = $bag->y - $board->$yAxis - self::FIT_TOLERANCE;
            if ($dy < 0) continue; // too small

            $score = $dx + $dy;
            if ($score < $bestScore) {
                $bestScore = $score;
            }
        }
        return $bestScore;
    }

}
