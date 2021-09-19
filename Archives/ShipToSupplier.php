<?php
/* $Revision: 1.18 $ */

use Rialto\CoreBundle\Entity\Company;
use Rialto\GeographyBundle\Model\Country;
require_once 'config.php';
/* Session started in session.inc for password checking and authorisation level check */
include('includes/DefineCartClass.php');
include('includes/DefineSerialItems.php');
$PageSecurity = 2;
require_once 'includes/session.inc';
$title = _('Confirm Dispatches and Invoice An Order');

include('includes/header.inc');
include_once('includes/DateFunctions.inc');
include_once('includes/SQL_CommonFunctions.inc');
include('includes/FreightCalculation.inc');
include('includes/GetSalesTransGLCodes.inc');
include_once('includes/WO_ui_input.inc');
include_once("includes/inventory_db.inc");   //	include_once('manufacturing/includes/inventory_db.inc');


if (!isset($_GET['TransferNo']) ) {
	echo "no supplier specified";
	include('includes/footer.inc');
	exit;
}

$TransferNo=$_GET['TransferNo'];
$sql	= "	SELECT * FROM LocTransfers
		LEFT JOIN StockMaster ON LocTransfers.StockID=StockMaster.StockID
		WHERE Reference='".$TransferNo."'";
$result = DB_query($sql,$db);
unset( $item_list );
$weight = 0.2;
while ($item_row=DB_fetch_array($result)) {
	if (!isset($item_list)) {
		$item_list = "STOCK ID          QTY";
		$weight +=  $item_row['KGS'] / 2.205 ;
		$supplierLoc = $item_row['RecLoc'];
	}
	$item_list .= "^" . $item_row['StockID'] . "  (" .$item_row['ShipQty'].")";
}

$sql = "SELECT * FROM Locations WHERE LocCode='".$supplierLoc."'";
$result = DB_query($sql,$db);
if (DB_num_rows($result)==1){
	$supplier = DB_fetch_array($result);
} else {
	exit;
}

echo "<TABLE BORDER=1>\n";
echo "<TR><TD CLASS='tableheader'>" . _('Name') . "</TD>
<TD CLASS='tableheader'>" . _('Position') . "</TD>
<TD CLASS='tableheader'>" . _('Phone No') . "</TD>
<TD CLASS='tableheader'>" . _('Fax No') .
"</TD></TR>\n";
do {
	printf("<TR><TD>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		</TR>",
		$myrow[1],
		$myrow[2],
		$myrow[3],
		$myrow[4] );

} while ($myrow = DB_fetch_row($result));

// 00044
//echo "<form target='blank' method='post' action='http://shipstix.gumstix.com/shipstix/shipit.php'>";
echo "<form target='blank' method='post' action='" . SITE_URI_SHIPSTIX . "/shipstix/shipit.php'>";
echo "<table>";

if ($supplier['OrderCountry'] == 'USA' || $supplier['OrderCountry'] == 'United States' ) {
	$supplier['OrderCountry'] = 'US';
}


TextInput_TableRow('Company','COMPANY_NAME',$supplier['LocationName'],20,50);
TextInput_TableRow('Attention','ATTENTION_NAME',$supplier['Contact'],20,50);
TextInput_TableRow('Address','STREET_ADDRESS',$supplier['Addr1'],20,50);
TextInput_TableRow('City','CITY',$supplier['City'],20,50);
TextInput_TableRow('State','STATE',$supplier['State'],20,50);
TextInput_TableRow('ZIP','ZIP',$supplier['Zip'],20,20);
if($supplier['Country'] == 'US' || $supplier['Country'] == 'USA' || $supplier['Country'] == 'United States')
{
        $shipment_choices = array('Ground'=>'03', '3 Day'=>'12', '2nd Day'=>'02', 'Next Day'=>'01');
        $shipment_default_type = 'Ground';
	$supplier['Country'] == 'US';
} else {
        $shipment_choices = array('Express'=>'07', 'Expedited'=>'08');
        $shipment_default_type = 'Express';
}
TextInput_TableRow('Country','COUNTRY',$supplier['Country'],20,50);
TextInput_TableRow('Phone','PHONE',preg_replace('/[a-zA-Z].*|\D/','',$supplier['Tel'] ),20,50);
echo '<tr><td>Ship Via</td><td>';
Input_Option(_('Shipping Method'),'SHIPMENT_TYPE',$shipment_choices,$shipment_default_type);
echo '</td></tr>';
TextInput_TableRow(_('Package Weight'),'PACKAGE_WEIGHT',$weight,20,50 );
TextInput_TableRow('Total','INVOICE_TOTAL',$_SESSION['Items']->total,20,50);
TextInput_TableRow('OrderNo','ORDERNO','TRF:'.$TransferNo,10,10);
$sql = "SELECT WORef FROM LocTransfersDetail WHERE LocTransfersID='".$TransferNo."'";
$result = DB_query($sql,$db);
if (DB_num_rows($result)>0) {
	$Comments = "For Build Orders:";
	while ($myrow=DB_fetch_array($result)) {
		$Comments .= "  " . $myrow['WORef'];
	}
} else {
	$Comments = "";
}
TextInput_TableRow('Comment','COMMENT',$Comments,20,50);
TextInput_TableRow('PackingList','PACKING_LIST',$item_list,40,200);
Input_Submit_TableCells('Shipping',_('Ship It'));
echo "</table>";
echo '</CENTER></FORM>';
include('includes/footer.inc');
?>

