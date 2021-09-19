<?php
/* $Revision: 1.4 $ */

$PageSecurity = 2;
include('includes/session.inc');
$title = _('AddStockStatus.php');
include('includes/header.inc');
include('includes/WO_ui_input.inc');
include ('includes/DateFunctions.inc');

include("includes/WO_Includes.inc");
include("includes/UI_Msgs.inc");
include_once("includes/svn_list.inc");
include("includes/manufacturing_ui.inc");

$statusChoices = array( 'New' => "New" , 'Design' =>"Design", 'Verify'=>"Verify", 'Prototype'=>"Prototype", 'Production'=>"Production" );

if ( isset($_GET['StockID']) && !isset($_POST['StockID']) ) {
	$_POST['StockID'] =  $_GET['StockID'];
}

if ( isset( $_POST['Add'] )) {
	$SQL = "INSERT INTO StockStatus ( `StockID` , `Note` , `Status` , `StatusID` )
		VALUES ( '"	.$_POST['StockID']."', '".$_POST['Note']."', '"
				.$_POST['Status']."', 0 )";
	$Result = DB_query($SQL,$db);
	unset( $_POST['Date'] );
	unset( $_POST['StockID'] );
	unset( $_POST['Note'] );
	unset( $_POST['Status'] );
        unset( $_POST['Add'] );
}

$SQL = "SELECT * FROM StockStatus WHERE 1";
$Result = DB_query($SQL,$db);
prnMsg(_('The number of invoices that will be posted is') . ' :' . DB_num_rows($Result),'info');
echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";
echo "<CENTER><TABLE>";

echo "<TR>";
echo "<TH>StatusID</TD>";
echo "<TH>StockID</TH>";
echo "<TH>Note</TH>";
echo "<TH>Status</TH>";
echo "<TH>Date</TH>";
echo "</TR>";
							
while ($StatusRecords= DB_fetch_array($Result)) {
	echo "<TR>";
	echo "<TD>".$StatusRecords['StatusID']."</TD>";
        echo "<TD>".$StatusRecords['StockID']."</TD>";
        echo "<TD>".$StatusRecords['Note']."</TD>";
        echo "<TD>".$StatusRecords['Status']."</TD>";
        echo "<TD>".$StatusRecords['Timestamp']."</TD>";
	echo "</TR>";
}

echo "<TR>";
echo "<TD COLSPAN=5><CENTER><TABLE><TR>";
echo "<TH COLSPAN=2>Add status history record</TH>";
echo "</TR><TR><TD>StockID</TD><TD>";
stockBOMItemsList('StockID', $db, $_POST['StockID'], false, false );
//	TextInput_TableCells( "StockID", 'StockID', $_POST['StockID'], 21, "StockID");

echo "</TR><TR><TD>Status</TD><TD>";
Input_Option("Status",'Status',$statusChoices);
//	TextInput_TableCells( "Status", 'Status', $_POST['Status'],20,20);
echo "</TD></TR><TR>";
TextInput_TableCells( "Note", 'Note', $_POST['Note'], 50, 50);
echo "</TR><TR><TD COLSPAN=2><CENTER>";
Input_Submit("Add","Add");
echo "</TABLE></TD></TABLE></FORM>";

include('includes/footer.inc');
?>
