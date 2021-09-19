<?php

namespace Rialto\Sales\Web\Report;

use Gumstix\Time\DateRange;
use Gumstix\Time\DateRangeType;
use Rialto\Security\Role\Role;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class RevenueByItem extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [Role::ACCOUNTING];
    }

    public function getTables(array $params): array
    {
        $tables = [];

        $bySku = $this->returnIfSet($params, 'showSku',
            "and orderItem.StkCode like :showSku");
        $byCustomer = $this->returnIfSet($params, 'customer', "
            and (customer.Name like :customer 
            or customer.CompanyName like :customer)
        ");
        $containsSku = $this->returnIfSet($params, 'containsSku', "
            and exists (
                select 1
                from SalesOrderDetails sod
                where sod.StkCode like :containsSku
                and sod.OrderNo = orderItem.OrderNo)
        ");

        $sql = "
            select orderItem.StkCode as SKU,
            concat(customer.Name, ', ', customer.CompanyName) as customer,
            orderItem.OrderNo as orderNo,
            invoice.TranDate as invoiceDate,
            invoiceItem.qtyInvoiced as qtySold,
            orderItem.finalUnitPrice * invoiceItem.qtyInvoiced as revenue

            from SalesInvoiceItem as invoiceItem
            join SalesOrderDetails as orderItem on invoiceItem.orderItemID = orderItem.ID
            join DebtorTrans as invoice on invoiceItem.debtorTransID = invoice.ID
            join DebtorsMaster as customer on invoice.customerID = customer.DebtorNo

            where invoice.TranDate >= :startDate
            and invoice.TranDate <= :endDate
            and orderItem.finalUnitPrice != 0
            $bySku
            $byCustomer
            $containsSku
        ";
        $table = new RawSqlAudit("Revenue", $sql);
        $table->setScale('qtySold', 0);
        $table->setScale('revenue', 2);
        $table->setLink('orderNo', 'sales_order_view', function(array $result) {
            return [
                'order' => $result['orderNo'],
            ];
        });

        $tables[] = $table;
        return $tables;
    }

    private function returnIfSet(array $params, $param, $sql)
    {
        return isset($params[$param])
            ? $sql
            : '';
    }

    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder
            ->add('customer', SearchType::class, [
                'required' => false,
                'label' => 'Customer name',
                'attr' => [
                    'placeholder' => 'Use % as wildcard',
                ]
            ])
            ->add('showSku', SearchType::class, [
                'required' => false,
                'label' => 'Show SKU',
                'attr' => [
                    'placeholder' => 'Use % as wildcard',
                ]
            ])
            ->add('containsSku', SearchType::class, [
                'required' => false,
                'label' => 'Order contains SKU',
                'attr' => [
                    'placeholder' => 'Use % as wildcard',
                ]
            ])
            ->add('shipped', DateRangeType::class, [
                'start_label' => 'Shipped between',
                'end_label' => 'and',
            ])
            ->add('filter', SubmitType::class)
            ->getForm();
    }

    protected function getDefaultParameters(array $query): array
    {
        $lastYear = new \DateTime('-1 year');
        $lastYear = $lastYear->format('Y');
        return [
            'shipped' => [
                'start' => "$lastYear-01-01",
                'end' => "$lastYear-12-31",
            ],
        ];
    }

    public function prepareParameters(array $params): array
    {
        /** @var  $range DateRange */
        $range = $params['shipped'];
        assertion($range instanceof DateRange);
        $params['startDate'] = $range->formatStart('Y-m-d');
        $params['endDate'] = $range->formatEnd('Y-m-d');
        return $params;
    }

}
