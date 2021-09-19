<?php

namespace Rialto\Stock\Web\Report;

use Rialto\Security\Role\Role;
use Rialto\Stock\Item\Eccn;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Report for the Bureau of Industry and Security (BIS) of export-controlled
 * products.
 *
 * @see http://wiki.ourstix.com/index.php?title=Export_Control
 */
class BisReport extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [Role::EMPLOYEE];
    }

    protected function getDefaultParameters(array $query): array
    {
        return [
            'year' => date('Y'),
            'skipCodes' => ['', Eccn::EAR99, Eccn::COMPUTERS],
        ];
    }

    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder
            ->add('year', NumberType::class)
            ->add('filter', SubmitType::class)
            ->getForm();
    }


    public function getTables(array $params): array
    {
        $tables = [];

        $table = new RawSqlAudit(
            'Self-classification report for ERN R101190',

            "select distinct replace(i.Description, ',', '') as \"PRODUCT NAME\",
                i.StockID as \"MODEL NAME\",
                'SELF' as MANUFACTURER,
                i.ECCN_Code as ECCN,
                'ENC' as \"AUTHORIZATION TYPE\",
                'operating system' as \"ITEM TYPE\"
                from StockMaster i
                join SalesOrderDetails oi on oi.StkCode = i.StockID
                join SalesInvoiceItem ii on ii.orderItemID = oi.ID
                join DebtorTrans dt on dt.ID = ii.debtorTransID
                where i.ECCN_Code not in (:skipCodes)
                and year(dt.TranDate) = :year"
        );
        $tables[] = $table;

        return $tables;
    }
}
