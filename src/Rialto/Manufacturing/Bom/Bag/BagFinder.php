<?php

namespace Rialto\Manufacturing\Bom\Bag;


use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\Version\ItemVersion;

/**
 * Automatically finds a bag that will fit a board and adds it to the BOM,
 * if needed.
 */
class BagFinder
{
    /** @var BagFinderGateway */
    private $gateway;

    /** @var BagFitStrategy */
    private $fitStrategy;

    public function __construct(BagFinderGateway $gateway)
    {
        $this->gateway = $gateway;
        $this->fitStrategy = new BagFitStrategy();
    }

    public function isBagNeeded(ItemVersion $parent)
    {
        return $parent->isCategory(StockCategory::BOARD) &&
            (! $this->containsBag($parent));
    }

    /** @return bool */
    private function containsBag(ItemVersion $parent)
    {
        return $this->gateway->containsBag($parent);
    }

    /** @return ItemVersion|null */
    public function findMatchingBag(ItemVersion $board)
    {
        $dimensions = $board->getDimensions();
        assert($dimensions);
        $possibleBags = $this->gateway->findEligibleBags();

        return $this->fitStrategy->findClosestFit($possibleBags, $dimensions);
    }

    public function addBagToBom(ItemVersion $board, ItemVersion $bag)
    {
        assertion($board->isCategory(StockCategory::BOARD));

        $bomItem = new BomItem($bag->getStockItem());
        $bomItem->setQuantity(1);
        $bomItem->setWorkType($this->gateway->getBagWorkType());
        $board->addBomItem($bomItem);
    }

    /**
     * @return ItemVersion[]
     */
    public function getBagsWithMissingDimensions()
    {
        return $this->gateway->findBagsWithMissingDimensions();
    }
}
