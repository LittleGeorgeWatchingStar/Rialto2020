<?php

namespace Rialto\Accounting\Web\Report;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\DbManager;
use Rialto\Database\Orm\DoctrineDbManager;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 * Shows entries in Uninvoiced Inventory (UI) and which
 * suppliers and POs have outstanding UI balances.
 */
class UninvoicedInventory extends BasicAuditReport
{
    protected function getDefaultParameters(array $query): array
    {
        $year = (int) date('Y') - 1;
        return [
            'startDate' => "$year-01-01",
            'endDate' => "$year-12-31",
            'uninvoicedInventory' => GLAccount::UNINVOICED_INVENTORY,
            'grnType' => SystemType::PURCHASE_ORDER_DELIVERY,
            'invType' => SystemType::PURCHASE_INVOICE,
        ];
    }

    /**
     * @param DoctrineDbManager $dbm
     */
    public function init(DbManager $dbm, array $params)
    {
        $conn = $dbm->getConnection();
        static $margin = "1 year";

        /* All GL entries for supplier invoices, joined through to the
         * supplier and PO. */
        $invoices = "select distinct
            e.CounterIndex,
            glTrans.sysType as Type,
            glTrans.groupNo as TypeNo,
            date(e.TranDate) as TranDate,
            e.Narrative,
            e.Amount,
            ifnull(st.SupplierNo, po.SupplierNo) as SupplierNo,
            po.OrderNo
            from GLTrans e
            join Accounting_Transaction glTrans
                on e.transactionId = glTrans.id
            left join SuppTrans st
                on st.transactionId = glTrans.id
            left join SupplierInvoice inv
                on st.SupplierNo = inv.supplierID
                and st.SuppReference = inv.supplierReference
            left join SuppInvoiceDetails invItem
                on invItem.invoiceID = inv.id
            left join GoodsReceivedItem grnItem
                on grnItem.invoiceItemID = invItem.SIDetailID
            left join GoodsReceivedNotice grn
                on grnItem.grnID = grn.BatchID
            left join PurchOrders po
                on grn.PurchaseOrderNo = po.OrderNo
            where date(e.TranDate)
                between date_sub(:startDate, interval $margin)
                and date_add(:endDate, interval $margin)
            and e.Account = :uninvoicedInventory
            and glTrans.sysType = :invType";

        /* All GL entries for GRNs */
        $receipts = "select distinct
            e.CounterIndex,
            glTrans.sysType as Type,
            glTrans.groupNo as TypeNo,
            date(e.TranDate) as TranDate,
            e.Narrative,
            e.Amount,
            po.SupplierNo,
            po.OrderNo
            from GLTrans e
            join Accounting_Transaction glTrans
                on e.transactionId = glTrans.id
            left join GoodsReceivedNotice grn
                on glTrans.groupNo = grn.BatchID
            left join PurchOrders po
                on grn.PurchaseOrderNo = po.OrderNo
            where date(e.TranDate)
                between date_sub(:startDate, interval $margin)
                and date_add(:endDate, interval $margin)
            and e.Account = :uninvoicedInventory
            and glTrans.sysType = :grnType
            and e.Narrative not like '%grn reversal%'";

        /* All GL entries for GRN reversals */
        $reversals = "select distinct
            e.CounterIndex,
            glTrans.sysType as Type,
            glTrans.groupNo as TypeNo,
            date(e.TranDate) as TranDate,
            e.Narrative,
            e.Amount,
            po.SupplierNo,
            po.OrderNo
            from GLTrans e
            join Accounting_Transaction glTrans
                on e.transactionId = glTrans.id
            left join GoodsReceivedItem grnItem
                on glTrans.groupNo = grnItem.id
            left join GoodsReceivedNotice grn
                on grnItem.grnID = grn.BatchID
            left join PurchOrders po
                on grn.PurchaseOrderNo = po.OrderNo
            where date(e.TranDate)
                between date_sub(:startDate, interval $margin)
                and date_add(:endDate, interval $margin)
            and e.Account = :uninvoicedInventory
            and glTrans.sysType = :grnType
            and e.Narrative like '%grn reversal%'";

        /* All GL entries for other types of SupplierTransactions */
        $others = "select distinct
            e.CounterIndex,
            glTrans.sysType as Type,
            glTrans.groupNo as TypeNo,
            date(e.TranDate) as TranDate,
            e.Narrative,
            e.Amount,
            st.SupplierNo,
            null
            from GLTrans e
            join Accounting_Transaction glTrans
                on e.transactionId = glTrans.id
            left join SuppTrans st
                on glTrans.id = st.transactionId
            where date(e.TranDate)
                between date_sub(:startDate, interval $margin)
                and date_add(:endDate, interval $margin)
            and e.Account = :uninvoicedInventory
            and glTrans.sysType not in (:grnType, :invType)";

        $sql = "create temporary table TempEntryPurchaseOrder
            ($receipts) union ($invoices) union ($reversals) union ($others)
            order by TranDate, Type, TypeNo, Amount";

        $conn->executeQuery($sql, $params);

        $sql = "create temporary table TempAffectedOrders
            select distinct SupplierNo, OrderNo from TempEntryPurchaseOrder
            where TranDate between :startDate and :endDate";

        $conn->executeQuery($sql, [
            'startDate' => $params['startDate'],
            'endDate' => $params['endDate'],
        ]);
    }

    public function getTables(array $params): array
    {
        $tables = [];

        $where = '';
        if (! empty($params['purchaseOrder']) ) {
            $where = ' where e.OrderNo = :purchaseOrder';
        }
        elseif (! empty($params['supplier']) ) {
            $where = ' where e.SupplierNo = :supplier';
        }

        /* Show all Uninvoiced Inventory (UI) entries */
        $all = "select e.* from TempEntryPurchaseOrder e
            $where
            order by SupplierNo, OrderNo, TranDate";
        $table = new RawSqlAudit("All entries", $all,
            "All GL entries in Uninvoiced Inventory within the date range");
//        $tables[] = $table;

        /* Show suppliers with non-zero UI balances */
        $bySupplier = "select
            group_concat(distinct e.Type) as SysTypes,
            min(e.TranDate) as FirstTranDate,
            sum(e.Amount) as Total,
            e.SupplierNo
            from TempEntryPurchaseOrder e
            join TempAffectedOrders o
                on e.OrderNo = o.OrderNo
            $where
            group by e.SupplierNo
            having Total != 0";

        $table = new RawSqlAudit("Grouped by supplier", $bySupplier,
            "All suppliers with non-zero balances");
        $table->setScale('Total', 2);
//        $tables[] = $table;

        /* Show POs with non-zero UI balances */
        $byOrder = "select
            group_concat(distinct e.Type) as SysTypes,
            count(distinct e.Type, e.TypeNo) as NumTrans,
            min(e.TranDate) as FirstTranDate,
            max(e.TranDate) as LastTranDate,
            sum(e.Amount) as Total,
            e.OrderNo,
            e.SupplierNo
            from TempEntryPurchaseOrder e
            join TempAffectedOrders o
                on e.OrderNo = o.OrderNo
            $where
            group by e.OrderNo
            having Total != 0
            order by abs(sum(e.Amount)) desc";

        $table = new RawSqlAudit("Grouped by PO", $byOrder,
            "All purchase orders with non-zero balances");
        $table->setScale('Total', 2);
        $tables[] = $table;

        return $tables;
    }


}
