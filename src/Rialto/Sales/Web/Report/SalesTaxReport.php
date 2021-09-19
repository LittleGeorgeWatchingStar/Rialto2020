<?php

namespace Rialto\Sales\Web\Report;

use Doctrine\DBAL\Connection;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Transaction\SystemType;

/**
 * Report sales and sales taxes.
 */
class SalesTaxReport
{
    /** @var Connection */
    private $conn;

    private $data = [];

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function loadData(Period $start, Period $end)
    {
        $sql = "
            SELECT
            if (address.stateCode IN ('CA', 'California'),
                customer.StateStatus, 'Out of state') AS taxStatus,
            SUM(t.OvAmount) AS sales,
            SUM(t.OvGST) AS taxesPaid,
            sum(taxOwed.taxOwed) AS taxesOwed
            FROM DebtorTrans t
            LEFT JOIN DebtorsMaster customer
                ON t.customerID = customer.DebtorNo
            LEFT JOIN SalesOrders so
                ON so.OrderNo = t.Order_
            LEFT JOIN (
                SELECT sum(round(item.qtyInvoiced * item.finalUnitPrice, 2) * item.SalesTaxRate) AS taxOwed,
                item.OrderNo
                FROM SalesOrderDetails item
                GROUP BY item.OrderNo
            ) AS taxOwed ON taxOwed.OrderNo = so.OrderNo
            LEFT JOIN Geography_Address address
              ON so.shippingAddressID = address.id
            WHERE t.Prd >= :startPeriod
            AND t.Prd <= :endPeriod
            AND t.Type IN (:invoice, :creditNote)
            GROUP BY taxStatus";

        $params = [
            'startPeriod' => $start->getId(),
            'endPeriod' => $end->getId(),
            'invoice' => SystemType::SALES_INVOICE,
            'creditNote' => SystemType::CREDIT_NOTE,
        ];
        $stmt = $this->conn->executeQuery($sql, $params);
        $this->data = $stmt->fetchAll();
    }

    public function getItems()
    {
        return $this->data;
    }
}
