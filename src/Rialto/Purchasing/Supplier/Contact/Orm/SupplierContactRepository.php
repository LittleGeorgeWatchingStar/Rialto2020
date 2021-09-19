<?php

namespace Rialto\Purchasing\Supplier\Contact\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Category\StockCategory;

class SupplierContactRepository extends RialtoRepositoryAbstract
{
    /**
     * @param Supplier $supp
     * @return SupplierContact[]
     */
    public function findBySupplier(Supplier $supp)
    {
        return $this->findBy([
            'supplier' => $supp->getId(),
            'active' => true,
        ]);
    }

    public function findOrderContacts(Supplier $supp)
    {
        return $this->findBy([
            'supplier' => $supp->getId(),
            'contactForOrders' => 1,
            'active' => true,
        ]);
    }

    public function findKitContacts(Supplier $supp)
    {
        return $this->findBy([
            'supplier' => $supp->getId(),
            'contactForKits' => 1,
            'active' => true,
        ]);
    }

    /**
     * @return QueryBuilder
     */
    public function queryPotentialSuppliers(StockCategory $cat)
    {
        $qb = $this->queryOrderContacts();
        $qb->join('contact.supplier', 'supplier')
            ->join(PurchasingData::class, 'pd',
                Join::WITH, 'pd.supplier = supplier')
            ->join('pd.stockItem', 'item')
            ->andWhere('item.category = :categoryID')
            ->setParameter('categoryID', $cat->getId())
            ->orderBy('supplier.name', 'asc')
            ->addOrderBy('contact.name', 'asc');
        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function queryOrderContactsForSupplier(Supplier $supplier)
    {
        $qb = $this->queryOrderContacts();
        $qb->andWhere('contact.supplier = :supplier')
            ->setParameter('supplier', $supplier)
            ->orderBy('contact.name', 'asc');
        return $qb;
    }

    /** @return QueryBuilder */
    private function queryOrderContacts()
    {
        $qb = $this->createQueryBuilder('contact');
        $qb->andWhere('contact.active = 1');
        $qb->andWhere("contact.email != ''");
        $qb->andWhere('contact.contactForOrders = 1');
        return $qb;
    }
}
