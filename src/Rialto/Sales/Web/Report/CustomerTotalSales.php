<?php

namespace Rialto\Sales\Web\Report;

use Rialto\Accounting\Transaction\SystemType;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 * Show customers' basic contact info along with cumulative sales.
 */
class CustomerTotalSales extends BasicAuditReport
{
    protected function getDefaultParameters(array $query): array
    {
        return [
            'minSales' => 10 * 1000,
            'invoice' => SystemType::SALES_INVOICE,
            'sort' => 'sales',
        ];
    }

    public function getTables(array $params): array
    {
        $tables = [];

        $orderBy = $this->getOrderBy($params['sort']);

        // Try different queries to see which one works best.
        $sql = "
            select distinct
                c.CompanyName as Company
                , c.Name as Name
                , c.EDIAddress as Email
                , a.countryCode as Country
                , sum(dt.OvAmount + dt.OvGST + dt.OvFreight + dt.OvDiscount) as Sales
                , date(min(dt.TranDate)) as `First invoice`
                , date(max(dt.TranDate)) as `Last invoice`
            from DebtorsMaster c
            join Geography_Address a
                on c.addressID = a.id
            join DebtorTrans dt
                on dt.customerID = c.DebtorNo
            where c.EDIAddress != ''
            and dt.Type = :invoice
            group by c.DebtorNo
            having Sales >= :minSales
            order by $orderBy";

        $table = new RawSqlAudit('Total sales by customer', $sql);
        $table->setScale('Sales', 2);

        $tables[] = $table;
        return $tables;
    }

    private function getOrderBy($sort)
    {
        switch ($sort) {
            case 'firstInvoice':
                return '`First invoice` asc';
            case 'lastInvoice':
                return '`Last invoice` asc';
            default:
                return 'Sales desc';
        }
    }
}
