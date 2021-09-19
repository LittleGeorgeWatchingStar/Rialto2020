<?php

namespace Rialto\Accounting\Web\Report;

use DateTime;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Security\Role\Role;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

/**
 *
 */
class TaxAudit extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [Role::ACCOUNTING];
    }

    public function getTables(array $params): array
    {
        $tables = [];

        if ($params['auditConfig']['BankStatements']) {
            $table = new RawSqlAudit('Bank Statements',
                'SELECT s.BankStatementID as ID, s.BankPostDate as Date, s.Amount,
                s.BankDescription as Narrative, s.BankRef as Ref,
                sum(m.amountCleared) as Cleared
                FROM BankStatements s
                left join BankStatementMatch m
                on m.statementID = s.BankStatementID
                WHERE BankPostDate BETWEEN :yearBegin AND :yearEnd
                GROUP BY s.BankStatementID
                having Cleared != s.Amount',
                'This lists all the line items from Silicon Valley Bank Statements that do not have a matching BankTrans in Rialto.'
            );
            $table->setWidth('Narrative', '6cm');
            $tables[] = $table;
        }

        // bank account code: 10200
        if ($params['auditConfig']['BankTransactions']) {
            $table = new RawSqlAudit('Bank Transactions', '
            SELECT t.BankTransID AS ID
            , glTrans.sysType as Type
            , glTrans.groupNo as TransNo
            , t.BankAct as Bank
            , t.Ref
            , t.TransDate as Date
            , t.BankTransType
            , t.Amount
            , t.Printed AS P
            , t.ChequeNo
            , sum(m.amountCleared) as Cleared
            FROM BankTrans t
            join Accounting_Transaction glTrans
                on t.transactionId = glTrans.id
            left join BankStatementMatch m
            on m.transactionID = t.BankTransID
            WHERE BankAct = :bankAccountCode
            AND TransDate BETWEEN :yearBegin AND :yearEnd
            GROUP BY t.BankTransID
            HAVING Cleared != t.Amount
            ',
                'This lists all the BankTrans in Rialto that have not had a bank statement confirm they were cleared.'
            );
            $table->setWidth('Ref', '3cm');
            $table->setAlias('BankTransType', 'Bank Type');
            $table->setAlias('ChequeNo', 'Check#');
            $tables[] = $table;
        }

        if ($params['auditConfig']['AccountsReceivable']) {
            $table = new RawSqlAudit('Accounts Receivable',
                'SELECT cust.DebtorNo, cust.Name,
                :yearBegin as StartDate, :yearEnd as EndDate,
                sum((dt.OvAmount + dt.OvGST + OvFreight + OvDiscount - dt.Alloc) / dt.Rate) -
                sum(CASE WHEN dt.TranDate > :yearEnd
                    THEN (dt.OvAmount + dt.OvGST + OvFreight + OvDiscount) / dt.Rate
                    ELSE 0 END) AS Amount
                FROM DebtorsMaster cust
                JOIN DebtorTrans dt ON dt.customerID = cust.DebtorNo
                WHERE (dt.TranDate) > :yearBegin
                GROUP BY cust.DebtorNo, cust.Name
                HAVING (Amount) > 10
                ORDER BY cust.Name',
                'This is a list of all A/R amounts that were unpaid at year-end'
            );
            $table->setWidth('Name', '5cm');
            $tables[] = $table;
        }

        if ($params['auditConfig']['PrepaidRevenue']) {
            $table = new RawSqlAudit('Prepaid Revenue',
                'SELECT res.customerID as Customer, res.Company, res.StartDate, res.EndDate,
                      sum(res.Amount) as Amount
                      FROM (SELECT dt.ID as CreditID, dt.customerID, dm.companyName as Company,
                :yearBegin as StartDate, :yearEnd as EndDate,
                (dt.OvAmount + dt.OvGST + dt.OvFreight + dt.OvDiscount) / dt.Rate
                 + sum(CASE WHEN dti.TranDate <= :yearEnd THEN alloc.Amt ELSE 0 END) as Amount
                FROM DebtorTrans dt
                LEFT JOIN CustAllocns alloc ON alloc.TransID_AllocFrom = dt.ID
                LEFT JOIN DebtorTrans dti on alloc.TransID_AllocTo = dti.ID
                JOIN DebtorsMaster dm on dm.DebtorNo = dt.customerID
                WHERE dt.Type IN (11, 12)
                AND dt.TranDate BETWEEN :yearBegin AND :yearEnd
                GROUP BY dt.ID
                HAVING Amount != 0) res
                GROUP BY res.customerID
                HAVING Amount < 0',
                'This lists all the amounts paid by customers towards sales orders that had not been delivered'
            );
            $table->setLink('Customer', 'debtor_transaction_list', function (array $row) {
                return [
                    'customer' => $row['Customer'],
                    'startDate' => $row['StartDate'],
                ];
            });
            $tables[] = $table;
        }

        if ($params['auditConfig']['NetOwing']) {
            $table = new RawSqlAudit('Net Owing',
                'SELECT dt.customerID as CustomerID, dm.companyName as Company,
                :yearBegin as StartDate, :yearEnd as EndDate,
                sum(dt.OvAmount + dt.OvGST + dt.OvFreight + dt.OvDiscount) AS Amount
                FROM DebtorTrans dt
                JOIN DebtorsMaster dm ON dm.DebtorNo = dt.customerID
                WHERE dt.TranDate > :yearBegin
                AND dt.TranDate <= :yearEnd
                GROUP BY dt.customerID
                HAVING Amount != 0',
                'Rough estimate of the change in Prepaid Revenue over the period');
            $tables[] = $table;
        }

        if ($params['auditConfig']['FinishedInventory']) {
            $tables[] = new InventoryValuationTaxAudit(GLAccount::fetchFinishedInventory());
        }

        if ($params['auditConfig']['RawInventory']) {
            $tables[] = new InventoryValuationTaxAudit(GLAccount::fetchRawInventory());
        }

        if ($params['auditConfig']['WipAudit']) {
            $tables[] = new WipTaxAudit();
        }

        if ($params['auditConfig']['AccountsPayable']) {
            $table = new RawSqlAudit('Accounts Payable',
                'SELECT Suppliers.SupplierID as ID, Suppliers.SuppName as Name,
                :yearEnd as EndDate,
                sum((SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc) / SuppTrans.Rate) -
                sum(CASE WHEN SuppTrans.TranDate > :yearEnd
                    THEN (SuppTrans.OvAmount + SuppTrans.OvGST) / SuppTrans.Rate
                    ELSE 0 END) AS Amount
                FROM Suppliers
                LEFT JOIN SuppTrans ON Suppliers.SupplierID = SuppTrans.SupplierNo
                GROUP BY Suppliers.SupplierID, Suppliers.SuppName
                HAVING ABS(Amount) > 1
                AND SupplierID NOT IN (41, 114)
                ORDER BY Suppliers.SuppName'
            );
            $table->setWidth('Name', '6cm');
            $tables[] = $table;
        }

        if ($params['auditConfig']['PrepaidTaxes']) {
            $table = new RawSqlAudit('Prepaid Taxes',
                'SELECT Suppliers.SupplierID as ID, Suppliers.SuppName as Name,
                :yearEnd as EndDate,
                sum((SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc) / SuppTrans.Rate) -
                sum(CASE WHEN SuppTrans.TranDate > :yearEnd
                    THEN (SuppTrans.OvAmount + SuppTrans.OvGST)/SuppTrans.Rate
                    ELSE 0 END) AS Amount
                FROM Suppliers
                LEFT JOIN SuppTrans ON Suppliers.SupplierID = SuppTrans.SupplierNo
                GROUP BY Suppliers.SupplierID, Suppliers.SuppName
                HAVING ABS(Amount) > 1
                AND SupplierID IN (41, 114)
                ORDER BY Suppliers.SuppName'
            );
            $table->setWidth('Name', '6cm');
            $tables[] = $table;
        }

        if ($params['auditConfig']['SupplierInvoices']) {
            $table = new RawSqlAudit('Total supplier invoices',
                'SELECT Suppliers.SupplierID as ID, Suppliers.SuppName as Name,
                (SELECT GROUP_CONCAT(DISTINCT Email SEPARATOR ",")
                    FROM SupplierContacts 
                    WHERE SupplierID = Suppliers.SupplierID
                    AND Email != ""
                    AND OrderContact = TRUE
                    AND Active = TRUE) as Email,
                :yearBegin as StartDate, :yearEnd as EndDate,
                sum(SuppTrans.OvAmount + SuppTrans.OvGST) AS Amount
                FROM Suppliers
                LEFT JOIN SuppTrans ON Suppliers.SupplierID = SuppTrans.SupplierNo
                WHERE TranDate BETWEEN :yearBegin AND :yearEnd
                AND Type = 20
                GROUP BY Suppliers.SupplierID, Suppliers.SuppName
                ORDER BY Suppliers.SuppName'
            );
            $table->setLink('ID', 'supplier_view', function (array $row) {
                return [
                    'supplier' => $row['ID'],
                ];
            });
            $table->setListDelimiter('Email', ',');
            $table->setWidth('Name', '6cm');
            $tables[] = $table;
        }

        if ($params['auditConfig']['VisaandMasterCardReceipts']) {
            $tables[] = new RawSqlAudit('Visa and MasterCard receipts',
                'select LEFT(TransDate, 7) AS Month, SUM(Amount) AS Amount
                from BankTrans
                WHERE Ref LIKE "Sweep V%"
                AND TransDate BETWEEN :yearBegin AND :yearEnd
                GROUP BY LEFT(TransDate, 7)'
            );
        }

        if ($params['auditConfig']['AmExReceipts']) {
            $tables[] = new RawSqlAudit('AmEx receipts',
                'select LEFT(TransDate,7) AS Month, SUM(Amount) AS Amount
                from BankTrans
                WHERE Ref LIKE "Sweep A%"
                AND TransDate BETWEEN :yearBegin AND :yearEnd
                GROUP BY LEFT(TransDate, 7)'
            );
        }

        return $tables;
    }

    protected function getDefaultParameters(array $query): array
    {
        $defaultYear = (int) date('Y') - 1;

        if (isset($query['yearBegin'])) {
            $yearBegin = new DateTime($query['yearBegin']);
        } else {
            $yearBegin = new DateTime();
            $yearBegin->setDate($defaultYear, 1, 1);
            $yearBegin->setTime(0, 0, 0);
        }

        if (isset($query['yearEnd'])) {
            $yearEnd = new DateTime($query['yearEnd']);
        } else {
            $yearEnd = new DateTime();
            $yearEnd->setDate($defaultYear, 12, 31);
            $yearEnd->setTime(23, 59, 59);
        }

        $auditConfig = [
            'BankStatements' => $query['BankStatements'] ?? false,
            'BankTransactions' => $query['BankTransactions'] ?? false,
            'AccountsReceivable' => $query['AccountsReceivable'] ?? false,
            'PrepaidRevenue' => $query['PrepaidRevenue'] ?? false,
            'FinishedInventory' => $query['FinishedInventory'] ?? false,
            'RawInventory' => $query['RawInventory'] ?? false,
            'WipAudit' => $query['WipAudit'] ?? false,
            'AccountsPayable' => $query['AccountsPayable'] ?? false,
            'PrepaidTaxes' => $query['PrepaidTaxes'] ?? false,
            'SupplierInvoices' => $query['SupplierInvoices'] ?? false,
            'VisaandMasterCardReceipts' => $query['VisaandMasterCardReceipts'] ?? false,
            'AmExReceipts' => $query['AmExReceipts'] ?? false,
            'NetOwing' => $query['NetOwing'] ?? false,
        ];

        return [
            'year' => $defaultYear,
            'yearBegin' => $yearBegin->format('Y-m-d H:i:s'),
            'yearEnd' => $yearEnd->format('Y-m-d H:i:s'),
            'bankAccountCode' => GLAccount::REGULAR_CHECKING_ACCOUNT,
            'auditConfig' => $auditConfig,
        ];
    }
}
