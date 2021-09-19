<?php

namespace Rialto\Accounting\Web\Report;


use Rialto\Web\Report\AuditTable;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Check accounting transactions for warning signs or invalid states.
 */
class TransactionCheck extends BasicAuditReport
{
    /**
     * @return string[]
     */
    protected function getDefaultParameters(array $query): array
    {
        return [
            'since' => '2000-01-01',
        ];
    }

    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder
            ->add('since', DateType::class, [
                'input' => 'string',
                'widget' => 'single_text',
                'attr' => ['class' => 'date'],
            ])
            ->add('filter', SubmitType::class)
            ->getForm();
    }


    /**
     * @return AuditTable[]
     */
    public function getTables(array $params): array
    {
        $tables = [];


        $stockMoveCols = [
            'systemTypeID' => 'sysType',
            'systemTypeNumber' => 'groupNo',
//            'periodID' => 'period',
            'dateMoved' => 'transactionDate',
        ];

        foreach ($stockMoveCols as $childCol => $transCol) {
            $table = new RawSqlAudit("StockMove.$childCol", "
                select m.id, m.$childCol, t.$transCol from StockMove m
                left join Accounting_Transaction t
                ON m.transactionId = t.id
                where m.$childCol != t.$transCol
            ");
            $tables[] = $table;
        }

        $entryCols = [
            'Type' => 'sysType',
            'TypeNo' => 'groupNo',
//            'PeriodNo' => 'period',
            'TranDate' => 'transactionDate',
        ];

        foreach ($entryCols as $childCol => $transCol) {
            $table = new RawSqlAudit("GLEntry.$childCol", "
                select m.CounterIndex as id, m.$childCol, t.$transCol from GLTrans m
                left join Accounting_Transaction t
                ON m.transactionId = t.id
                where m.$childCol != t.$transCol
            ");
            $tables[] = $table;
        }

        $suppCols = [
            'Type' => 'sysType',
            'TransNo' => 'groupNo',
            'TranDate' => 'transactionDate',
        ];

        foreach ($suppCols as $childCol => $transCol) {
            $table = new RawSqlAudit("SuppTrans.$childCol", "
                select m.ID as id, m.$childCol, t.$transCol from SuppTrans m
                left join Accounting_Transaction t
                ON m.transactionId = t.id
                where m.$childCol != t.$transCol
            ");
            $tables[] = $table;
        }

        $debtorCols = [
            'Type' => 'sysType',
            'TransNo' => 'groupNo',
//            'Prd' => 'period',
            'TranDate' => 'transactionDate',
        ];

        foreach ($debtorCols as $childCol => $transCol) {
            $table = new RawSqlAudit("DebtorTrans.$childCol", "
                select m.ID as id, m.$childCol, t.$transCol from DebtorTrans m
                left join Accounting_Transaction t
                ON m.transactionId = t.id
                where m.$childCol != t.$transCol
            ");
            $tables[] = $table;
        }

        $bankCols = [
            'Type' => 'sysType',
            'TransNo' => 'groupNo',
            'TransDate' => 'date(t.transactionDate)',
        ];

        foreach ($bankCols as $childCol => $transCol) {
            $table = new RawSqlAudit("BankTrans.$childCol", "
                select m.BankTransID as id, m.$childCol, $transCol as transactionDate from BankTrans m
                left join Accounting_Transaction t
                ON m.transactionId = t.id
                where m.$childCol != $transCol
            ");
            $tables[] = $table;
        }

        $sql = "
            SELECT t.*
              , sum(e.Amount) AS balance
            FROM Accounting_Transaction t
            LEFT JOIN GLTrans e ON e.transactionId = t.id
            WHERE t.transactionDate >= :since
            GROUP BY t.id
            HAVING balance != 0
            LIMIT 1000
        ";
        $table = new RawSqlAudit('Unbalanced transactions', $sql);
        $tables[] = $table;

        return $tables;
    }

}
