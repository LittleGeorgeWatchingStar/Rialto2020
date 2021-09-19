<?php

namespace Rialto\Accounting\Web\Report;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 * Finds work orders whose WIP transactions go negative (which they never should).
 */
class WipByWorkOrder
extends BasicAuditReport
{
    public function getTables(array $params): array
    {
        $reports = [];

        $issues = "
            select e.CounterIndex,
            i.WorkOrderID as WorkOrder,
            e.TranDate as TranDate,
            e.Amount as Amount
            from GLTrans e
            join WOIssues i
                on e.TypeNo = i.IssueNo
            where e.Type = :issueType
            and e.Account = :wipAccount";

        $nonIssues = "
            select e.CounterIndex,
            e.TypeNo as WorkOrder,
            e.TranDate as TranDate,
            e.Amount as Amount
            from GLTrans e
            where e.Type != :issueType
            and e.Account = :wipAccount";

        $report = new RawSqlAudit('Non-zero WIP transactions',
            "select e.WorkOrder,
                date(min(e.TranDate)) as FirstTranDate,
                date(max(e.TranDate)) as LastTranDate,
                date(wo.dateClosed) as DateClosed,
                wo.qtyOrdered as QtyOrdered,
                wo.qtyIssued as QtyIssued,
                wo.qtyReceived as QtyReceived,
                sum(e.Amount) as Balance
                from (
                    $issues
                    union all
                    $nonIssues
                ) as e
                left join StockProducer wo
                    on e.WorkOrder = wo.id
                    and wo.type = 'labour'
                group by e.WorkOrder
                having abs(Balance) > :threshold
                and LastTranDate >= :startDate
                order by FirstTranDate");
        $report->setScale('QtyOrdered', 0);
        $report->setScale('QtyIssued', 0);
        $report->setScale('QtyReceived', 0);
        $report->setScale('IssueAmount', 2);
        $report->setScale('ReceiptAmount', 2);
        $report->setScale('Balance', 2);
        $reports[] = $report;

        return $reports;
    }

    protected function getDefaultParameters(array $query): array
    {
        return [
            'issueType' => SystemType::WORK_ORDER_ISSUE,
            'receiptType' => SystemType::WORK_ORDER_RECEIPT_LEGACY,
            'wipAccount' => GLAccount::WORK_IN_PROCESS_INVENTORY,
            'threshold' => 0,
            'startDate' => ((int) date('Y') - 1) . '-01-01',
        ];
    }

}
