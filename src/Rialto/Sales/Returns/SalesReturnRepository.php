<?php

namespace Rialto\Sales\Returns;

use Doctrine\ORM\QueryBuilder;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Sales\Order\SalesOrder;

class SalesReturnRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('sr');
        $builder
            ->leftJoinAndSelect('sr.lineItems', 'li')
            ->leftJoinAndSelect('sr.originalInvoice', 'invoice')
            ->leftJoinAndSelect('invoice.customer', 'customer')
            ->leftJoinAndSelect('invoice.salesOrder', 'origOrder')
            ->leftJoinAndSelect('sr.replacementOrder', 'repOrder');

        $builder->add('id', function(QueryBuilder $qb, $id) {
            $qb->where('sr.id like :id')
                ->setParameter('id', $id);
            return true;
        });

        $builder->add('customer', function(QueryBuilder $qb, $customer){
            $qb->andWhere('customer = :customer')
                ->setParameter('customer', $customer);
        });

        $builder->add('status', function(QueryBuilder $qb, $status) {
            switch ($status) {
                case 'receive':
                    $qb->andWhere('li.qtyAuthorized > li.qtyReceived');
                    break;
                case 'test':
                    $qb->andWhere('li.qtyReceived > (li.qtyPassed + li.qtyFailed)');
                    break;
                case 'tested':
                    $qb->leftJoin('repOrder.lineItems', 'si')
                        ->andWhere('IFNULL(si.completed, 0) = 0')
                        ->andWhere('(li.qtyPassed + li.qtyFailed) > 0');
                    break;
                default:
                    return;
            }
        });

        $builder->add('reworkOrder', function(QueryBuilder $qb, $woID) {
            $qb->where('li.reworkOrder = :reworkID')
                ->setParameter('reworkID', $woID);
        });

        $builder->add('stockItem', function(QueryBuilder $qb, $stockItem) {
            $qb->join('li.originalStockMove', 'move')
                ->andWhere('move.stockItem = :stockItem')
                ->setParameter('stockItem', $stockItem);
        });

        $builder->add('startDate', function(QueryBuilder $qb, $startDate) {
            $qb->andWhere('sr.dateAuthorized >= :startDate')
                ->setParameter('startDate', $startDate);
        });

        $builder->add('endDate', function(QueryBuilder $qb, $endDate) {
            $date = new \DateTime($endDate);
            $date->setTime(23, 59, 59);
            $qb->andWhere('sr.dateAuthorized <= :endDate')
                ->setParameter('endDate', $date);
        });

        $builder->add('_order', function (QueryBuilder $qb) {
            $qb->orderBy('sr.dateAuthorized', 'desc');
        });

        return $builder->buildQuery($params);
    }

    /** @return SalesReturn|null */
    public function findExisting(DebtorInvoice $invoice)
    {
        return $this->findOneBy(['originalInvoice' => $invoice->getId()]);
    }

    /**
     * The SalesReturn that generated the given replacement order.
     *
     * @return SalesReturn|null|object
     */
    public function findOneByReplacementOrder(SalesOrder $order)
    {
        return $this->findOneBy(['replacementOrder' => $order->getId()]);
    }

    /**
     * @return int The number of sales returns with some amount left to receive.
     */
    public function countUnreceived()
    {
        $sql = "select count(distinct rma.id)
            from SalesReturn rma
            join SalesReturnItem item
            on item.salesReturn = rma.id
            where item.qtyAuthorized > item.qtyReceived";
        return $this->executeCount($sql);
    }

    private function executeCount($sql)
    {
        $stmt = $this->_em->getConnection()->executeQuery($sql);
        return (int) $stmt->fetchColumn();
    }

    public function countNeedTesting()
    {
        $sql = "select count(distinct rma.id)
            from SalesReturn rma
            join SalesReturnItem item
            on item.salesReturn = rma.id
            where item.qtyReceived > (item.qtyPassed + item.qtyFailed)";
        return $this->executeCount($sql);
    }

    public function countTested()
    {
        $sql = "select count(distinct rma.id)
            from SalesReturn rma
            join SalesReturnItem item
            on item.salesReturn = rma.id
            left join SalesOrders so
            on rma.replacementOrder = so.OrderNo
            left join SalesOrderDetails si
            on so.OrderNo = si.OrderNo
            where (item.qtyPassed + item.qtyFailed) > 0
            and ifnull(si.Completed, 0) = 0";
        return $this->executeCount($sql);
    }
}
