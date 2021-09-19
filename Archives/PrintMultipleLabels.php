<?php
/* $Revision: 1.15 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Print Controlled Item Labels');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include("includes/WO_ui_input.inc");
include("includes/CommonGumstix.inc");
include("includes/labels.inc");

/*If this form is called with the StockID then it is assumed that the stock item is to be modified */

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

echo '<center><table>';
if (isset($_GET['StockID'])) {
	$_POST['StockID'] = $_GET['StockID'];
}
$_POST['StockID'] = strtoupper($_POST['StockID']);

$available_reels = GetAvailableReels($_POST['StockID'], $db, 7);
$sql = "SELECT StockID, SUM(Quantity) Sums FROM StockSerialItems GROUP BY StockID HAVING SUM(Quantity) != 0 ";
$res = DB_query($sql, $db);
while ( $row = DB_fetch_array($res)) {
	$StockID = $row['StockID'];
	if ( $_POST['SelectAll']) {
		$_POST[$StockID] = true;
	}
	if ( $_POST['ClearAll']) {
		$_POST[$StockID] = false;
	} 
        Input_Check_TableRow( $StockID. '&nbsp<i>('  . number_format($row['Sums']) . ')</i>', $StockID, $_POST[$StockID], true );
	if (check_to_bool($_POST[$StockID]) && isset($_POST['Print']) ) {
		print_label( "",$StockID, number_format($row['Sums']), Date("d-M-y") ); 
	}
}
echo '<tr>';
Input_Submit_TableCells('Print', 'Print' );
Input_Submit_TableCells('Show', 'Show');
Input_Submit_TableCells('SelectAll', 'SelectAll');
Input_Submit_TableCells('ClearAll', 'ClearAll');

echo '<tr>';
echo '</table>';
echo '</FORM>';
include('includes/footer.inc');
?>

