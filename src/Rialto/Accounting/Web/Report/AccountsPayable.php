<?php

namespace Rialto\Accounting\Web\Report;

use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 *
 */
class AccountsPayable extends BasicAuditReport
{
    public function getTables(array $params): array
    {
        $reports = [];
        $report = new RawSqlAudit('Mismatched supplier transactions',
            "select st.ID, st.TranDate, trans.sysType as Type, trans.groupNo as TypeNo,
            st.OvAmount + st.OvGST as TransTotal,
            sum(ifnull(e.Amount, 0)) as AccountsPayable,
            st.OvAmount + st.OvGST + sum(ifnull(e.Amount, 0)) as Difference
            from SuppTrans st
            join Accounting_Transaction trans
                on st.transactionId = trans.id
            left join GLTrans e
                on e.transactionId = trans.id
                and e.Account = 20000
            group by st.ID
            having Difference != 0
            order by st.TranDate");
        $report->setScale('TransTotal', 2);
        $report->setScale('AccountsPayable', 2);
        $report->setScale('Difference', 2);
        $reports[] = $report;

        $report = new RawSqlAudit('Mismatched accounts payable transactions',
            "select e.TranDate, trans.sysType as Type, trans.groupNo as TypeNo, sum(e.Amount) as AccountsPayable,
            st.ID, st.OvAmount + st.OvGST as TransTotal,
            sum(e.Amount) + ifnull(st.OvAmount + st.OvGST, 0) as Difference
            from GLTrans e
            join Accounting_Transaction trans
                on e.transactionId = trans.id
            left join SuppTrans st
                on st.transactionId = trans.id
            where e.Account = 20000
            group by trans.sysType, trans.groupNo
            having Difference != 0
            order by e.TranDate, trans.sysType");
        $report->setScale('AccountsPayable', 2);
        $report->setScale('TransTotal', 2);
        $report->setScale('Difference', 2);
        $reports[] = $report;
        return $reports;

    }

    protected function getDefaultParameters(array $query): array
    {
        return [];
    }
}
