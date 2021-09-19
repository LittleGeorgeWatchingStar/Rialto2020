<?php


//--------------------------------------------------------------------------------------

function get_bom_array( $stock_id, $db ) {
	$to_return = array();
	$bom_res = getBOM( $stock_id, $db );
	while ( $bom_item = DB_fetch_array(  $bom_res ) ) {
		$to_return[$bom_item['Component']] = $bom_item;
	}
	return $to_return;
}

