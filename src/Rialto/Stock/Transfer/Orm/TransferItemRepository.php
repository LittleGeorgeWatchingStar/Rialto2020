<?php

namespace Rialto\Stock\Transfer\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Stock\Transfer\TransferItem;

class TransferItemRepository extends FilteringRepositoryAbstract
{
    public function findByFilters(array $params)
    {
        $query = $this->queryByFilters($params);
        return $query->getResult();
    }

    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('item');
        $builder->join('item.transfer', 'transfer');
        $builder->join('item.stockBin', 'bin');

        $builder->add('transfer', function(QueryBuilder $qb, $transferId) {
            $qb->andWhere('transfer.id = :transferId')
                ->setParameter('transferId', $transferId);
        });
        $builder->add('since', function(QueryBuilder $qb, $since) {
            $qb->andWhere('transfer.dateShipped >= :since')
                ->setParameter('since', $since);
        });
        $builder->add('received', function(QueryBuilder $qb, $received) {
            if ( $received == 'no' ) {
                $qb->andWhere('item.dateReceived is null');
            } else {
                $qb->andWhere('item.dateReceived is not null');
            }
        });
        $builder->add('missing', function(QueryBuilder $qb, $missing) {
            if ( $missing == 'yes' ) {
                $qb->andWhere('transfer.dateReceived is not null')
                    ->andWhere('bin.transfer = transfer');
            }
        });
        $builder->add('purchaseOrder', function(QueryBuilder $qb, $poId) {
            /** @var $repo TransferRepository */
            $repo = $qb->getEntityManager()->getRepository(Transfer::class);
            $repo->selectByPurchaseOrderId($qb, $poId, 'transfer', 'bin');
        });
        $builder->add('stockBin', function(QueryBuilder $qb, $binId) {
            $qb->andWhere('item.stockBin = :binId')
                ->setParameter('binId', $binId);
        });

        return $builder->buildQuery($params);
    }

    /** @return TransferItemQueryBuilder */
    public function createBuilder()
    {
        return new TransferItemQueryBuilder($this);
    }

    /** @return TransferItem[] */
    public function findTransferItembyStockItem(StockItem $stockItem)
    {
        return $this->createBuilder()
                    ->byStockItem($stockItem)
                    ->inTransit()
                    ->unreceived()
                    ->transferNotRecieved()
                    ->getQuery()
                    ->getResult();
    }
}
