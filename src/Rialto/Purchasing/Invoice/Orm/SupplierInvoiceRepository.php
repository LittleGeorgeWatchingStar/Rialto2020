<?php

namespace Rialto\Purchasing\Invoice\Orm;

use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Supplier\Supplier;

class SupplierInvoiceRepository extends FilteringRepositoryAbstract
{
    public function findByFilters(array $params)
    {
        $query = $this->queryByFilters($params);
        return $query->getResult();
    }

    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('invoice');
        $builder->add('id', function($qb, $id) {
            $qb->andWhere('invoice.id = :id')
                ->setParameter('id', $id);
            return true; // don't process any more filters
        });
        $builder->add('supplier', function($qb, $supplierID) {
            $qb->andWhere('invoice.supplier = :supplierID')
                ->setParameter('supplierID', $supplierID);
        });
        $builder->add('purchaseOrder', function($qb, $poID) {
            $qb->andWhere('invoice.purchaseOrder = :poID')
                ->setParameter('poID', $poID);
        });
        $builder->add('reference', function($qb, $reference) {
            $qb->andWhere('invoice.supplierReference like :reference')
                ->setParameter('reference', "%$reference%");
        });
        $builder->add('since', function($qb, $date) {
            $qb->andWhere('invoice.invoiceDate >= :date')
                ->setParameter('date', $date);
        });
        $builder->add('approved', function($qb, $approved) {
            if ( $approved == 'yes' ) {
                $qb->andWhere('invoice.approved = 1');
            }
            elseif ( $approved == 'no' ) {
                $qb->andWhere('invoice.approved = 0');
            }
        });

        return $builder->buildQuery($params);
    }

    /** @return SupplierInvoice|null */
    public function findBySupplierReference(Supplier $supp, $ref)
    {
        $qb = $this->createQueryBuilder('invoice');
        $qb->andWhere('invoice.supplier = :supplier')
            ->setParameter('supplier', $supp->getId())
            ->andWhere('invoice.supplierReference = :ref')
            ->setParameter('ref', $ref);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /** @return SupplierInvoice[] */
    public function findByPurchaseOrder(PurchaseOrder $po)
    {
        return $this->findBy(['purchaseOrder' => $po->getId()]);
    }

    public function findSupplierInvoicesForOpenPO()
    {
        $qb = $this->createQueryBuilder('supplierInvoice');
        $qb->innerJoin('supplierInvoice.purchaseOrder', 'siOpenPO')
            ->leftJoin('siOpenPO.items', 'poItems')

            ->groupBy('siOpenPO')
            ->andWhere('poItems.dateClosed is null')
            ->andWhere('poItems.qtyOrdered > poItems.qtyReceived');

        return $qb->getQuery()->getResult();
    }
}
