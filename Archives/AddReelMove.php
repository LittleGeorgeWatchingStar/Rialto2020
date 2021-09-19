<?php
/* $Revision: 1.15 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Change Moved Reels');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include("includes/WO_ui_input.inc");
include("includes/CommonGumstix.inc");
include("includes/labels.inc");

/*If this form is called with the StockID then it is assumed that the stock item is to be modified */

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

if ( (isset($_GET['StkMoveNo'])) && (isset($_GET['StockID']))  ) {
	$_POST['StkMoveNo'] = $_GET['StkMoveNo'];
	$_POST['StockID'] = $_GET['StockID'];
}

$StkMoveNo = $_POST['StkMoveNo'];
$StockID   = $_POST['StockID'];
$NewSerialNo = $_POST['NewReelID'];

if ( ($StkMoveNo=='') || ($StockID=='')) {
        include ('includes/footer.inc');
        exit;
}

$sql = "SELECT * FROM StockMoves
	LEFT JOIN StockSerialMoves ON StockMoves.StkMoveNo=StockSerialMoves.StockMoveNo AND StockMoves.StockID=StockSerialMoves.StockID
	WHERE StkMoveNo=$StkMoveNo AND StockMoves.StockID='$StockID' AND StockSerialMoves.StockID IS NULL";
$row = DB_fetch_array(DB_query( $sql, $db ));

if (isset($_POST['Add'])) {
	$res = DB_query('BEGIN', $db );
	$LocCode = $row['LocCode'];
	$ReelQty = $row['Qty'];
	$sql = "INSERT INTO StockSerialMoves (StockMoveNo, StockID, SerialNo, MoveQty) VALUES ('$StkMoveNo','$StockID','$NewSerialNo','$ReelQty')";
        echo $sql . '<BR>';
	$res = DB_query($sql, $db );
        $sql = 'UPDATE StockSerialItems SET Quantity=Quantity+('. $ReelQty .') WHERE SerialNo='. $NewSerialNo .' AND LocCode='. $LocCode ;
	echo $sql . '<BR>';
        $res = DB_query($sql, $db );
	$res = DB_query('COMMIT', $db );
	include ('includes/footer.inc');
	exit;
} 

echo '<center><table>';
if (isset($_POST['Select'])) {
	echo '<tr><td></td>';
	Input_Submit_TableCells('Add', 'Add ' . $StkMoveNo . ' to ' . $_POST['NewReelID']);
	echo '</tr>';
} 
TextInput_TableRow('Item', 'StockID', $StockID, 20, 20);
Input_Hidden('StkMoveNo', $StkMoveNo );
echo '<tr><td>New ReelID</td>';
echo '<td>';
EchoSelectReel("NewReelID", $db, $StockID, $row['LocCode'], $_POST["NewReelID"]);
echo '</tr></td>';
echo '<tr>';
Input_Hidden('StkItmMoveNo', $StkItmMoveNo );

echo '<tr><td></td>';
Input_Submit_TableCells('Select', 'Select');
echo '</tr>';
echo '</table>';
echo '</FORM>';
include('includes/footer.inc');
?>

