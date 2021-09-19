<?php

namespace Rialto\Stock\Transfer\Orm;

use Gumstix\Doctrine\HighLevelQueryBuilder;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\Version\Version;

class TransferItemQueryBuilder extends HighLevelQueryBuilder
{
    public function __construct(TransferItemRepository $repo)
    {
        parent::__construct($repo, 'item');
        $this->qb->join('item.transfer', 'transfer')
            ->leftJoin('item.stockBin', 'bin')
            ->leftJoin('bin.stockItem', 'stockItem');
    }

    public function byBin(StockBin $bin)
    {
        $this->qb->andWhere('item.stockBin = :bin')
            ->setParameter('bin', $bin);

        return $this;
    }

    public function byStockItem(Item $item)
    {
        return $this->bySku($item->getSku());
    }

    public function bySku($sku)
    {
        $this->qb->andWhere('stockItem.stockCode = :sku')
            ->setParameter('sku', $sku);
        return $this;
    }

    public function byVersion(Version $version)
    {
        if ($version->isSpecified()) {
            $this->qb->andWhere('bin.version = :version')
                ->setParameter('version', (string) $version);
        }
        return $this;
    }

    public function sent()
    {
        $this->qb->andWhere('transfer.dateShipped is not null');
        return $this;
    }

    public function unreceived()
    {
        $this->qb->andWhere('item.dateReceived is null');
        return $this;
    }

    public function missing()
    {
        $this->unreceived();
        $this->qb->andWhere('transfer.dateReceived is not null')
            ->andWhere('bin.quantity > 0');

        return $this;
    }

    public function inTransit()
    {
        $this->qb->andWhere('bin.transfer is not null');
        return $this;
    }

    public function notEmpty()
    {
        $this->qb->andWhere('bin.quantity > 0');
        return $this;
    }

    public function toDestination(Facility $destination)
    {
        $this->qb->andWhere('transfer.destination = :destination')
            ->setParameter('destination', $destination);
        return $this;
    }

    public function orderBySku()
    {
        $this->qb->orderBy('stockItem.stockCode', 'asc');
        return $this;
    }

    public function transferNotRecieved()
    {
        $this->qb->andWhere('transfer.dateReceived is null');
        return $this;
    }
}
