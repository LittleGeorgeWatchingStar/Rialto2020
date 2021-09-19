<?php

namespace Rialto\Sales\Order\Orm;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use Gumstix\Doctrine\HighLevelFilter;
use Gumstix\Time\DateRange;
use Rialto\Accounting\Bank\Statement\BankStatement;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Type\SalesType;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;

class SalesOrderRepository extends FilteringRepositoryAbstract
{
    public function createBuilder()
    {
        return new SalesOrderQueryBuilder($this);
    }

    public function queryByFilters(array $params)
    {
        $queryBuilder = $this->createBuilder();
        $queryBuilder->prefetchForSalesOrderList();
        $filter = new HighLevelFilter($queryBuilder);

        $filter->add('id', function (SalesOrderQueryBuilder $qb, $id) {
            $qb->byId($id);
            return true; // don't process any other filters.
        });

        $filter->add('reference', function (SalesOrderQueryBuilder $qb, $ref) {
            $qb->byReferenceMatch($ref);
        });

        /* Finds orders by the ID of the external application that generated
         * the customer record (eg, the e-commerce storefront). */
        $filter->add('customerEdiReference', function (SalesOrderQueryBuilder $qb, $srcID) {
            $qb->byEdiReference($srcID);
        });

        $filter->add('type', function (SalesOrderQueryBuilder $qb, $type) {
            $qb->bySalesType($type);
        });
        $filter->add('salesStage', function (SalesOrderQueryBuilder $qb, $stage) {
            $qb->bySalesStage($stage);
        });
        $filter->add('customer', function (SalesOrderQueryBuilder $qb, $value) {
            $qb->byCustomerMatch($value);
        });
        $filter->add('customerRef', function (SalesOrderQueryBuilder $qb, $ref) {
            $qb->byReferenceMatch($ref);
        });
        $filter->add('taxExemption', function (SalesOrderQueryBuilder $qb, $exemption) {
            $qb->byTaxExemption($exemption);
        });

        $filter->add('source', function (SalesOrderQueryBuilder $qb, $sourceId) {
            $qb->bySourceId($sourceId);
        });

        $filter->add('item', function (SalesOrderQueryBuilder $qb, $sku) {
            $qb->bySkuMatch($sku);
        });
        $filter->add('startDate', function (SalesOrderQueryBuilder $qb, $startDate) {
            $range = DateRange::create()->withStart($startDate);
            $qb->byDateOrdered($range);
        });
        $filter->add('endDate', function (SalesOrderQueryBuilder $qb, $endDate) {
            $range = DateRange::create()->withEnd($endDate);
            $qb->byDateOrdered($range);
        });

        $filter->add('printed', function (SalesOrderQueryBuilder $qb, $printed) {
            if ($printed == 'yes') {
                $qb->isPrinted();
            } elseif ($printed == 'no') {
                $qb->isNotPrinted();
            }
        });

        $filter->add('shipped', function (SalesOrderQueryBuilder $qb, $shipped) {
            switch ($shipped) {
                case 'yes':
                    $qb->isComplete();
                    break;
                case 'any':
                    break;
                default:
                    $qb->isNotComplete();
                    break;
            }
        });

        $filter->add('allocated', function (salesOrderQueryBuilder $qb, $allocated){
            if ($allocated == 'yes') {
                $qb->hasAllocation();
            } elseif ($allocated == 'no') {
                $qb->hasNoAllocation();
            }
        });


        $filter->add('invoiceStartDate', function (SalesOrderQueryBuilder $qb, $date) {
            $range = DateRange::create()->withStart($date);
            $qb->byDateInvoiced($range);
        });
        $filter->add('invoiceEndDate', function (SalesOrderQueryBuilder $qb, $date) {
            $range = DateRange::create()->withEnd($date);
            $qb->byDateInvoiced($range);
        });

        $filter->add('shippingAddress', function (SalesOrderQueryBuilder $qb, $pattern) {
            $qb->byShippingAddressMatch($pattern);
        });

        $filter->add('county', function (SalesOrderQueryBuilder $qb, $countyName) {
            $qb->byCounty($countyName);
        });

        $filter->add('_order', function (SalesOrderQueryBuilder $qb, $orderBy) {
            $qb->orderByPriority();
            switch ($orderBy) {
                case 'id':
                    $qb->orderById();
                    break;
                case 'customer':
                    $qb->orderByCustomer();
                    break;
                case 'customerRef':
                    $qb->orderByCustomerReference();
                    break;
                default:
                    $qb->orderByDates();
                    break;
            }

            $qb->orderByDateOrdered();
        });

        return $filter->buildQuery($params);
    }

