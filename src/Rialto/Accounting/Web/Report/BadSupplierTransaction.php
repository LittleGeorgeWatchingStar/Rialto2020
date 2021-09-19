<?php

namespace Rialto\Accounting\Web\Report;

use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 * Look for SupplierTransactions that are in an invalid state.
 */
class BadSupplierTransaction extends BasicAuditReport
{
    protected function getDefaultParameters(array $query): array
    {
        return [
            'invoiceTypes' => join(',', SupplierTransaction::getInvoiceTypes()),
            'creditTypes' => join(',', SupplierTransaction::getCreditTypes()),
            '_order' => 'default',
        ];
    }

    public function getTables(array $params): array
    {
        $tables = [];
        $sql = "
            select suppTrans.ID
            , suppTrans.SupplierNo
            , sysType.TypeName
            , glTrans.groupNo as TransNo
            , date(suppTrans.TranDate) as TranDate
            , suppTrans.OvAmount
            , suppTrans.OvGST
            , suppTrans.SuppReference
            , suppTrans.TransText
            from SuppTrans suppTrans
            join Accounting_Transaction glTrans
                on suppTrans.transactionId = glTrans.id
            left join SysTypes sysType
                on glTrans.sysType = sysType.TypeID
            where (
                glTrans.sysType in (:invoiceTypes) and
                suppTrans.OvAmount + suppTrans.OvGST < 0
            ) or (
                glTrans.sysType in (:creditTypes) and
                suppTrans.OvAmount + suppTrans.OvGST > 0
            )
        ";
        switch ($params['_order']) {
            case 'amount':
                $sql .= "order by abs(suppTrans.OvAmount + suppTrans.OvGST), glTrans.sysType, suppTrans.TranDate";
                break;
            case 'date':
                $sql .= "order by suppTrans.TranDate, glTrans.sysType";
                break;
            default:
                $sql .= "order by glTrans.sysType, suppTrans.TranDate";
                break;
        }

        $description = "Transactions whose amount is negative when it should be positive and vice-versa";
        $table = new RawSqlAudit("Wrong sign", $sql, $description);
        $table->setScale('OvAmount', 4);
        $table->setLink('ID', 'supplier_transaction_link', function ($result) {
            return ['trans' => $result['ID']];
        });
        $table->setLink('SupplierNo', 'supplier_view', function ($result) {
            return ['supplier' => $result['SupplierNo']];
        });

        $tables[] = $table;
        return $tables;
    }

}
