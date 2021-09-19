<?php

$path = explode(PATH_SEPARATOR, get_include_path());
$path[] = __DIR__ . '/../lib';
$path[] = __DIR__ . '/../web';
set_include_path(join(PATH_SEPARATOR, $path));

require_once 'config.php';

function main()
{
    static $dryRun = false;
    $config = GumstixConfig::get();
    $dba = $config->getDbAdapter('erp_db');
    $dba->query('set autocommit = off');
    $dba->query('set foreign_key_checks = off');
    $dba->beginTransaction();
    try {
        createJoinRecords($dba);
        calculateAmounts($dba);
        verifyStatements($dba);
        verifyTransactions($dba);

        if ($dryRun) {
            echo "DRY RUN!  Rolling back transaction.\n";
            $dba->rollBack();
        }
        else {
            $dba->commit();
        }
    }
    catch ( Exception $ex ) {
        $dba->rollBack();
        echo "FATAL ERROR: {$ex->getMessage()}\n";
    }
}

function createJoinRecords($dba)
{
    echo "\nCreating join records...\n";
    $statements = $dba->fetchAll("
        select BankStatementID, BankTransID, Amount
        from BankStatements
        where BankTransID not in ('','0')
    ");

    $counter = 0;
    $errors = array();
    foreach ( $statements as $statement ) {
        $statementId = $statement['BankStatementID'];
        $transIds = explode(',', $statement['BankTransID']);
        foreach ( $transIds as $transId ) {
            if (! $transId ) continue;

            /* Create the join record */
            $error = '';
            try {
                $dba->insert('BankStatementMatch', array(
                    'bankStatementId' => $statementId,
                    'bankTransactionId' => $transId,
                ));
            }
            catch ( Zend_Db_Statement_Exception $ex ) {
                $error = $ex->getMessage();
                $errors[] = "$statementId => $transId $error\n";
            }
            echo "$statementId => $transId $error\n";
            $counter ++;
        }
    }
    echo "$counter rows succeeded.\n";
    echo "ERRORS:\n";
    echo join("\n", $errors);
}

function calculateAmounts(Zend_Db_Adapter_Abstract $dba)
{
    echo "\nCalculating cleared amounts...\n";

    $stmtTotals = array();
    $transTotals = array();
    $errors = array();

    $joins = $dba->fetchAll("
        select m.*, bt.AmountCleared as transAmount, st.Amount as stmtAmount
        from BankStatementMatch m
        join BankTrans bt
        on m.bankTransactionId = bt.BankTransID
        join BankStatements st
        on m.bankStatementId = st.BankStatementID
    ");

    foreach ( $joins as $join ) {
        $stmtId = $join['bankStatementId'];
        $stmtAmt = $join['stmtAmount'];
        $transId = $join['bankTransactionId'];
        $transAmt = $join['transAmount'];

        if (empty($stmtTotals[$stmtId])) {
            $stmtTotals[$stmtId] = 0;
        }
        if (empty($transTotals[$transId])) {
            $transTotals[$transId] = 0;
        }

        $stmtLeft = $stmtAmt - $stmtTotals[$stmtId];
        $transLeft = $transAmt - $transTotals[$transId];

        $amount = calculateJoinAmount($stmtLeft, $transLeft);
        if ( $amount == 0 ) {
            $errors[] = "$stmtId: $stmtAmt $stmtLeft; $transId: $transAmt $transLeft\n";
            continue;
        }
        $stmtTotals[$stmtId] += $amount;
        $transTotals[$transId] += $amount;

        echo "$stmtId, $transId, $amount\n";

        $set = array('amountCleared' => $amount);
        $where = array(
            'bankStatementId = ?' => $stmtId,
            'bankTransactionId = ?' => $transId,
        );
        $dba->update('BankStatementMatch', $set, $where);
    }

    echo "ERRORS:\n";
    echo join("\n", $errors);
}

function calculateJoinAmount($stmtAmt, $transAmt)
{
    if ( $stmtAmt == 0 ) return 0;

    $sign = round($stmtAmt / abs($stmtAmt));
    if ( $sign * $transAmt != abs($transAmt) ) {
        return 0;
    }
    return ( $sign < 0 ) ?
        max($stmtAmt, $transAmt) :
        min($stmtAmt, $transAmt);
}

function verifyStatements($dba)
{
    echo "\nVerifying statements...\n";
    $mismatches = $dba->fetchAll("
        select st.BankStatementID, st.Amount, sum(m.amountCleared) as totalCleared
        from BankStatements st
        join BankStatementMatch m
        on st.BankStatementID = m.bankStatementId
        group by st.BankStatementID
        having abs(st.Amount) < abs(totalCleared)
    ");
    foreach ( $mismatches as $mismatch ) {
        printf("statement %s: %s < %s\n",
            $mismatch['BankStatementID'],
            $mismatch['Amount'],
            $mismatch['totalCleared']
        );
    }
    $numErrors = count($mismatches);
    if ( $numErrors != 0 ) {
        echo "ERROR: $numErrors statements do not match\n";
    }
}


function verifyTransactions($dba)
{
    echo "\nVerifying transactions...\n";
    $mismatches = $dba->fetchAll("
        select bt.BankTransID, bt.AmountCleared, sum(m.amountCleared) as totalCleared
        from BankTrans bt
        join BankStatementMatch m
        on bt.BankTransID = m.bankTransactionId
        group by bt.BankTransID
        having (bt.AmountCleared != totalCleared)

    ");
    foreach ( $mismatches as $mismatch ) {
        printf("trans %s: %s != %s\n",
            $mismatch['BankTransID'],
            $mismatch['AmountCleared'],
            $mismatch['totalCleared']
        );
    }
    $numErrors = count($mismatches);
    if ( $numErrors != 0 ) {
        echo "ERROR: $numErrors transactions do not match\n";
    }
}

main();
