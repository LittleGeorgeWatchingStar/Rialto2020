<?php
/* $Revision: 1.15 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Create a new reel that hadn\'t been created before');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include("includes/WO_ui_input.inc");
include("includes/CommonGumstix.inc");
include("includes/labels.inc");

/*If this form is called with the StockID then it is assumed that the stock item is to be modified */

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';
echo '<center>';
echo '<br>Please creat the following reel<br><br>';

if (isset($_GET['StockID'])) {
	$_POST['StockID'] = $_GET['StockID'];
}

$_POST['StockID'] =  strtoupper($_POST['StockID']);
$StockID = $_POST['StockID'];

if (isset($_GET['LocCode'])) {
	$_POST['Location'] = $_GET['LocCode'];
}
$Location= $_POST['Location'];

if (isset($_GET['TranDate'])) {
        $_POST['TranDate'] = $_GET['TranDate'];
}
$unix_date= strtotime("-1 days",  strtotime( $_POST['TranDate'] ) );
$TranDate = date("Y-m-d", $unix_date );

if (isset($_GET['ReelID'])) {
        $_POST['ReelID'] = $_GET['ReelID'];
}
$ReelID = $_POST['ReelID'];

if (isset($_GET['Qty'])) {
        $_POST['Qty'] = $_GET['Qty'];
}
$Qty = $_POST['Qty'];


Input_Text('StockID', 'StockID', $StockID);
Input_Text('Location','Location',$Location);
Input_Text('TranDate','TranDate',$TranDate);
Input_Text('ReelID','ReelID',$ReelID);
Input_Text('Qty','Qty',$Qty);

echo '<BR>';

if (isset($_POST['Create'])) {
/*      Remove parts from non-reeled existence */
	$sql = "INSERT INTO StockMoves (StockID, TransNo, Type, LocCode, TranDate, Reference, Qty, Show_On_Inv_Crds, NewQOH )
		VALUES ('$StockID','$ReelID','700','$Location','$TranDate','Control stock','" . -$Qty . " ',0,'0')";
	$result = DB_query($sql, $db, $errorMsg);
	echo $sql . '<br>';

/*      Add the parts to the newly created reel */
	$sql = "INSERT INTO StockMoves (StockID, TransNo, Type, LocCode, TranDate, Reference, Qty, Show_On_Inv_Crds, NewQOH )
		VALUES ('$StockID','$ReelID','700','$Location','$TranDate','Create reel','$Qty',0,'0')";
	$result = DB_query($sql, $db, $errorMsg);
	$StockMoveNo = DB_Last_Insert_ID($db);
	echo $sql . '<br>';

	$sql = "INSERT INTO StockSerialMoves (StkItmMoveNo,StockMoveNo,StockID,SerialNo,MoveQty)
		VALUES (0, '$StockMoveNo', '$StockID', '$ReelID', '$Qty' )";
	$res = DB_query($sql, $db);
	echo $sql . '<br>';

} elseif (isset($_POST['StockID'])) {
	if ($ReelID!='') {
		echo '<BR>';
		Input_Submit('Create', 'Create' );
		echo '<BR>';
	}
}

echo '</FORM>';
include('includes/footer.inc');
?>

