<?php

/* $Revision: 1.4 $ */

/* Find the refunds that have no been put into CardTrans

  SELECT * FROM GLTrans G
  LEFT JOIN CardTrans C ON C.Type=G.Type and C.TransNo=G.TypeNo AND G.Type=101
  WHERE C.Type IS NULL ANF G.TranDate LIKE '2009%';

 */


/*
  Look at all swept card transactions and their posted dates wherever the sweep has not been matched.

  If the posted date changes to the next day or the prior day:
  1) Change the post date' in CardTrans
  2) Change the Amount in GLTrans on the 2 sweep dates.
  3) Change the Amount in the BankTrans on the 2 sweep dates
  4) Change the transaction-fee date for

 */

$PageSecurity = 7;

include ('includes/session.inc');

$title = _('Move CardTrans date by 1');

include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/CommonGumstix.inc');
include('includes/WO_ui_input.inc');

echo '<FORM METHOD="POST" ACTION="' . $_SERVER["PHP_SELF"] . '?' . SID . '">';

//	CHOOSE THE DATE AT ISSUE
echo '<CENTER>';
Input_Text('From Date', 'TransDate', isset($_POST['TransDate']) ? $_POST['TransDate'] : Date('Y-m-d') );
Input_Submit('Go', 'Go');

//	CHOOSE THE CARD TRANSACTION AT ISSUE
$processing_one = false;
$sql = 'SELECT * FROM CardTrans WHERE PostDate ="' . $_POST['TransDate'] . '"';
$res = DB_query($sql, $db);
while ( $found = DB_fetch_array($res) ) {
    if ( check_to_bool($_POST['SELECT_' . $found['CardTransID']]) ) {
        $processing_one = true;
        $rest = DB_query('BEGIN', $db);
        echo $found['CardTransID'] . '<br>';
        switch ( $found['CardID'] ) {
            case 'VISA':
            case 'MCRD':
            case 'DCVR': $sweepType = 'Sweep VIMC -';
                break;
            default: $sweepType = 'Sweep AmEx - ';
                break;
        }
        if ( isset($_POST['ToDate']) && ( $_POST['ToDate'] != '') ) {
            //      IF THIS TRANSACTION HAS ALREADY BEEN SWEPT, THEN GET THE INFORMATION ABOUT THE DATE IT WAS POSTED
            $findSQL = 'SELECT * FROM BankTrans WHERE Type=102 AND TransDate="' . $found['PostDate'] . '" AND Ref LIKE "' . $sweepType . '%";';
            if ( $bank_from_res = DB_fetch_array(DB_query($findSQL, $db)) ) {
                $findSQL = 'SELECT * FROM GLTrans WHERE ACCOUNT=10600 AND Type=  ' . $bank_from_res['Type'] . ' AND TypeNo=   ' . $bank_from_res['TransNo'] . ';';
                $gl_from_resDR = DB_fetch_array(DB_query($findSQL, $db));
                $findSQL = 'SELECT * FROM GLTrans WHERE ACCOUNT=10200 AND Type=  ' . $bank_from_res['Type'] . ' AND TypeNo=   ' . $bank_from_res['TransNo'] . ';';
                $gl_from_resCR = DB_fetch_array(DB_query($findSQL, $db));
            }

            //      IF THE TRANSACTIONS HAVE BEEN SWEPT ON THE POST-TO DATE THEN FIX THIS AS WELL
            $findSQL = 'SELECT * FROM BankTrans WHERE Type=102 AND TransDate="' . $_POST['ToDate'] . '";';
            $findRES = DB_query($findSQL, $db);
            if ( $bank_to_res = DB_fetch_array($findRES) ) {
                $findSQL = 'SELECT * FROM GLTrans WHERE ACCOUNT=10600 AND Type=  ' . $bank_to_res['Type'] . ' AND TypeNo=   ' . $bank_to_res['TransNo'] . ';';
                $gl_to_resDR = DB_fetch_array(DB_query($findSQL, $db));
                $findSQL = 'SELECT * FROM GLTrans WHERE ACCOUNT=10200 AND Type=  ' . $bank_to_res['Type'] . ' AND TypeNo=   ' . $bank_to_res['TransNo'] . ';';
                $gl_to_resCR = DB_fetch_array(DB_query($findSQL, $db));
            }
            if ( isset($gl_from_resDR) ) {
                $FixSQL = ' UPDATE BankTrans  SET Amount=Amount-(' . $found['Amount'] . ') WHERE BankTransID=' . $bank_from_res['BankTransID'] . ';';
                $ret = DB_query($FixSQL, $db);
                $FixSQL = ' UPDATE GLTrans    SET Amount=Amount-(' . $found['Amount'] . ') WHERE CounterIndex=' . $gl_from_resDR['CounterIndex'] . ';';
                $ret = DB_query($FixSQL, $db);
                $FixSQL = ' UPDATE GLTrans    SET Amount=Amount+(' . $found['Amount'] . ') WHERE CounterIndex=' . $gl_from_resCR['CounterIndex'] . ';';
                $ret = DB_query($FixSQL, $db);
            }
            if ( isset($gl_to_resDR) ) {
                $FixSQL = ' UPDATE BankTrans  SET Amount=Amount+(' . $found['Amount'] . ') WHERE BankTransID =' . $bank_to_res['BankTransID'] . ';';
                $ret = DB_query($FixSQL, $db);
                $FixSQL = ' UPDATE GLTrans    SET Amount=Amount-(' . $found['Amount'] . ') WHERE CounterIndex=' . $gl_to_resDR['CounterIndex'] . ';';
                $ret = DB_query($FixSQL, $db);
                $FixSQL = ' UPDATE GLTrans    SET Amount=Amount+(' . $found['Amount'] . ') WHERE CounterIndex=' . $gl_to_resCR['CounterIndex'] . ';';
                $ret = DB_query($FixSQL, $db);
            }
            $FixSQL = ' UPDATE CardTrans  SET PostDate = "' . $_POST['ToDate'] . '" WHERE CardTransID= ' . $found['CardTransID'];
            $ret = DB_query($FixSQL, $db);
            $rest = DB_query('ROLLBACK', $db);
            //   $rest = DB_query( 'COMMIT', $db);
            echo '<BR><B>MOVED</B>';
        }
        else {
            echo '<CENTER><TABLE border=1>';
            echo '<tr><th>ID</th><th>Date</th><th>Amount</th><th>CardType</th>';
            echo '<th>NewDate</th></tr>';
            echo '<tr>';
            echo '<td>' . $found['CardTransID'] . '</td>';
            echo '<td>' . $found['PostDate'] . '</td>';
            echo '<td>' . $found['Amount'] . '</td>';
            echo '<td>' . $found['CardID'] . '</td>';
            TextInput_TableCells(NULL, 'ToDate', $_POST['ToDate']);
            echo '</tr>';
            Input_Hidden('SELECT_' . $found['CardTransID'], 'on');
        }
    }
}

if ( ! $processing_one ) {
    echo '<CENTER><TABLE border=1>';
    echo '<tr><th>ID</th><th>Date</th><th>Amount</th><th>CardType</th>';
    echo '<th>Select</th></tr>';
    $res = DB_query($sql, $db);
    while ( $this_card_tran = DB_fetch_array($res) ) {
        echo '<tr>';
        echo '<td>' . $this_card_tran['CardTransID'] . '</td>';
        echo '<td>' . $this_card_tran['PostDate'] . '</td>';
        echo '<td>' . $this_card_tran['Amount'] . '</td>';
        echo '<td>' . $this_card_tran['CardID'] . '</td>';
        echo '<td>';
        Input_Check(NULL, 'SELECT_' . $this_card_tran['CardTransID'], false, true);
        echo '</td></tr>';
    }
}

echo '</TABLE></CENTER>';
echo '</FORM>';
include('includes/footer.inc');
?>
