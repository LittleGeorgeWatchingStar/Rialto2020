<?php

namespace Rialto\Sales\Web\Report;


use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Security\Role\Role;
use Rialto\Time\Web\DateType;
use Rialto\Web\Report\AuditTable;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Reports information about sales during a user-specified time interval.
 */
class SalesByPeriod extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [Role::CUSTOMER_SERVICE, Role::ACCOUNTING];
    }

    /**
     * @return string[]
     */
    protected function getDefaultParameters(array $query): array
    {
        $thisYear = (int) date('Y');
        return [
            'startDate' => "$thisYear-01-01",
            'endDate' => date('Y-m-d'),
            'minSales' => 10 * 1000,
            'region' => 'country',
        ];
    }

    public function prepareParameters(array $params): array
    {
        $params['invoice'] = SystemType::SALES_INVOICE;
        return $params;
    }

    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder
            ->add('startDate', DateType::class, [
                'input' => 'string',
                'label' => 'Between',
            ])
            ->add('endDate', DateType::class, [
                'input' => 'string',
                'label' => 'and',
            ])
            ->add('minSales', MoneyType::class, [
                'currency' => Currency::USD,
                'label' => 'Exclude sales less than',
            ])
            ->add('region', ChoiceType::class, [
                'choices' => [
                    'country' => 'country',
                    'state/province' => 'state',
                ],
            ])
            ->add('filter', SubmitType::class)
            ->getForm();
    }


    /**
     * @return AuditTable[]
     */
    public function getTables(array $params): array
    {
        return [
            $this->getCustomerExpenditures(),
            $this->getProductsPurchased(),
            $this->getCustomerRegions($params['region']),
        ];
    }

    private function getCustomerExpenditures()
    {
        $sql = "
            SELECT DISTINCT
                c.CompanyName AS Company
                , c.Name AS Name
                , c.EDIAddress AS Email
                , a.countryCode AS Country
                , sum(dt.OvAmount + dt.OvGST + dt.OvFreight + dt.OvDiscount) AS Sales
            FROM DebtorsMaster c
            JOIN Geography_Address a
                ON c.addressID = a.id
            JOIN DebtorTrans dt
                ON c.DebtorNo = dt.customerID
            WHERE dt.Type = :invoice
            AND dt.TranDate >= :startDate
            AND dt.TranDate <= :endDate
            GROUP BY c.DebtorNo
            HAVING Sales >= :minSales
            ORDER BY Sales DESC
        ";
        $table = new RawSqlAudit('Customer expenditure', $sql);
        $table->setScale('Sales', 2);
        return $table;
    }


    private function getProductsPurchased()
    {
        $sql = "
            SELECT
                stockItem.StockID AS `Stock code`
                , stockItem.Description AS `Item`
                , sum(invItem.qtyInvoiced) AS `Units sold`
            FROM StockMaster stockItem
            JOIN SalesOrderDetails orderItem
                ON stockItem.StockID = orderItem.StkCode
            JOIN SalesInvoiceItem invItem
                ON invItem.orderItemID = orderItem.ID
            JOIN DebtorTrans invoice
                ON invItem.debtorTransID = invoice.ID
            WHERE invoice.Type = :invoice
            AND invoice.TranDate >= :startDate
            AND invoice.TranDate <= :endDate
            GROUP BY stockItem.StockID
            ORDER BY `Units sold` DESC
        ";
        $table = new RawSqlAudit('Products purchased', $sql);
        $table->setScale('Units sold', 0);
        return $table;
    }

    private function getCustomerRegions($region)
    {
        $groupBy = "a.countryCode";
        $selectState = '';
        if ($region == 'state') {
            $groupBy .= ', a.stateCode';
            $selectState = ', a.stateCode as State';
        }
        $sql = "
            select a.countryCode as Country
                $selectState
                , count(distinct c.DebtorNo) as Customers
                , sum(dt.OvAmount + dt.OvGST + dt.OvFreight + dt.OvDiscount) as Sales
            from DebtorsMaster c
            join Geography_Address a
                on c.addressID = a.id
            join DebtorTrans dt
                on c.DebtorNo = dt.customerID
            where dt.Type = :invoice
            and dt.TranDate >= :startDate
            and dt.TranDate <= :endDate
            group by $groupBy
            order by Customers desc
        ";
        $table = new RawSqlAudit('Number of customers by region', $sql);
        $table->setScale('Customers', 0);
        $table->setScale('Sales', 2);
        return $table;
    }

}
