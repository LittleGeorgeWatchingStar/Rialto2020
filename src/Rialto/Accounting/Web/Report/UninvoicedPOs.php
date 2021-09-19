<?php

namespace Rialto\Accounting\Web\Report;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 * Show purchase orders that have not been fully invoiced.
 */
class UninvoicedPOs extends BasicAuditReport
{
    public function getTables(array $params): array
    {
        $sortOrder = $this->getSortOrder($params);

        $tables = [];
        $table = new RawSqlAudit("Amount received != Invoice amount",
            "select rec.OrderNo, supp.SuppName as Supplier,
                rec.AmtReceived, inv.AmtInvoiced,
                rec.AmtReceived + inv.AmtInvoiced as Balance
                from (
                    select grn.PurchaseOrderNo as OrderNo,
                    po.SupplierNo as SupplierID,
                    sum(e.Amount) as AmtReceived
                    from GLTrans e
                    join Accounting_Transaction glTrans
                        on e.transactionId = glTrans.id
                    join GoodsReceivedNotice grn
                        on glTrans.sysType = :grnTypeID and glTrans.groupNo = grn.BatchID
                    join PurchOrders po
                        on grn.PurchaseOrderNo = po.OrderNo
                    where e.Account = :uninvoicedAccount
                    and po.DatePrinted >= :startDate
                    group by grn.PurchaseOrderNo
                ) as rec
                left join (
                    select i.purchaseOrderID as OrderNo,
                    sum(ii.Total) as AmtInvoiced
                    from SupplierInvoice i
                    join SuppInvoiceDetails ii
                        on ii.invoiceID = i.id
                    group by i.purchaseOrderID
                ) as inv on rec.OrderNo = inv.OrderNo
                left join Suppliers supp
                    on supp.SupplierID = rec.SupplierID
                where abs(rec.AmtReceived + inv.AmtInvoiced) > :threshold");
        $table->setScale('AmtReceived', 2);
        $table->setScale('AmtInvoiced', 2);
        $table->setScale('Balance', 2);
//        $tables[] = $table;

        $table = new RawSqlAudit("Amount received != SuppTrans amount",
            "select rec.OrderNo, supp.SuppName as Supplier,
                rec.AmtReceived, inv.AmtInvoiced,
                rec.AmtReceived + inv.AmtInvoiced as Balance
                from (
                    select grn.PurchaseOrderNo as OrderNo,
                    po.SupplierNo as SupplierID,
                    sum(e.Amount) as AmtReceived
                    from GLTrans e
                    join Accounting_Transaction glTrans
                        on e.transactionId = glTrans.id
                    join GoodsReceivedNotice grn
                        on glTrans.sysType = :grnTypeID and glTrans.groupNo = grn.BatchID
                    join PurchOrders po
                        on grn.PurchaseOrderNo = po.OrderNo
                    where e.Account = :uninvoicedAccount
                    and po.DatePrinted >= :startDate
                    group by grn.PurchaseOrderNo
                ) as rec
                left join (
                    select po.OrderNo,
                    sum(e.Amount) as AmtInvoiced
                    from GLTrans e
                    join Accounting_Transaction glTrans
                        on e.transactionId = glTrans.id
                    join SuppTrans st
                        on glTrans.id = st.transactionId
                    join PurchOrders po
                        on po.SupplierNo = st.SupplierNo
                    join SupplierInvoice inv
                        on inv.supplierReference = st.SuppReference
                        and inv.purchaseOrderID = po.OrderNo
                    where e.Account = :uninvoicedAccount
                    group by po.OrderNo
                ) as inv on rec.OrderNo = inv.OrderNo
                left join Suppliers supp
                    on supp.SupplierID = rec.SupplierID
                where abs(rec.AmtReceived + inv.AmtInvoiced) > :threshold
                order by $sortOrder");
        $table->setScale('AmtReceived', 2);
        $table->setScale('AmtInvoiced', 2);
        $table->setScale('Balance', 2);
        $tables[] = $table;

        return $tables;
    }

    /**
     * Carefully filter the "_order" parameter to prevent SQL injection.
     */
    private function getSortOrder(array $params)
    {
        switch ( $params['_order']) {
            case 'Supplier':
                return 'Supplier';
            case 'Balance':
                return 'Balance';
            default:
                return 'OrderNo';
        }
    }

    protected function getDefaultParameters(array $query): array
    {
        return [
            'grnTypeID' => SystemType::PURCHASE_ORDER_DELIVERY,
            'uninvoicedAccount' => GLAccount::UNINVOICED_INVENTORY,
            'startDate' => empty($query['startDate']) ? '2003-01-01' : $query['startDate'],
            'threshold' => empty($query['threshold']) ? 0 : $query['threshold'],
            '_order' => 'OrderNo',
        ];
    }

}
