<?php
/* $Revision: 1.3 $ */

use Rialto\PurchasingBundle\Entity\Supplier;
use Rialto\PurchasingBundle\Entity\PurchasingData;
use Rialto\StockBundle\Entity\StockItem;
$PageSecurity = 5;

include ('includes/session.inc');
$title = _('List all Purchasing Data by Supplier');
include ('includes/header.inc');

function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(",", "", $str);
}

$ErrMsg = _('The SQL to get the stock quantites failed with the message');
$sql = 'SELECT PurchData.StockID, Package, PartValue, EOQ, Materialcost, SupplierNo, ManufacturerCode,Price,SuppName,LeadTime  FROM StockMaster
	INNER JOIN PurchData ON StockMaster.StockID=PurchData.StockID
	INNER JOIN Suppliers ON Suppliers.SupplierID=PurchData.SupplierNo
	WHERE Discontinued = 0 AND CategoryID=1 ORDER BY SuppName, PurchData.StockID';
$result = DB_query($sql, $db, $ErrMsg);

echo "<TABLE>";
$vName = "";
echo	"<TR>" .
        "<TH>StockID</TH>" .
        "<TH>EOQ</TH>" .
        "<TH>Cost</TH>" .
        "<TH>Package</TH>" .
        "<TH>Part</TH>" .
        "<TH></TH>" .
        "<TH>Supplier</TH>" .
        "<TH>Cost</TH>" .
	"<TH>Lead</TH>" .
	"</TR>";

while ($myrow = DB_fetch_array($result)){

	$T_line = "<TD>".$myrow["StockID"]."</TD><TD>".
		stripcomma($myrow["EOQ"])."</TD><TD>".
		stripcomma($myrow["Materialcost"])."</TD><TD>".
		stripcomma($myrow["Package"])."</TD><TD>".
		stripcomma($myrow["PartValue"])."</TD>";
	$T_line .= "<TD><A HREF=\"/index.php/record/Stock/StockItem/{$myrow['StockID']}\"> Details</A>";
	$T_line .=  "<TD>".
		$myrow['ManufacturerCode']."</TD>"."<TD>".$myrow['Price']."</TD>"."<TD>".$myrow['LeadTime']."</TD>";
        $T_line .= "<TD><A HREF=\"/index.php/record/Purchasing/PurchasingData/?stockItem={$myrow["StockID"]}\"> Purchasing</A>";
	if ($vName != $myrow['SuppName']) {
		$vName = $myrow['SuppName'];
		echo "<TR bgcolor='#CCEEEE'><TD COLSPAN=10>".$vName."</TD></TR>";
	}
	echo "<TR>".$T_line."</TR>";
}

echo "</TABLE>";

include('includes/footer.inc');

?>
