<?php


namespace Rialto\Manufacturing\Bom\Bag;

use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Item\Version\ItemVersion;

/**
 * @see BagFinder
 */
interface BagFinderGateway
{
    public function containsBag(ItemVersion $board): bool;

    /**
     * @return ItemVersion[]
     */
    public function findEligibleBags(): array;

    /**
     * @return ItemVersion[]
     */
    public function findBagsWithMissingDimensions(): array;

    public function getBagWorkType(): WorkType;
}
