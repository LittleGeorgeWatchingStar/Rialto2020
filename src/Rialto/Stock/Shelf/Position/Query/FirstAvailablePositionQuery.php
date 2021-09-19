<?php


namespace Rialto\Stock\Shelf\Position\Query;


use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Shelf\ShelfPosition;
use Rialto\Stock\Shelf\Velocity;

/**
 * Query to find the first available @see ShelfPosition for
 * a @see StockBin and @see Velocity
 */
interface FirstAvailablePositionQuery
{
    /** @return ShelfPosition|null */
    public function __invoke(StockBin $bin, Velocity $velocity);
}
