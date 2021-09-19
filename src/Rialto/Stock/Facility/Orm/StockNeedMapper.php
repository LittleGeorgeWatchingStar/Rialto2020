<?php

namespace Rialto\Stock\Facility\Orm;

use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Facility\StockNeed;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;

/**
 * Database mapper for StockNeed.
 *
 * @see StockNeed
 */
class StockNeedMapper
{
    /** @var StockItemRepository */
    private $repo;

    public function __construct(DbManager $dbm)
    {
        $this->repo = $dbm->getRepository(StockItem::class);
    }

    /**
     * Returns a list of stock needs for purchased components.
     * @return StockNeed[]
     */
    public function fetchNeedsToPurchase()
    {
        return $this->fetchNeeds(StockItem::PURCHASED);
    }

    /**
     * Returns a list of stock needs for manufactured items.
     * @return StockNeed[]
     */
    public function fetchNeedsToBuild()
    {
        return $this->fetchNeeds(StockItem::MANUFACTURED);
    }

    private function fetchNeeds($mbFlag)
    {
        $needs = [];
        foreach ( $this->repo->findStockNeeds($mbFlag) as $result ) {
            $item = $this->repo->find($result['stockCode']);
            $needs[] = new StockNeed($item,
                $result['inStock'],
                $result['onOrder'],
                $result['allocated'],
                $result['orderPoint']);
        }
        return $needs;
    }
}
