<?php

namespace Rialto\Sales\Order\Orm;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Gumstix\Doctrine\HighLevelQueryBuilder;
use Gumstix\Time\DateRange;
use Rialto\Allocation\Allocation\BinAllocation;
use Rialto\Geography\County\County;
use Rialto\Sales\Order\Allocation\Requirement;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Stock\Facility\Facility;

class SalesOrderQueryBuilder extends HighLevelQueryBuilder
{
    public function __construct(EntityRepository $repo)
    {
        parent::__construct($repo, 'so');

        $this->qb
            ->join('so.customerBranch', 'branch')
            ->join('branch.customer', 'customer')
            ->leftJoin('so.lineItems', 'item')
            ->leftJoin('item.stockItem', 'stockItem')
            ->leftJoin('so.shippingAddress', 'shippingAddress')
            ->leftJoin('so.invoices', 'invoice')
            ->distinct();
    }

    public function byId($id)
    {
        $this->qb->where('so.id = :id')
            ->setParameter('id', $id);
        return $this;
    }

    /**
     * Searches any order number-like field.
     */
    public function byReferenceMatch($ref)
    {
        $this->qb->andWhere('(so.id like :ref ' .
            'or so.customerReference like :ref ' .
            'or so.sourceID like :ref)')
            ->setParameter('ref', "%$ref%");
        return $this;
    }

    public function byCustomer($customer)
    {
        $this->qb->andWhere('customer = :customer')
            ->setParameter('customer', $customer);
        return $this;
    }

