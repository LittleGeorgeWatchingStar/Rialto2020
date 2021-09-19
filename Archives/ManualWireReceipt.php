<?php

$PageSecurity = 10;

include("includes/session.inc");

$title = _('Manual Wire Receipt');

include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/WO_ui_input.inc");
include("includes/UI_Msgs.inc");
include('includes/SQL_CommonFunctions.inc');
include('includes/CommonGumstix.inc');

if ( isset($_POST['CommitReceipt']) ) {
    $Amount = $_POST['ReceiptAmount'];
    $Account = $_POST['ReceiptAccount'];
    $NegAmount = -$Amount;
    $TransactionID = $_POST['TransactionID'];
    $SQL_TransDate = FormatDateForSQL($_POST['ReceiptDate']);
    $TXT_TransDate = Date('d-M-Y', strtotime($_POST['ReceiptDate']));

    if ( ( $Amount != 0) && ($TransactionID != '') ) {
        $result = DB_query('BEGIN', $db);
        $TransNo = GetNextTransNo(12, $db);
        $PeriodNo = GetPeriod($_POST['ReceiptDate'], $db);
        $GL_SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate,PeriodNo, Account, Narrative, 	Amount) ";


        $DebtorsAccount = (check_to_bool($_POST['PrepaymentFlag']) == 1) ? 22000 : 11000;
        $SQL = $GL_SQL . "VALUES (12,'$TransNo', '$SQL_TransDate','$PeriodNo','$DebtorsAccount','Receipt','$NegAmount')";
        $ErrMsg = _('Cannot insert a GL entry for the journal line because');
        $DbgMsg = _('The SQL that failed to insert the GL Trans record was');
        $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

        $SQL = $GL_SQL . "VALUES (12,'$TransNo', '$SQL_TransDate','$PeriodNo','$Account','Receipt','$Amount')";
        $ErrMsg = _('Cannot insert a GL entry for the journal line because');
        $DbgMsg = _('The SQL that failed to insert the GL Trans record was');
        $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

        $sql = "INSERT INTO BankTrans (Type,TransNo,Amount,BankAct,Ref,TransDate,BankTransType,CurrCode,ExRate)
                      VALUES (12,'$TransNo','$Amount','$Account','$TransactionID','$SQL_TransDate','Direct credit','USD','1.0000')";
        $result = DB_query($sql, $db);

        $SQL = " INSERT INTO DebtorTrans  ( TransNo, Type, DebtorNo, BranchCode, TranDate, Prd, Rate, OvAmount";
        if ( $_POST['SalesOrderNumber'] != '' ) {
            $SQL .= ", Order_ ";
        }
        $SQL .= ")";
        $SQL .= " VALUES ( " . $TransNo . ", 12," . $_POST['DebtorNumber'] . ", " . $_POST['BranchCode'] . ", '";
        $SQL .= FormatDateForSQL($_POST['ReceiptDate']) . "'," . $PeriodNo . ", 1.0000," . -$_POST['ReceiptAmount'];
        if ( $_POST['SalesOrderNumber'] != '' ) {
            $SQL .= ",'" . $_POST['SalesOrderNumber'] . "'";
        }
        $SQL .= ")";
        $ErrMsg = _('Cannot commit the changes');
        $DbgMsg = _('The SQL that failed to insert the GL Trans record was');
        $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

        $result = DB_query('COMMIT', $db, $ErrMsg, _('The commit database transaction failed'), true);
        prnMsg(_('Journal') . ' ' . $TransNo . ' ' . _('has been sucessfully entered'), 'success');
        include('includes/footer.inc');
        exit;
    }
}

if ( ! isset($_GET['ReceiptDate']) || $invalid_date ) {
    $_POST['ReceiptDate'] = Date("m/d/Y");
}
echo "<BR>";
echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . ' METHOD=POST>';
echo '<CENTER><TABLE BORDER=1>';
$customerID = $_SESSION['CustomerID'];
if ( ! $customerID ) {
    $customerID = $_GET['CustomerID'];
}
$SQL = "SELECT BranchCode,BrName  FROM CustBranch WHERE DebtorNo='" . $customerID . "'";
$result = DB_query($SQL, $db, "Ooops 1");
TextInput_TableRow(_("Debtor Number"), 'DebtorNumber', $customerID, 13, 15);

echo "<TR><TD>Branch Code </TD><TD>";
echo "<SELECT NAME='BranchCode'>";
while ( $myrow = DB_fetch_row($result) ) {
    if ( $_POST['BranchCode'] == $myrow[0] ) {
        $sel = ' SELECTED ';
    }
    else {
        $sel = ' ';
    }
    echo "<OPTION " . $sel . " VALUE='" . $myrow[0] . "'>" . $myrow[0] . ' - ' . $myrow[1];
}
echo "</SELECT></TD></TR>";

echo '<tr><td>Sales Order Number</td><td>';
SelectSalesOrderForCustomer('SalesOrderNumber', $db, $customerID, $_POST['BranchCode'], $_POST['SalesOrderNumber']);
echo '</td></tr>';

echo "<TR><TD>";
echo "<SELECT NAME='ReceiptAccount'>";
echo "<OPTION VALUE='10200'>SVB Checking";
echo "</SELECT></TD>";
echo '<td>';
if ( ! isset($_POST['PrepaymentFlag']) ) {
    $_POST['PrepaymentFlag'] = 'on';
}
Input_PreCheck('This is a prepayment', 'PrepaymentFlag', $_POST['PrepaymentFlag']);
echo '</td></tr>';

DateInput_TableRow(_("Date"), 'ReceiptDate', $_POST['ReceiptDate'], 13, 15);
TextInput_TableRow(_("Amount"), 'ReceiptAmount', $_POST['ReceiptAmount'], 13, 15);
TextInput_TableRow(_("Transaction ID"), 'TransactionID', $_POST['TransactionID'], 13, 15);

echo "<TR><TD><CENTER>";
Input_Submit(_("Update"), "Update");
echo "</TD><TD><CENTER>";
Input_Submit(_("CommitReceipt"), "Enter");
echo "</TR></TD>";
echo '</TABLE>'; /* Close the main table */
echo '</FORM>';

include('includes/footer.inc');
?>
