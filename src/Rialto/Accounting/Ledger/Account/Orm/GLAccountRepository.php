<?php

namespace Rialto\Accounting\Ledger\Account\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Rialto\Accounting\Ledger\Account\AccountGroup;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Purchasing\Invoice\SupplierInvoiceItem;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Purchasing\Supplier\Supplier;

class GLAccountRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('a');

        $builder->add('id', function(QueryBuilder $qb, $id) {
            $qb->where('a.id like :id');
            $qb->setParameter('id', $id);
            return true;
        });
        $builder->add('matching', function(QueryBuilder $qb, $matching) {
            $matching = str_replace(' ', '%', $matching);
            $matching = "%$matching%";
            $qb->where('a.id like :matching or a.name like :matching');
            $qb->setParameter('matching', $matching);
        });

        return $builder->buildQuery($params);
    }

    public function findSalesAdjustments()
    {
        return $this->findByGroup('Sales Adjustments');
    }

    /** @return QueryBuilder */
    public function querySalesAdjustments()
    {
        return $this->queryByGroup(AccountGroup::SALES_ADJUSTMENTS);
    }

    /** @return QueryBuilder */
    public function queryByGroup($group)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->where('a.accountGroup = :groupName')
            ->setParameter('groupName', $group);
        return $qb;
    }

    /**
     * Returns a list of all accounts in the given group.
     *
     * @param string $group
     * @return array
     *  A list of GLAccount objects.
     */
    public function findByGroup($group)
    {
        return $this->findBy([
            'accountGroup' => $group
        ]);
    }

    /** @return QueryBuilder */
    public function queryValidAccountsForPurchaseOrderItem()
    {
        $qb = $this->createQueryBuilder('gla')
            ->distinct()
            ->from(PurchaseOrderItem::class, 'detail')
            ->where('detail.glAccount = gla')
            ->orderBy('gla.id', 'asc');
        return $qb;
    }

    /**
     * The accounts that are most commonly used for invoice items from
     * $supplier.
     *
     * @see SupplierInvoiceItem
     * @return GLAccount[]
     */
    public function findCommonInvoiceAccounts(Supplier $supplier)
    {
        $qb = $this->createQueryBuilder('account');
        $qb->join(SupplierInvoiceItem::class, 'item',
                Join::WITH, 'item.glAccount = account')
            ->join('item.supplierInvoice', 'invoice')
            ->where('invoice.supplier = :supplierID')
            ->setParameter('supplierID', $supplier->getId())
            ->addSelect('count(item.id) as HIDDEN freq')
            ->groupBy('account.id')
            ->having('freq > 0')
            ->orderBy('freq', 'desc')
            ->addOrderBy('account.id', 'asc')
            ->setMaxResults(10);
        $query = $qb->getQuery();
        return $query->getResult();
    }

    /** @return QueryBuilder */
    public function queryCreditCardAccounts()
    {
        $accounts = [
            GLAccount::fetchAuthorizeNet()->getId()
        ];
        $qb = $this->createQueryBuilder('gla');
        $qb->where($qb->expr()->in('gla', ':accounts'))
            ->setParameter('accounts', $accounts);
        return $qb;
    }

    /** @return QueryBuilder */
    public function queryProfitAndLoss()
    {
        $qb = $this->createQueryBuilder('gla')
            ->innerJoin('gla.accountGroup', 'g')
            ->where('g.profitAndLoss = 1')
            ->orderBy('gla.id');
        return $qb;
    }

    /** @return QueryBuilder */
    public function queryNonProfitAndLoss()
    {
        $qb = $this->createQueryBuilder('gla')
            ->innerJoin('gla.accountGroup', 'g')
            ->where('g.profitAndLoss = 0')
            ->orderBy('gla.id');
        return $qb;
    }
}
