<?php
/* $Revision: 1.12 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Ticket Explorer');

include_once('includes/header.inc');
include_once('includes/CommonGumstix.inc');
include("Test/ticket_support.inc");
?>

<link rel = "stylesheet" type = "text/css" href = "Test/ticket_styles.css"/> 
<script type = "text/javascript" src="Test/ticket_explorer.js"></script>

<?php 
include("Test/ticket_support.inc");

$col_headers = array( 'LocCode' => 0, 'LocationName'=>1 );
foreach ( $col_headers as $key => $index ) {
	if ($field_list != '')  $field_list .=',';
	$field_list .=  $key;
}
$sql = 'SELECT ' .  $field_list . ' FROM Locations';
$res = DB_query( $sql, $db );


$table = new Display_Table( $col_headers );
$table->orderHeaderColumn();

while ( $row = DB_fetch_array( $res )) {
	$table->addRow( $row );
}

$table->printJSTable();
include('includes/footer.inc');
?>
