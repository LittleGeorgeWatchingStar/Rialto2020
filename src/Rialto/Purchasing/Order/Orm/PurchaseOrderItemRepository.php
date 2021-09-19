<?php

namespace Rialto\Purchasing\Order\Orm;

use InvalidArgumentException;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Purchasing\Producer\Orm\StockProducerRepository;

class PurchaseOrderItemRepository extends StockProducerRepository
{
    /**
     * True if the same item and version has been ordered from the same
     * supplier before.
     *
     * @return boolean
     */
    public function hasBeenOrderedBefore(PurchaseOrderItem $poItem)
    {
        if (! $poItem->isStockItem() ) {
            return false;
        }
        if (! $poItem->hasSupplier() ) {
            throw new InvalidArgumentException("PO item has no supplier");
        }

        $supplier = $poItem->getSupplier();
        $qb = $this->createQueryBuilder('poItem');
        $qb->select('count(poItem.id)')
            ->join('poItem.purchaseOrder', 'po')
            ->join('poItem.purchasingData', 'pd')
            ->where('po.supplier = :supplier')
            ->setParameter('supplier', $supplier)
            ->andWhere('pd.stockItem = :stockCode')
            ->setParameter('stockCode', $poItem->getSku())
            ->andWhere('poItem.version = :version')
            ->setParameter('version', (string) $poItem->getVersion())
            ->andWhere('poItem.qtyInvoiced > 0');

        $count = (int) $qb->getQuery()->getSingleScalarResult();
        return $count > 0;
    }
}
