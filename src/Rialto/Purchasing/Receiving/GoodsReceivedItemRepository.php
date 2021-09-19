<?php

namespace Rialto\Purchasing\Receiving;

use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Purchasing\Invoice\SupplierInvoiceItem;
use Rialto\Purchasing\Producer\StockProducer;

class GoodsReceivedItemRepository extends RialtoRepositoryAbstract
{
    /** @return QueryBuilder */
    public function queryBySupplierInvoiceItem(SupplierInvoiceItem $item)
    {
        $qb = $this->createQueryBuilder('gi');
        $qb->join('gi.producer', 'producer')
            ->andWhere('gi.invoiceItem is null')
            ->andWhere('producer.purchaseOrder = :po')
            ->setParameter('po', $item->getPurchaseOrderNumber());

        if ( $item->getSku() ) {
            $qb->join('producer.purchasingData', 'pd')
                ->andWhere('pd.stockItem = :item')
                ->setParameter('item', $item->getSku());
        }

        return $qb;
    }

    /** @return GoodsReceivedItem[] */
    public function findBySupplierInvoiceItem(SupplierInvoiceItem $item)
    {
        $qb = $this->queryBySupplierInvoiceItem($item);
        return $qb->getQuery()->getResult();
    }

    /** @return boolean */
    public function hasGrns(StockProducer $producer)
    {
        $qb = $this->createQueryBuilder('gi');
        $qb->select('count(gi.id)')
            ->where('gi.producer = :producerID')
            ->setParameter('producerID', $producer->getId());
        return $qb->getQuery()->getSingleScalarResult() > 0;
    }

}
