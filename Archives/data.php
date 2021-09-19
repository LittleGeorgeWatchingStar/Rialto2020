<?php
header('Content-Type: text/xml');

$PageSecurity = 10;
include("includes/session.inc");

if ( $_SERVER['HTTP_HOST'] == 'weberp.gumstix.com' ) {
	$osc_db = 'osc_gum';
} else {
	$osc_db = 'osc_dev';
}

$res_users = DB_fetch_array( DB_query( 'select count(*) as UsersOnline from ' . $osc_db . '.whos_online', $db) );
$res_sales = DB_fetch_array( DB_query( 'select count(*) as SalesOnline from ' . $osc_db . '.orders where orders_status=8', $db) );

echo '<?xml version="1.0" encoding="UTF-8" ?>
<response xml:lang="en-US">
  <users>' . $res_users['UsersOnline']  . '</users>
  <sales>' . $res_sales['SalesOnline']  . '</sales>
</response>
';
?>
