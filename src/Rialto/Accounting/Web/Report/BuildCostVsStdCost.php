<?php

namespace Rialto\Accounting\Web\Report;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Security\Role\Role;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 * Compares what we actually paid for manufactured goods vs our standard
 * cost of those items.
 */
class BuildCostVsStdCost extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [Role::ACCOUNTING];
    }

    protected function getDefaultParameters(array $query): array
    {
        $year = (int) date('Y') - 1;
        return [
            'startDate' => "$year-01-01",
            'endDate' => "$year-12-31",
            'finishedInventory' => GLAccount::FINISHED_INVENTORY,
            'poType' => SystemType::PURCHASE_ORDER_DELIVERY,
            'woType' => SystemType::WORK_ORDER_RECEIPT,
            'legacyType' => SystemType::WORK_ORDER_RECEIPT_LEGACY,
            '_order' => 'date',
        ];
    }

    public function getTables(array $params): array
    {
        $orderBy = $this->getSortOrder($params);

        $tables = [];
        $tables[] = $this->createGrnTable($orderBy);
        $tables[] = $this->createLegacyTable($orderBy);

        return $tables;
    }

    /**
     * The table for new-style GRN receipts.
     */
    private function createGrnTable($orderBy)
    {
        $extFinalCost = "round(sum(e.Amount), 2)";
        $extStdCost = "round(gi.qtyReceived * (cost.materialCost + cost.labourCost + cost.overheadCost), 2)";

        $table = new RawSqlAudit("Build cost vs std cost",
            "select
            wo.id as workOrder,
            wo.purchaseOrderID as PO,
            gi.grnID as GRN,
            pd.StockID as stockCode,
            e.TranDate as date,
            glTrans.sysType,
            gi.qtyReceived,

            round(sum(e.Amount) / gi.qtyReceived, 2) as unitFinalCost,
            cost.materialCost + cost.labourCost + cost.overheadCost as unitStdCost,

            $extFinalCost as extFinalCost,
            $extStdCost as extStdCost,
            $extFinalCost - $extStdCost as extDiff

            from StockProducer wo
            join PurchData pd on wo.purchasingDataID = pd.ID
            join GoodsReceivedItem gi
                on gi.producerID = wo.id
            join Accounting_Transaction glTrans
                on glTrans.groupNo = gi.grnID
            join GLTrans e
                on e.transactionId = glTrans.id
                and e.Narrative like concat('%', pd.StockID, '%')
            left join StandardCost cost
                on pd.StockID = cost.stockCode
                and e.TranDate >= cost.startDate
            left join StandardCost nextCost
                on cost.stockCode = nextCost.stockCode
                and cost.startDate < nextCost.startDate
                and e.TranDate >= nextCost.startDate
            where nextCost.stockCode is null
            and wo.type = 'labour'
            and glTrans.sysType in (:poType, :woType)
            and e.Account = :finishedInventory
            and date(e.TranDate) between :startDate and :endDate
            group by gi.id
            order by $orderBy");

        $table->setScale('unitFinalCost', 2);
        $table->setScale('unitStdCost', 2);

        $table->setScale('extFinalCost', 2);
        $table->setScale('extStdCost', 2);
        $table->setScale('extDiff', 2);

        return $table;
    }

    private function getSortOrder($params)
    {
        switch ($params['_order']) {
            case 'workOrder':
                return 'workOrder, date';
            case 'PO':
                return 'PO, date';
            case 'extDiff':
                return 'extDiff, date';
            default:
                return 'date';
        }
    }

    /**
     * The table for legacy-style work order receipts.
     */
    private function createLegacyTable($orderBy)
    {
        $extFinalCost = "round(e.extFinalCost, 2)";
        $extStdCost = "round(m.qtyReceived * (cost.materialCost + cost.labourCost + cost.overheadCost), 2)";

        $table = new RawSqlAudit("Build cost vs std cost (legacy WO receipts)",
            "select
            wo.id as workOrder,
            wo.purchaseOrderID as PO,
            po.locationID as location,
            pd.StockID as stockCode,
            e.date,
            e.sysType,
            m.qtyReceived,

            round(e.extFinalCost / m.qtyReceived, 2) as unitFinalCost,
            cost.materialCost + cost.labourCost + cost.overheadCost as unitStdCost,

            $extFinalCost as extFinalCost,
            $extStdCost as extStdCost,
            $extFinalCost - $extStdCost as extDiff

            from StockProducer wo
            join PurchOrders po on wo.purchaseOrderID = po.OrderNo
            join PurchData pd on wo.purchasingDataID = pd.ID
            join (
                select glTrans.groupNo as typeNo,
                    sum(m.quantity) as qtyReceived
                from StockMove m
                join Accounting_Transaction glTrans
                    on m.transactionId = glTrans.id
                where glTrans.sysType = :legacyType
                and date(m.dateMoved) between :startDate and :endDate
                group by glTrans.groupNo
            ) as m
                on wo.id = m.typeNo
            join (
                select glTrans.groupNo as typeNo,
                min(e.TranDate) as date,
                group_concat(distinct glTrans.sysType) as sysType,
                sum(e.Amount) as extFinalCost,
                e.Narrative as narrative
                from GLTrans e
                join Accounting_Transaction glTrans
                    on e.transactionId = glTrans.id
                where glTrans.sysType = :legacyType
                and e.Account = :finishedInventory
                and date(e.TranDate) between :startDate and :endDate
                group by glTrans.groupNo
            ) as e
                on wo.id = e.typeNo
            left join StandardCost cost
                on pd.StockID = cost.stockCode
                and e.date >= cost.startDate
            left join StandardCost nextCost
                on cost.stockCode = nextCost.stockCode
                and cost.startDate < nextCost.startDate
                and e.date >= nextCost.startDate
            where nextCost.stockCode is null
            and wo.type = 'labour'
            and e.narrative like concat('%', pd.StockID , '%')
            order by $orderBy");

        $table->setScale('unitFinalCost', 2);
        $table->setScale('unitStdCost', 2);

        $table->setScale('extFinalCost', 2);
        $table->setScale('extStdCost', 2);
        $table->setScale('extDiff', 2);

        return $table;
    }
}
