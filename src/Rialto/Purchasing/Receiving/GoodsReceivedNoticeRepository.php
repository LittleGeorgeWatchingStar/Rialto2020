<?php

namespace Rialto\Purchasing\Receiving;

use Doctrine\ORM\QueryBuilder;
use Rialto\Accounting\AccountingEventRepository;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Purchasing\Order\PurchaseOrder;

class GoodsReceivedNoticeRepository
extends FilteringRepositoryAbstract
implements AccountingEventRepository
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('grn');
        $builder->join('grn.purchaseOrder', 'po');

        $builder->add('supplier', function(QueryBuilder $qb, $supplierID) {
            $qb->andWhere('po.supplier = :supplierID')
                ->setParameter('supplierID', $supplierID);
        });

        $builder->add('invoiced', function(QueryBuilder $qb, $invoiced) {
            if ( $invoiced == 'no' ) {
                $qb->join('grn.items', 'grnItem')
                    ->andWhere('grnItem.qtyInvoiced < grnItem.qtyReceived')
                    /* Only include GRNs with an uninvoiced inventory entry
                     * to eliminate false positives from work order wastage. */
                    ->join(GLEntry::class, 'entry',
                        'WITH', 'grn.id = entry.systemTypeNumber')
                    ->andWhere('entry.systemType = :sysTypeID')
                    ->setParameter('sysTypeID', SystemType::PURCHASE_ORDER_DELIVERY)
                    ->andWhere('entry.account = :accountID')
                    ->setParameter('accountID', GLAccount::UNINVOICED_INVENTORY)
                    ->distinct();
            }
            elseif ( $invoiced == 'partial' ) {
                $qb->join('grn.items', 'grnItem')
                    ->andWhere('grnItem.qtyInvoiced > 0')
                    ->andWhere('grnItem.qtyInvoiced < grnItem.qtyReceived')
                    ->distinct();
            }
        });

        $builder->add('startDate', function(QueryBuilder $qb, $startDate) {
            $qb->andWhere('grn.date >= :startDate')
                ->setParameter('startDate', $startDate);
        });
        $builder->add('endDate', function(QueryBuilder $qb, $endDate) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);
            $qb->andWhere('grn.date <= :endDate')
                ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'));
        });

        return $builder->buildQuery($params);
    }

    public function findByPurchseOrder(PurchaseOrder $po)
    {
        return $this->findBy(
            ['purchaseOrder' => $po->getId()],
            ['date' => 'ASC']
        );
    }

    public function findByType(SystemType $sysType, $typeNo)
    {
        return $this->findBy(['id' => $typeNo]);
    }

}
