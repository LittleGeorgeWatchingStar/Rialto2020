<?php
/* $Revision: 1.5 $ */
/* contributed by Chris Bice */

$PageSecurity = 11;
include('includes/session.inc');
$title = _('Edit Inventory Location Transfers');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include("includes/WO_ui_input.inc");

if (isset($_POST['Submit'])) {
	for ($i=0;$i<$_POST['NumOfRows'];$i++) {
		$sql =	"  UPDATE LocTransfers SET ShipQty='" . $_POST['ShipQty-' . $i] .
			"' WHERE RecQty!=ShipQty AND Reference='" . $_POST['TransferID'] .
			"' AND StockID = '" . $_POST['StockID-' . $i] . "'";
		$result = DB_query( $sql , $db, "E1", "E2" );
	}
	echo "Submitted " .$i . " rows. ";
	include('includes/footer.inc');
	exit;
}

If ( (!isset($_POST['Edit']) || !isset($_POST['TransferID'])) && !isset($_POST['Cancel'])  ){
	echo "What were you trying to do, anyway?";
    include('includes/footer.inc');
	exit;
}

$result = DB_query("SELECT * from LocTransfers WHERE ShipQty != RecQty AND Reference='" . $_POST['TransferID'] . "'",$db);
if (DB_num_rows($result)==0){
	echo "Nothing to edit on this one";
	unset($_POST['Submit']);
	unset($_POST['TransferID']);
	include('includes/footer.inc');
	exit;
}
echo "Transfer order: " .  $_POST['TransferID'] . "<BR>";
echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';
if (!isset($_POST['NumOfRows']) || isset($_POST['Cancel']) ) {
	$i=0;
	unset($_POST['Cancel'] );
	while ($thisrow = DB_fetch_array($result)) {
		$_POST["StockID-" . $i] = $thisrow['StockID'];
		$_POST["ShipQty-" . $i] = $thisrow['ShipQty'];
		$i++;
	}
	$_POST['NumOfRows']=$i;
}
echo "<CENTER><Table>";
for ($i=0;$i<$_POST['NumOfRows'];$i++) {
	echo "<TR>";
	Input_Hidden("StockID-" . $i, $_POST["StockID-" . $i]);
	TextInput_TableCells($_POST["StockID-" . $i], "ShipQty-" . $i, $_POST["ShipQty-" . $i]);
        echo "</TR>";
}
echo "</Table>";
Input_Submit("Submit", "Submit");
Input_Submit("Cancel", "Cancel");
Input_Hidden('NumOfRows', $_POST['NumOfRows'] );
Input_Hidden('TransferID', $_POST['TransferID'] );
echo '<P><A HREF="'.$rootpath.'/ShipToSupplier.php?' . SID . 'TransferNo=' . $_POST['TransferID'] . '">'. _('UPS'). '</A>';
echo "</form>";

include('includes/footer.inc');

?>