    public function byCustomerMatch($value)
    {
        $search = '%' . str_replace(' ', '%', $value) . '%';
        $this->qb->andWhere('
                customer.id = :value
                or customer.name like :search
                or customer.companyName like :search
                or customer.email like :search
                or branch.branchName like :search
                or branch.contactName like :search
                or branch.email like :search
            ');
        $this->qb->setParameter('value', $value);
        $this->qb->setParameter('search', $search);
        return $this;
    }

    public function byShippingAddressMatch($pattern)
    {
        $search = '%' . str_replace(' ', '%', $pattern) . '%';
        $this->qb->andWhere('
                so.deliveryCompany like :shippingAddress
                or so.deliveryName like :shippingAddress
                or shippingAddress.street1 like :shippingAddress
                or shippingAddress.street2 like :shippingAddress
                or shippingAddress.mailStop like :shippingAddress
                or shippingAddress.city like :shippingAddress
                or shippingAddress.stateCode like :shippingAddress
                or shippingAddress.countryCode like :shippingAddress
            ')
            ->setParameter('shippingAddress', $search);
        return $this;
    }

    /**
     * That's counTY, not counTRY.
     */
    public function byCounty($countyName)
    {
        $this->qb->join(County::class, 'county', Join::WITH,
            "shippingAddress.postalCode like concat(county.postalCode, '%')")
            ->andWhere('county.name like :countyName')
            ->setParameter('countyName', "%$countyName%");
        return $this;
    }

    public function bySourceId($sourceId)
    {
        $this->qb->andWhere('so.sourceID = :sourceId')
            ->setParameter('sourceId', $sourceId);
        return $this;
    }

    public function byCreator($creator)
    {
        $this->qb->andWhere('so.createdBy = :creator')
            ->setParameter('creator', $creator);
        return $this;
    }

    /**
     * @deprecated Confirm that no external apps are using this, then remove.
     */
    public function byEdiReference($srcID)
    {
        $this->qb->andWhere('customer.EDIReference = :srcID')
            ->setParameter('srcID', $srcID);
        return $this;
    }

    public function byTaxExemption($exemption)
    {
        $this->qb->andWhere('customer.taxExemptionStatus = :taxExemption')
            ->setParameter('taxExemption', $exemption);
        return $this;
    }

    public function isApprovedToShip()
    {
        $this->qb
            ->andWhere('so.dateToShip is not null')
            ->andWhere('so.dateToShip <= CURRENT_TIMESTAMP()');
        return $this;
    }

    public function hasTargetShipDate()
    {
        $this->qb->andWhere('so.targetShipDate is not null');
        return $this;
    }

    public function doesNotHaveTargetShipDate()
    {
        $this->qb->andWhere('so.targetShipDate is null');
        return $this;
    }

    public function orderByTargetShipDate()
    {
        $this->qb->orderBy('so.targetShipDate', 'asc');
        return $this;
    }

    public function isPrinted()
    {
        $this->qb->andWhere('so.datePrinted is not null');
        return $this;
    }

    public function isNotPrinted()
    {
        $this->qb->andWhere('so.datePrinted is null');
        return $this;
    }

    public function hasAllocation()
    {
        // Find orders....
        $subquery = $this->createSubquery()
            // where there exists...
            ->select('1')
            // a requirement...
            ->from(Requirement::class, 'req')
            // of an item in the order...
            ->andWhere('req.orderItem = item')
            // that has allocations...
            ->join('req.allocations', 'alloc');

        $this->qb
            ->andWhere("exists ({$subquery->getDQL()})");

        return $this;
    }

    public function hasNoAllocation()
    {
        // Find orders....
        $subquery = $this->createSubquery()
            // where there exists...
            ->select('1')
            // a requirement...
            ->from(Requirement::class, 'req')
            // of an item in the order...
            ->andWhere('req.orderItem = item')
            // that has allocations...
            ->join('req.allocations', 'alloc');

        $this->qb
            ->andWhere("not exists ({$subquery->getDQL()})");

        return $this;
    }
    public function isNotComplete()
    {
        $subquery = $this->subqueryOpenItems();
        $this->qb->andWhere("exists($subquery)");
        return $this;
    }

    /**
     * @return string
     */
    private function subqueryOpenItems()
    {
        $subquery = $this->createSubquery()
            ->select('1')
            ->from(SalesOrderDetail::class, 'incompleteItem')
            ->andWhere('incompleteItem.salesOrder = so')
            ->andWhere('incompleteItem.completed = 0');
        return $subquery->getDQL();
    }

    public function isComplete()
    {
        $subquery = $this->subqueryOpenItems();
        $this->qb->andWhere("not exists($subquery)");
        return $this;
    }

    public function isNotFullyPaid()
    {
        $this->qb->leftJoin('so.creditAllocations', 'alloc')
            ->having('sum(ifnull(alloc.amount, 0)) <
                sum(item.finalUnitPrice * item.qtyOrdered)')
            ->groupBy('so.id');
        return $this;
    }

    /**
     * @param string|Facility $location
     */
    public function byLocation($location)
    {
        $this->qb
            ->andWhere('so.shipFromFacility = :location')
            ->setParameter('location', $location);
        return $this;
    }

    public function bySalesType($type)
    {
        $this->qb->andWhere('so.salesType = :salesType')
            ->setParameter('salesType', $type);
        return $this;
    }

    public function bySalesStage($stage)
    {
        $this->qb
            ->andWhere('so.salesStage = :salesStage')
            ->setParameter('salesStage', $stage);
        return $this;
    }

    public function bySku($sku)
    {
        $subquery = $this->createSubquery()
            ->select('1')
            ->from(SalesOrderDetail::class, 'filterItem')
            ->join('filterItem.stockItem', 'filterStockItem')
            ->andWhere('filterItem.salesOrder = so')
            ->andWhere('filterStockItem.stockCode like :sku');
        $this->qb
            ->andWhere("exists ({$subquery->getDQL()})")
            ->setParameter('sku', $sku);
        return $this;
    }

    public function bySkuMatch($sku)
    {
        return $this->bySku("%$sku%");
    }

    public function bySkuArray($skus)
    {
        $subquery = $this->createSubquery()
            ->select('1')
            ->from(SalesOrderDetail::class, 'filterItem')
            ->join('filterItem.stockItem', 'filterStockItem')
            ->andWhere('filterItem.salesOrder = so')
            ->andWhere('filterStockItem.stockCode in (:skus)');

        $this->qb
            ->andWhere("exists ({$subquery->getDQL()})")
            ->setParameter('skus', $skus);
        return $this;
    }

    public function hasPartsInStock()
    {
        // Find orders....
        $subquery = $this->createSubquery()
            // where there exists...
            ->select('1')
            // a requirement...
            ->from(Requirement::class, 'req')
            // of an item in the order...
            ->andWhere('req.orderItem = item')
            // that has allocations...
            ->join('req.allocations', 'alloc')
            // from bins...
            ->join(BinAllocation::class, 'binAlloc', 'WITH', 'alloc = binAlloc')
            ->join('binAlloc.source', 'bin')
            // at this order's shipping location.
            ->andWhere('bin.facility = so.shipFromFacility');

        $this->qb
            ->andWhere("exists ({$subquery->getDQL()})");

        return $this;
    }

    /** @return QueryBuilder */
    private function createSubquery()
    {
        return $this->qb->getEntityManager()->createQueryBuilder();
    }

    public function byDateOrdered(DateRange $range)
    {
        if ($range->hasStart()) {
            // ie where so.dateOrdered >= :dateOrderedStart
            $this->qb->andWhere('date_diff(so.dateOrdered, :dateOrderedStart) >= 0')
                ->setParameter('dateOrderedStart', $range->getStart());
        }
        if ($range->hasEnd()) {
            $this->qb->andWhere('date_diff(so.dateOrdered, :dateOrderedEnd) <= 0')
                ->setParameter('dateOrderedEnd', $range->getEnd());
        }
        return $this;
    }

    public function byDateInvoiced(DateRange $range)
    {
        if ($range->hasStart()) {
            $this->qb->andWhere('date_diff(invoice.date, :dateInvoicedStart) >= 0')
                ->setParameter('dateInvoicedStart', $range->getStart());
        }
        if ($range->hasEnd()) {
            $this->qb->andWhere('date_diff(invoice.date, :dateInvoicedEnd) <= 0')
                ->setParameter('dateInvoicedEnd', $range->getEnd());
        }
        return $this;
    }

    public function prefetchForSalesOrderList()
    {
        $this->qb
            ->addSelect('branch')
            ->addSelect('customer')
            ->addSelect('item')
            ->addSelect('stockItem')
            ->addSelect('shippingAddress')
            ->addSelect('invoice');
        return $this;
    }

    public function orderByPriority()
    {
        $this->qb->orderBy('so.priority', 'desc');
        return $this;
    }

    public function orderById()
    {
        $this->qb->addOrderBy('so.id', 'asc');
        return $this;
    }

    public function orderByCustomer()
    {
        $this->qb->addOrderBy('so.deliveryName', 'asc')
            ->addOrderBy('so.billingName', 'asc')
            ->addOrderBy('customer.name', 'asc')
            ->addOrderBy('customer.companyName', 'asc')
            ->addOrderBy('branch.branchName', 'asc')
            ->addOrderBy('branch.contactName', 'asc');
        return $this;
    }

    public function orderByCustomerReference()
    {
        $this->qb->addOrderBy('so.customerReference', 'asc');
        return $this;
    }

    public function orderByDates()
    {
        $this->qb->addOrderBy('ifnull(so.dateToShip, 0)', 'desc')
            ->addOrderBy('so.dateToShip', 'asc')
            ->addOrderBy('so.dateOrdered', 'asc');
        return $this;
    }

    public function orderByDateOrdered()
    {
        $this->qb->addOrderBy('so.dateOrdered', 'asc');
        return $this;
    }
}
