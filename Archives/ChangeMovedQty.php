<?php
/* $Revision: 1.15 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Change Quantities on Moved Reels');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include("includes/WO_ui_input.inc");
include("includes/CommonGumstix.inc");
include("includes/labels.inc");

/*If this form is called with the StockID then it is assumed that the stock item is to be modified */

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

echo '<center><table>';
if (isset($_GET['StkItmMoveNo'])) {
	$_POST['StkItmMoveNo'] = $_GET['StkItmMoveNo'];
}

$StkItmMoveNo = $_POST['StkItmMoveNo'];
$sql = "SELECT * FROM StockSerialMoves
	LEFT JOIN StockMoves ON StockMoves.StkMoveNo=StockSerialMoves.StockMoveNo
	WHERE StkItmMoveNo=$StkItmMoveNo";
$row = DB_fetch_array(DB_query( $sql, $db ));

if (isset($_POST['Change'])) {
	echo '<tr><td>';
	echo 'Changed ' . $row['MoveQty'] . ' to ' . $_POST['NewQty'] . " for $StkItmMoveNo ";
	echo '</tr></td>';
	echo '</table>';
	echo '</FORM>';
        $sql = 'UPDATE StockMoves SET Qty='. $_POST['NewQty'] . " WHERE StockID='" . $row['StockID'] . "' AND LocCode=". $row['LocCode'] . ' AND StkMoveNo=' .  $row['StockMoveNo'];
        echo $sql . '<BR>';
        $res = DB_query($sql, $db );
	include ('includes/footer.inc');
	exit;
} 

if (isset($_POST['Select'])) {
	echo '<tr><td></td>';
	Input_Submit_TableCells('Change', 'Change ' . $row['MoveQty'] . ' to ' . $_POST['NewQty']);
	$NewQty = $_POST['NewQty'];
	echo '</tr>';
} else {
	$NewQty = $row['MoveQty'];
}

TextInput_TableRow('Item', 'StockID', $row['StockID'], 20, 20);
TextInput_TableRow('NewQty', 'NewQty', $NewQty, 20, 20);
echo '<tr><td>ReelID</td><td>';
echo $row['SerialNo'];
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

