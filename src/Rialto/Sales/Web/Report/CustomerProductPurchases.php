<?php

namespace Rialto\Sales\Web\Report;

use Rialto\Security\Role\Role;
use Rialto\Time\Web\DateType;
use Rialto\Web\Report\AuditTable;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerProductPurchases extends BasicAuditReport
{
    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder
            ->add('startDate', DateType::class, [
                'input' => 'string',
            ])
            ->add('product', TextType::class, [
                'label' => 'Products matching',
                'required' => false,
                'label_attr' => [
                    'title' => 'You can match either the stock code or ' .
                        'the description',
                    'class' => 'tooltip',
                ],
            ])
            ->add('filter', SubmitType::class)
            ->getForm();
    }

    public function getAllowedRoles()
    {
        return [Role::CUSTOMER_SERVICE];
    }

    /**
     * @return string[]
     */
    protected function getDefaultParameters(array $query): array
    {
        $startDate = new \DateTime('-1 year');
        return [
            'startDate' => $startDate->format('Y-m-d'),
            'product' => '',
        ];
    }

    public function prepareParameters(array $params): array
    {
        $params['product'] = "%" . $params['product'] . "%";
        return $params;
    }


    /**
     * @return AuditTable[]
     */
    public function getTables(array $params): array
    {
        $tables = [];

        $sql = "
            SELECT customer.CompanyName AS company
                , customer.Name AS contact
                , customer.EDIAddress AS email
                , item.StockID AS stockCode
                , item.Description AS product
                , sum(invItem.qtyInvoiced) AS quantity
                FROM DebtorsMaster customer
                JOIN DebtorTrans invoice ON invoice.customerID = customer.DebtorNo
                JOIN SalesInvoiceItem invItem ON invItem.debtorTransID = invoice.ID
                JOIN SalesOrderDetails orderItem ON orderItem.ID = invItem.orderItemID
                JOIN StockMaster item ON item.StockID = orderItem.StkCode
                WHERE invoice.TranDate >= :startDate
                AND ( item.StockID LIKE :product OR item.Description LIKE :product)
                GROUP BY customer.DebtorNo, item.StockID
                ORDER BY company, contact, email
                LIMIT 1000
        ";
        $table = new RawSqlAudit('Products purchased by customer', $sql);
        $table->setScale('quantity', 0);

        $tables[] = $table;
        return $tables;
    }


}
