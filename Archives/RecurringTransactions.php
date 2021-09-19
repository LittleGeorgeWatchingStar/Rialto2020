<?php
/* $Revision: 1.4 $ */

use Rialto\PurchasingBundle\Entity\Supplier;
$PageSecurity = 2;
include('includes/session.inc');
$title = _('RecurringTransactions.php');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include_once('includes/SQL_CommonFunctions.inc');
include('includes/WO_ui_input.inc');
include('includes/WO_Includes.inc');
include_once("includes/inventory_db.inc");   //include_once('manufacturing/includes/inventory_db.inc');

if ( isset( $_POST['Delete'] )) {
	$SQL = "DELETE FROM RecurringInvoices WHERE RecurringID=".$_POST['ID2Delete'];
	$Result = DB_query($SQL,$db);
	$SQL = "DELETE FROM RecurringGLInvoices WHERE RecurringID=".$_POST['ID2Delete'];
	$Result = DB_query($SQL,$db);
	unset( $_POST['ID2Delete'] );
        unset( $_POST['Delete'] );
}


if ( isset( $_POST['Add'] )) {
	$SQL = "INSERT INTO RecurringInvoices  ( `RecurringID` , `SupplierNo` , `SuppReference` , `Dates` , `OvAmount` )
		VALUES ( '', '"	.$_POST['NewSupplierNo']."', '".$_POST['NewSuppReference']."', '"
				.$_POST['NewDates']."', '".$_POST['NewOvAmount']."')";
	$Result = DB_query($SQL,$db);
	unset( $_POST['NewSupplierNo'] );
	unset( $_POST['NewSuppReference'] );
	unset( $_POST['NewDates'] );
	unset( $_POST['NewOvAmount'] );
        unset( $_POST['Add'] );
}

$SQL = "SELECT * FROM RecurringInvoices WHERE 1";
$Result = DB_query($SQL,$db);
echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";
echo "<CENTER><TABLE>";

echo "<TR>";
echo "<TH>ID</TD>";
echo "<TH>Supplier</TH>";
echo "<TH>SuppReference</TH>";
echo "<TH>Dates</TH>";
echo "<TH>OvAmount</TH>";
echo "</TR>";
							
while ($Invoice = DB_fetch_array($Result)) {
	echo "<TR>";
	echo "<TD>".$Invoice['RecurringID']."</TD>";
        echo "<TD>".get_SupplierName($Invoice['SupplierNo'],$db)."</TD>";
        echo "<TD>".$Invoice['SuppReference']."</TD>";
        echo "<TD>".$Invoice['Dates']."</TD>";
	echo "<TD><A HREF=RecurringDetails.php?RecurringID=".$Invoice['RecurringID'].">".$Invoice['OvAmount']."</A></TD>";
	echo "</TR>";
}


echo "<TR>";
echo "<TD COLSPAN=5><CENTER><TABLE><TR>";
echo "<TH COLSPAN=2>Add a new recurring transaction here</TH>";
echo "</TR><TR>";
TextInput_TableCells( "SupplierNo", 'NewSupplierNo', $_POST['NewSupplierNo'], 13, "Stencil");
echo "</TR><TR>";

TextInput_TableCells( "SuppReference", 'NewSuppReference', $_POST['NewSuppReference'],19,19);
echo "</TR><TR>";
TextInput_TableCells( "Dates", 'NewDates', $_POST['NewDates'], 9, 9);
echo "</TR><TR>";
TextInput_TableCells( "OvAmount", 'NewOvAmount', $_POST['NewOvAmount'], 9, 9);
echo "</TR><TR><TD COLSPAN=2><CENTER>";
Input_Submit("Add","Add");
echo "<TR><TH COLSPAN=2>Delete the following transaction here</TH>";
echo "</TR><TR>";
TextInput_TableCells( "RecurringID To Delete", 'ID2Delete', '', 9, 9);
echo "<TR><TD COLSPAN=2><CENTER>";
Input_Submit("Delete","Delete");
echo "</TD></TR>";
echo "</TABLE></TD></TABLE></FORM>";

include('includes/footer.inc');
?>
