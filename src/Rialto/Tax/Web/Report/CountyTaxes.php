<?php

namespace Rialto\Tax\Web\Report;

use Rialto\Accounting\Transaction\SystemType;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 * Sales and county taxes paid grouped by sales order.
 */
class CountyTaxes extends BasicAuditReport
{
    protected function getDefaultParameters(array $query): array
    {
        $year = date('Y');
        return [
            'start' => "$year-01-01",
            'end' => "$year-12-31",
            'invoice' => SystemType::SALES_INVOICE,
            'creditNote' => SystemType::CREDIT_NOTE,
            'county' => '',
        ];
    }

    public function prepareParameters(array $params): array
    {
        $params['county'] = '%' . $params['county'] . '%';
        return $params;
    }

    public function getTables(array $params): array
    {
        $reports = [];

        $report = new RawSqlAudit('Sales amounts and taxes paid',
            "select o.OrderNo as orderNo,
            lower(county.Name) as county,
            lower(address.city) as city,
            sum(t.OvAmount) as sales,
            sum(t.OvGST) as taxesPaid
            from DebtorTrans t
            join Accounting_Transaction glTrans
                on t.transactionId = glTrans.id
            join DebtorsMaster customer
                on t.customerID = customer.DebtorNo
            left join SalesOrders o
                on o.OrderNo = t.Order_
            left join Geography_Address address
                on o.shippingAddressID = address.id
            left join County county
                on address.stateCode = 'CA' and address.postalCode like concat(county.PostalCode, '%')
            where (t.OvGST != 0 or customer.StateStatus = 'Taxable')
            and address.countryCode in (null, 'US')
            and address.stateCode in (null, 'CA', 'California')
            and county.Name like :county
            and date(t.TranDate) >= :start
            and date(t.TranDate) <= :end
            and glTrans.sysType in (:invoice, :creditNote)
            group by o.OrderNo
            order by county.Name, address.city, o.OrderNo"
        );
        $report->setScale('sales', 2);
        $report->setScale('taxesPaid', 2);

        $reports[] = $report;

        return $reports;
    }

}
