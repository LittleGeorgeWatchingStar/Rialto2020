<?php

namespace Rialto\Purchasing\Supplier\Orm;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item;
use Rialto\Web\DomainName;

class SupplierRepository extends FilteringRepositoryAbstract
{
    public function findAll()
    {
        return $this->findBy([], ['name' => 'asc']);
    }

    /**
     * @return Query
     */
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('s');
        $builder->add('name', function (QueryBuilder $qb, $name) {
            $qb->andWhere('s.name like :name')
                ->setParameter('name', $name);
        });
        $builder->add('matching', function (QueryBuilder $qb, $pattern) {
            $pattern = str_replace(' ', '%', $pattern);
            $pattern = "%$pattern%";
            $qb->andWhere('s.name like :pattern')
                ->setParameter('pattern', $pattern);
        });
        return $builder->buildQuery($params);
    }

    /**
     * Returns the first supplier whose name or domain name matches.
     *
     * @param string $search_key
     * @return Supplier|null
     */
    public function findFirstMatching($name, $url)
    {
        $pattern = preg_replace('/\W+/', '%', $name);
        $pattern = "%$pattern%";
        $domainName = DomainName::parse($url);

        $qb = $this->createQueryBuilder('supplier')
            ->where('supplier.name like :pattern')
            ->setParameter('pattern', $pattern)
            ->orWhere('supplier.website like :domainName')
            ->setParameter('domainName', "%$domainName%")
            ->setMaxResults(1);
        $query = $qb->getQuery();
        logDebug($query->getParameters(), $query->getSQL());
        return $query->getOneOrNullResult();
    }

    /** @return Supplier[] */
    public function findByItem(Item $item)
    {
        $qb = $this->selectByItem($item);
        return $qb->getQuery()->getResult();
    }

    /** @return QueryBuilder */
    private function selectByItem(Item $item, $orderQty = 1)
    {
        $qb = $this->createQueryBuilder('supplier')
            ->from(PurchasingData::class, 'pd')
            ->where('pd.supplier = supplier')
            ->innerJoin('pd.costBreaks', 'cost')
            ->where('pd.stockItem = :item')
            ->andWhere(':orderQty >= cost.minimumOrderQty')
            ->orderBy('pd.preferred', 'DESC')
            ->addOrderBy('cost.unitCost', 'ASC')
            ->addOrderBy('cost.manufacturerLeadTime', 'ASC')
            ->addOrderBy('cost.supplierLeadTime', 'ASC')
            ->setParameters([
                'item' => $item->getSku(),
                'orderQty' => $orderQty,
            ]);
        return $qb;
    }

    /** @return QueryBuilder */
    public function queryActiveManufacturers()
    {
        $qb = $this->createQueryBuilder('s');
        $qb->join('s.facility', 'facility')
            ->where('facility.active = 1')
            ->orderBy('s.name', 'asc');
        return $qb;
    }

    /**
     * A query to find suppliers who provide items of the given category.
     *
     * @param string|StockCategory $category
     *
     * @return QueryBuilder
     */
    public function queryByStockCategory($category)
    {
        $qb = $this->createQueryBuilder('supplier');
        $qb->join(PurchasingData::class, 'pd', Join::WITH, 'pd.supplier = supplier')
            ->join('pd.stockItem', 'item')
            ->andWhere('item.category = :category')
            ->setParameter('category', $category)
            ->orderBy('supplier.name', 'asc');
        return $qb;
    }

    /** @return Supplier */
    public function findByAccountNumber($accountNo)
    {
        $qb = $this->createQueryBuilder('supplier');
        $qb->andWhere('supplier.customerAccount like :accountNo or supplier.customerNumber like :accountNo')
            ->setParameter('accountNo', $accountNo);
        return $qb->getQuery()->getSingleResult();
    }
}
