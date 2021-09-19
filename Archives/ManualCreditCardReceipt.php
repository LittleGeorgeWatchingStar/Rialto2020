<?php

$PageSecurity = 10;

include("includes/session.inc");

$title = _('Credit Card Receipt');

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
    $CardID = $_POST['CardID'];
    $TransactionID = $_POST['TransactionID'];
    $AuthorizationCode = $_POST['AuthorizationCode'];
    $SQL_TransDate = FormatDateForSQL($_POST['ReceiptDate']);
    $TXT_TransDate = Date('d-M-Y', strtotime($_POST['ReceiptDate']));

    if ( ( $Amount != 0) && ($TransactionID != '') && ($AuthorizationCode != '') ) {
        $result = DB_query('BEGIN', $db);
        $TransNo = GetNextTransNo(12, $db);
        $PeriodNo = GetPeriod($_POST['ReceiptDate'], $db);
        $GL_SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate,PeriodNo, Account, Narrative, 	Amount) ";

        $DebtorsAccount = (check_to_bool($_POST['PrepaymentFlag']) == 1) ? 22000 : 11000;
        $SQL = $GL_SQL . "VALUES (12,'$TransNo', '$SQL_TransDate','$PeriodNo','$DebtorsAccount','Receipt','$NegAmount')";
        $ErrMsg = _('Cannot insert a GL entry for the journal line because');
        $DbgMsg = _('The SQL that failed to insert the GL Trans record was');
        $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
        echo $SQL . "<BR>";

        $SQL = $GL_SQL . "VALUES (12,'$TransNo', '$SQL_TransDate','$PeriodNo','$Account','Receipt','$Amount')";
        $ErrMsg = _('Cannot insert a GL entry for the journal line because');
        $DbgMsg = _('The SQL that failed to insert the GL Trans record was');
        $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

        $sql = "INSERT INTO CardTrans (CardTransID,Type,TransNo,TransactionID,AuthorizationCode,Amount,TransDate,TransTime,CardID,PostDate)
		      VALUES (NULL,12,'$TransNo','$TransactionID','$AuthorizationCode','$Amount','$TXT_TransDate','02:00:00PM','$CardID','$SQL_TransDate')";
        $result = DB_query($sql, $db);
        echo $sql . "<BR>";

        $SQL = " INSERT INTO DebtorTrans  ( TransNo, Type, DebtorNo, BranchCode, TranDate, Prd, Rate, OvAmount, Order_ )";
        $SQL .= " VALUES ( " . $TransNo . ", 12,'" . $_POST['DebtorNumber'] . "', '" . $_POST['BranchCode'] . "', '";
        $SQL .= FormatDateForSQL($_POST['ReceiptDate']) . "'," . $PeriodNo . ", 1.0000," . -$_POST['ReceiptAmount'];
        $SQL .= ",'" . $_POST['SalesOrderNumber'] . "')";
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
$SQL = "SELECT BranchCode FROM DebtorTrans WHERE DebtorNo='" . $customerID . "'";
$result = DB_query($SQL, $db, "Ooops 1");
$myrow = DB_fetch_row($result);
if ( $myrow[0] == '' ) {
    $SQL = "SELECT BranchCode FROM CustBranch WHERE DebtorNo='" . $customerID . "'";
    $result = DB_query($SQL, $db, "Ooops 1");
    $myrow = DB_fetch_row($result);
}
TextInput_TableRow(_("Debtor Number"), 'DebtorNumber', $customerID, 13, 15);
TextInput_TableRow(_("Branch Code"), 'BranchCode', $myrow[0], 13, 15);

echo '<tr><td>Sales Order Number</td><td>';
SelectSalesOrderForCustomer('SalesOrderNumber', $db, $customerID, $myrow[0], $_POST['SalesOrderNumber']);
echo '</td></tr>';

echo "<TR><TD>";
echo "<SELECT NAME='ReceiptAccount'>";
echo "<OPTION VALUE='10600'>Authorize.net";
echo "</SELECT></TD>";

echo "<TD>";
echo "<SELECT NAME='CardID'>";
echo "<OPTION VALUE='VISA'>Visa";
echo "<OPTION VALUE='MCRD'>Mastercard";
echo "<OPTION VALUE='AMEX'>American Express";
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
TextInput_TableRow(_("AuthorizationCode"), 'AuthorizationCode', $_POST['AuthorizationCode'], 13, 15);

echo "<TR><TD><CENTER>";
Input_Submit(_("Update"), "Update");
echo "</TD><TD><CENTER>";
Input_Submit(_("CommitReceipt"), "Enter");
echo "</TR></TD>";
echo '</TABLE>'; /* Close the main table */
echo '</FORM>';

include('includes/footer.inc');
?>