    public function findMatchingOrdersForBankStatement(BankStatement $statement)
    {
        if (!$statement->isDeposit()) {
            return [];
        }
        $customerName = $statement->getCustomerName();
        if (!$customerName) {
            return [];
        }
        $customerName = $this->prepCustomerNameForMatching($customerName);

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(SalesOrder::class, 'so');
        $rsm->addIndexBy('so', 'id');

        $sql = "SELECT DISTINCT so.* FROM SalesOrders so
            JOIN SalesOrderDetails sod ON so.OrderNo = sod.OrderNo
            JOIN CustBranch branch ON so.branchID = branch.id
            JOIN DebtorsMaster cust ON branch.DebtorNo = cust.DebtorNo
            WHERE so.OrderType = :type
            AND sod.QtyInvoiced = 0
            AND sod.Completed = 0
            AND (cust.Name REGEXP :customerName
                OR cust.CompanyName REGEXP :customerName
                OR so.CompanyName REGEXP :customerName
                OR so.DeliverTo REGEXP :customerName
                OR so.BuyerName REGEXP :customerName)
            ORDER BY so.OrdDate DESC";
        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameters([
            'customerName' => $customerName,
            'type' => SalesType::DIRECT,
        ]);

        return $query->getResult();
    }

    private function prepCustomerNameForMatching($name)
    {
        $words = Customer::getKeywordsFromName($name);
        return join('|', $words);
    }

    /** @return QueryBuilder */
    public function queryByCustomer(Customer $customer)
    {
        return $this->createBuilder()
            ->byCustomer($customer)
            ->getQueryBuilder();
    }

    /** @return QueryBuilder */
    public function queryUnpaidOrdersByCustomer(Customer $customer)
    {
        return $this->createBuilder()
            ->byCustomer($customer)
            ->isNotFullyPaid()
            ->getQueryBuilder();
    }

    /** @return boolean */
    public function orderAlreadyExists(User $creator, $sourceID)
    {
        $qb = $this->createQueryBuilder('so');
        $qb->select('count(so.id)')
            ->where('so.createdBy = :creator')
            ->setParameter('creator', $creator)
            ->andWhere('so.sourceID = :sourceID')
            ->setParameter('sourceID', $sourceID);
        $result = (int) $qb->getQuery()->getSingleScalarResult();
        return $result > 0;
    }

    /** @return string[][] */
    public function getOrderStatusSummary(
        $stage = SalesOrder::ORDER,
        $location = Facility::HEADQUARTERS_ID)
    {
        $sql = "
        SELECT COUNT(DISTINCT so.OrderNo) AS numOrders,
            COUNT( DISTINCT( IF( so.DateToShip IS NULL, NULL, so.OrderNo))) AS toShip,
            st.TypeAbbrev AS typeID,
            st.Sales_Type AS typeName
            FROM SalesOrders so
            JOIN SalesOrderDetails sod ON sod.OrderNo = so.OrderNo
            JOIN SalesTypes st ON so.OrderType = st.TypeAbbrev
            WHERE sod.Completed = 0
            AND so.FromStkLoc = :location
            AND so.SalesStage = :stage
            GROUP BY st.TypeAbbrev
        ";

        $conn = $this->_em->getConnection();
        return $conn->fetchAll($sql, [
            'stage' => $stage,
            'location' => $location,
        ]);
    }

    /** @return QueryBuilder */
    public function queryRefundableOrders(Customer $customer)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->join('o.customerBranch', 'branch')
            ->andWhere('branch.customer = :customer')
            ->setParameter('customer', $customer)
            ->join('o.creditAllocations', 'alloc')
            ->groupBy('o.id')
            ->having('sum(alloc.amount) > 0');
        return $qb;
    }

    /** @return \DateTime|null */
    public function findDateOfMostRecentOrderCreatedByUser(User $user)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('o.dateOrdered')
            ->andWhere('o.createdBy = :user')
            ->setParameter('user', $user)
            ->orderBy('o.dateOrdered', 'desc')
            ->setMaxResults(1);
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result ? $result['dateOrdered'] : null;
    }

    public function findOpenSalesOrder()
    {
        $qb = $this->createQueryBuilder('o');
        $qb->join('o.lineItems', 'details')
            ->andHaving('sum(details.completed) > 0')
            ->groupBy('o');
        return $qb->getQuery()->getResult();
    }
}
