<?php

/* $Revision: 1.4 $ */

$PageSecurity = 2;
include('includes/session.inc');
$title = _('Details of Recurring Transactions');
include('includes/header.inc');
include('includes/WO_ui_input.inc');
include ('includes/DateFunctions.inc');

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";
if ( isset($_GET['RecurringID']) ) {
    $_POST['RecurringID'] = $_GET['RecurringID'];
    $RecurringID = $_POST['RecurringID'];
}

if ( !isset($_POST['RecurringID']) ) {
    echo "<CENTER><TABLE>";
    echo "<TR>";
    TextInput_TableCells("RecurringID", 'RecurringID', $_POST['RecurringID'], 9, 9);
    echo "</TR><TR><TD>";
    Input_Submit("OK", "OK");
    echo "</TD></TR>";
    echo "</TABLE></FORM>";
}
else {
    $RecurringID = $_POST['RecurringID'];
    if ( isset($_POST['Post']) ) {
        $SQL = "SELECT * FROM RecurringGLInvoices WHERE RecurringID=" . $_POST['RecurringID'];
        $Result = DB_query($SQL, $db);
        while ( $Invoice = DB_fetch_array($Result) ) {
            if ( ($_POST['Account' . $Invoice['ID']] == "") && ($_POST['Amount' . $Invoice['ID']] == "") ) {
                $gSQL = " DELETE FROM RecurringGLInvoices ";
            }
            else {
                $gSQL = " UPDATE RecurringGLInvoices SET " .
                    " Account	='" . $_POST['Account' . $Invoice['ID']] . "', " .
                    " Amount	='" . $_POST['Amount' . $Invoice['ID']] . "'," .
                    " Reference	='" . $_POST['Reference' . $Invoice['ID']] . "' ";
            }
            $gSQL .= " WHERE	ID	='" . $Invoice['ID'] . "'";
            $newresult = DB_query($gSQL, $db);
        }
        if ( $_POST['AmountNEW'] != 0 ) {
            $SQL = " INSERT INTO RecurringGLInvoices ( RecurringID, Account, Amount,Reference) VALUES ( " .
                "'" . $_POST['RecurringID'] . "', " .
                "'" . $_POST['AccountNEW'] . "', " .
                "'" . $_POST['AmountNEW'] . "', " .
                "'" . $_POST['ReferenceNEW'] . "'  ); ";
            $newresult = DB_query($SQL, $db);
        }
        $SQL = " SELECT SUM(Amount) OvAmount From RecurringGLInvoices WHERE RecurringID =" . $RecurringID;
        $Result = DB_fetch_array(DB_query($SQL, $db));
        if ( !isset($Result['OvAmount']) ) {
            $Result['OvAmount'] = 0;
        }
        $SQL = " UPDATE RecurringInvoices SET OvAmount = " . $Result['OvAmount'] . " WHERE RecurringID=" . $RecurringID;
        $Result = DB_query($SQL, $db);
        unset($_POST['Post']);
        unset($_POST['AccountNEW']);
        unset($_POST['ReferenceNEW']);
        unset($_POST['AmountNEW']);
    }
    elseif ( isset($_POST['Cancel']) ) {
        unset($_POST['AccountNEW']);
        unset($_POST['ReferenceNEW']);
        unset($_POST['AmountNEW']);
        unset($_POST['Cancel']);
    }
}

echo "<CENTER><TABLE>";

echo "<TR>";
echo "<TD>ID</TD>";
echo "<TD>Account</TD>";
echo "<TD>Reference</TD>";
echo "<TD>Amount</TD>";
echo "</TR>";

$sum = 0;
$SQL = "SELECT * FROM RecurringGLInvoices WHERE RecurringID=" . $_POST['RecurringID'];
$Result = DB_query($SQL, $db);
while ( $Invoice = DB_fetch_array($Result) ) {
    if ( !isset($_POST['Account' . $Invoice['ID']]) || ( isset($_POST['Cancel'])) ) {
        $_POST['Account' . $Invoice['ID']] = $Invoice['Account'];
        $_POST['Amount' . $Invoice['ID']] = $Invoice['Amount'];
        $_POST['Reference' . $Invoice['ID']] = $Invoice['Reference'];
    }
    $sum += $_POST['Amount' . $Invoice['ID']];
    echo "<TR>";
    echo "<TD align=center>" . $Invoice['ID'] . "</TD>";
    TextInput_TableCells("", 'Account' . $Invoice['ID'], $_POST['Account' . $Invoice['ID']], 9, 9);
    TextInput_TableCells("", 'Reference' . $Invoice['ID'], $_POST['Reference' . $Invoice['ID']], 20, 20);
    TextInput_TableCells("", 'Amount' . $Invoice['ID'], $_POST['Amount' . $Invoice['ID']], 9, 9);
    echo "</TR>";
}

echo "<TR>";
echo "<TD align=center>New</TD>";
TextInput_TableCells("", 'AccountNEW', $_POST['AccountNEW'], 9, 9);
TextInput_TableCells("", 'ReferenceNEW', $_POST['ReferenceNEW'], 20, 20);
TextInput_TableCells("", 'AmountNEW', $_POST['AmountNEW'], 9, 9);
echo "</TR>";
$sum += $_POST['AmountNEW'];
echo "<TR BGCOLOR='LTYELLOW'><TD></TD><TD></TD>";
echo "<TD><CENTER>Total</TD>";
echo "<TD><RIGHT>$sum</TD>";
echo "</TR>";

Input_Hidden("RecurringID", $_POST['RecurringID']);
echo "<TR><TD></TD>";
echo "<TD><CENTER>";
Input_Submit("Update", "Update");
echo "</TD>";
echo "<TD><CENTER>";
Input_Submit("Post", "Post");
echo "</TD>";
echo "<TD><CENTER>";
Input_Submit("Cancel", "Cancel");
echo "</TD>";
echo "</TR>";
echo "</TABLE></FORM>";

include('includes/footer.inc');
?>
