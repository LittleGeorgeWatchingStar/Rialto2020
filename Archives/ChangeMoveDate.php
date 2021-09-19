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

echo '<center><table>';

if (isset($_GET['StockMoveNo'])) {
	$_POST['StockMoveNo'] = $_GET['StockMoveNo'];
}
$StockMoveNo = $_POST['StockMoveNo'];

if ( (!isset($_POST['NewDate'])) OR ( $_POST['NewDate']=='') ) {
	if (isset($_GET['NewDate'])) {
		$NewDate =  $_GET['NewDate'];
	} else {
		$NewDate =  '2007-05-02';
	}
}
Input_Text('NewDate','NewDate',$NewDate);
Input_Hidden('StockMoveNo', $StockMoveNo );

$sql = "SELECT SM2.* FROM StockMoves SM1
	LEFT JOIN StockMoves SM2 ON SM1.Type=SM2.Type AND SM1.TransNo=SM2.TransNo
	WHERE SM1.StkMoveNo=$StockMoveNo";
$ret = DB_query( $sql, $db );
echo $sql . '<br>';

if (isset($_POST['Change'])) {
	while ($row = DB_fetch_array($ret)) {
		echo '<tr><td>';
		echo 'Changed ' . $row['TranDate'] . ' to ' . $_POST['NewDate'] . " for " . $row['StkMoveNo'];
		echo '</tr></td>';
		$sql = "UPDATE StockMoves SET TranDate='" . $_POST['NewDate'] . "' WHERE StkMoveNo=" . $row['StkMoveNo'];
	       	echo $sql . '<BR>';
	        $res = DB_query($sql, $db );
	}
	echo '</table>';
	echo '</FORM>';
	include ('includes/footer.inc');
	exit;
}
	 
//	if (isset($_POST['Select'])) {
	while ($row = DB_fetch_array($ret)) {
		echo '<tr><td>';
		echo $row['StkMoveNo'] . '</td><td>';
		echo $row['TransDate'] . '</td></tr>';
	}
	echo '</table>';
	echo $StockMoveNo . $NewDate;
	if ( ($NewDate!='') AND ($StockMoveNo!='')) {
		Input_Submit('Change', 'Change ' . $row['TransDate'] . ' to ' . $NewDate);
	}
	Input_Submit('Select', 'Select');
	echo '</FORM>';
	include('includes/footer.inc');
//	}
?>

