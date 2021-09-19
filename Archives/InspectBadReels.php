<?php
/* $Revision: 1.15 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Inspect bad reels');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include("includes/WO_ui_input.inc");
include("includes/CommonGumstix.inc");
include_once("includes/labels.inc");

/*If this form is called with the StockID then it is assumed that the stock item is to be modified */

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';
echo '<center>';
Input_Submit('Refresh', 'Refresh');
Input_Submit('Remove', 'Remove' );
Input_Submit('CheckAll', 'CheckAll' );


$all_locations = array ( 7=>'Technology Drive', 8=>'Innerstep', 9=>'Bestek' );

echo '<table width=60%><tr valign=top>';

foreach ( $all_locations as $loc_code => $loc_name ) {

	echo '<td width=33%><center><b>' . $loc_name .  '</b>' ;
	echo '<table border=1>';
	$empty_reels = GetBadReels($loc_code, $db );
	foreach ($empty_reels as $id => $reel  ) {
		$stock_id = $reel['StockID'];
		$id = $reel['id'];
		$qty = $reel['Quantity'];
		if ( $stock_id != $last_stock_id) {
			$listing = '<a href="RecalcQOH.php?&StockID=' . $stock_id . '">' . $stock_id . '</a>';
			$last_stock_id =  $stock_id;
		} else {
			$listing = '';
		}
		if ( isset( $_POST['CheckAll']) ) {
			$_POST[$loc_code . '-' . $id] = true;
		}
		echo '<tr>';
		echo '<td>' . $listing . '</td>';
		Input_Check_TableCells( $id, $loc_code . '-' . $id, $_POST[$loc_code . '-' . $id], true );
		echo '</tr>';
	}
	echo '</td></table>';
}
echo '</tr></table>';

echo $change_print;

unset ( $_POST['CheckAll'] );

echo '</FORM>';
include('includes/footer.inc');
?>

