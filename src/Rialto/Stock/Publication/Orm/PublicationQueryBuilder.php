<?php

namespace Rialto\Stock\Publication\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Gumstix\Doctrine\HighLevelQueryBuilder;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Stock\Item;
use Rialto\Stock\Publication\UploadPublication;
use Rialto\Stock\Publication\UrlPublication;

class PublicationQueryBuilder extends HighLevelQueryBuilder
{
    public function __construct(PublicationRepository $repo)
    {
        parent::__construct($repo, 'pub');
    }

    public function byItem(Item $item)
    {
        $this->qb->andWhere('pub.stockItem = :item')
            ->setParameter('item', $item->getSku());

        return $this;
    }

    public function isUrl()
    {
        $class = UrlPublication::class;
        $this->qb->andWhere("pub instance of $class");

        return $this;
    }

    public function isUpload()
    {
        $class = UploadPublication::class;
        $this->qb->andWhere("pub instance of $class");

        return $this;
    }

    public function byPurpose($purpose)
    {
        $this->qb->andWhere('pub.purpose = :purpose')
            ->setParameter('purpose', $purpose);

        return $this;
    }

    public function byDescription($description)
    {
        $this->qb->andWhere('pub.description = :description')
            ->setParameter('description', $description);

        return $this;
    }

    public function bySalesOrder(SalesOrder $order)
    {
        $this->qb
            ->join('pub.stockItem', 'item')
            ->join(SalesOrderDetail::class, 'li', Join::WITH,
                'li.stockItem = item')
            ->andWhere('li.salesOrder = :salesOrder')
            ->setParameter('salesOrder', $order);

        return $this;
    }
}
