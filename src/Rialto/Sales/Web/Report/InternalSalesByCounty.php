<?php

namespace Rialto\Sales\Web\Report;

use Doctrine\DBAL\Connection;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Transaction\SystemType;

/**
 * Report of sales to ourselves for our internal use, broken down by
 * county.
 */
class InternalSalesByCounty
{
    private $data = [];

    /** @var Connection */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function loadData(Period $start, Period $end)
    {
        $sql = "
            SELECT
            county.Name AS countyName,
            sum(sod.discountPrice) AS discountPrice,
            sum(sod.discountCost) AS discountCost,
            SUM( t.OvAmount ) AS sales,
            SUM( t.OvGST ) AS taxesPaid
            FROM DebtorTrans t
            JOIN DebtorsMaster customer
                ON t.customerID = customer.DebtorNo
            LEFT JOIN SalesOrders so
                ON so.OrderNo = t.Order_
            LEFT JOIN (
                SELECT
                d.OrderNo,
                sum(d.UnitPrice * d.QtyInvoiced * d.DiscountPercent) AS discountPrice,
                sum((c.materialCost + c.labourCost + c.overheadCost) *
                    d.QtyInvoiced * d.discountPercent) AS discountCost
                FROM SalesOrderDetails d
                JOIN StandardCost c ON d.StkCode = c.stockCode
                WHERE c.startDate = (
                    SELECT max(c1.startDate)
                    FROM StandardCost c1
                    WHERE date(c1.startDate) <= :endDate
                    AND c1.stockCode = c.stockCode)
                AND d.DiscountAccount in (:eng, :mkt)
                GROUP BY d.OrderNo
            ) AS sod
                ON so.OrderNo = sod.OrderNo
            LEFT JOIN Geography_Address AS address
                ON so.shippingAddressID = address.id
            LEFT JOIN County county
                ON address.postalCode = county.PostalCode
            WHERE t.Prd >= :startPeriod
            AND t.Prd <= :endPeriod
            AND t.Type IN (:invoice, :creditNote)
            AND customer.internalCustomer = 1
            AND address.stateCode = 'CA'
            AND address.countryCode = 'US'
            GROUP BY county.Name";

        $params = [
            'startPeriod' => $start->getId(),
            'endPeriod' => $end->getId(),
            'endDate' => $end->getEndDate()->format('Y-m-d'),
            'invoice' => SystemType::SALES_INVOICE,
            'creditNote' => SystemType::CREDIT_NOTE,
            'eng' => GLAccount::CHARGE_TO_ENGINEERING,
            'mkt' => GLAccount::CHARGE_TO_MARKETING,
        ];
        $stmt = $this->conn->executeQuery($sql, $params);
        $this->data = $stmt->fetchAll();
    }

    public function setManualAmount($amount)
    {
        $this->data[] = [
            'discountAccount' => 'Manually entered',
            'discountPrice' => $amount,
            'discountCost' => $amount,
            'sales' => 0,
            'taxesPaid' => 0,
        ];
    }

    public function getItems()
    {
        return $this->data;
    }
}
