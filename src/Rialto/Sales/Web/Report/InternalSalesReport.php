<?php

namespace Rialto\Sales\Web\Report;

use Doctrine\DBAL\Connection;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Transaction\SystemType;

/**
 * Report of sales to ourselves for our internal use.
 */
class InternalSalesReport
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
            gl.AccountName AS discountAccount,
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
                d.DiscountAccount,
                sum(d.UnitPrice * d.QtyInvoiced * d.DiscountPercent) AS discountPrice,
                sum((c.materialCost + c.labourCost + c.overheadCost) *
                    d.QtyInvoiced * d.discountPercent) AS discountCost
                FROM SalesOrderDetails d
                JOIN StandardCost c ON d.StkCode = c.stockCode
                WHERE c.startDate = (
                    SELECT max(c1.startDate)
                    FROM StandardCost c1
                    WHERE c1.startDate <= :endDate
                    AND c1.stockCode = c.stockCode)
                AND d.DiscountAccount IN (:eng, :mkt)
                GROUP BY d.OrderNo, d.DiscountAccount
            ) AS sod
                ON so.OrderNo = sod.OrderNo
            LEFT JOIN ChartMaster AS gl
                ON gl.AccountCode = sod.DiscountAccount
            WHERE t.Prd >= :startPeriod
            AND t.Prd <= :endPeriod
            AND t.Type IN (:invoice, :creditNote)
            AND customer.internalCustomer = 1
            GROUP BY sod.DiscountAccount";

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
