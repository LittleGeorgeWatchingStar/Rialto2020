<?php

use Rialto\StockBundle\Entity\StockLevel;
use Rialto\StockBundle\Entity\Location;
use Rialto\StockBundle\Entity\StockCategory;
$PageSecurity = 2;

include('includes/session.inc');

$title = _('All Stock Status By Location/Category');

include('includes/header.inc');
include('includes/DateFunctions.inc');
include("includes/WO_ui_input.inc");
include("includes/WO_Includes.inc");
include("includes/UI_Msgs.inc");
include("includes/manufacturing_ui.inc");

if (isset($_GET['StockID'])){
	$StockID =$_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID =$_POST['StockID'];
}


echo '<HR><FORM ACTION="' . $_SERVER['PHP_SELF'] . '?'. SID . '" METHOD=POST>';

$sql = "SELECT LocCode,
		LocationName
	FROM Locations";
$resultStkLocs = DB_query($sql,$db);

echo '<TABLE><TR><TD>';

echo '<TABLE><TR><TD>' . _('From Stock Location') . ':</TD><TD><SELECT name="StockLocation"> ';
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow["LocCode"] == $_POST['StockLocation']){
		     echo '<OPTION SELECTED Value="' . $myrow["LocCode"] . '">' . $myrow["LocationName"];
		} else {
		     echo '<OPTION Value="' . $myrow["LocCode"] . '">' . $myrow["LocationName"];
		}
	} elseif ($myrow['LocCode']==$_SESSION['UserStockLocation']){
		 echo '<OPTION SELECTED Value="' . $myrow['LocCode'] . '">' . $myrow['LocationName'];
		 $_POST['StockLocation']=$myrow["LocCode"];
	} else {
		 echo '<OPTION Value="' . $myrow['LocCode'] . '">' . $myrow['LocationName'];
	}
}
echo '</SELECT></TD></TR>';

$SQL="SELECT CategoryID, CategoryDescription FROM StockCategory ORDER BY CategoryDescription";
$result1 = DB_query($SQL,$db);
if (DB_num_rows($result1)==0){
	echo '</TABLE></TD></TR>
		</TABLE>
		<P>';
	prnMsg(_('There are no stock categories currently defined please use the link below to set them up'),'warn');
	echo '<BR><A HREF="/index.php/record/Stock/StockCategory/">' . _('Define Stock Categories') . '</A>';
	include ('includes/footer.inc');
	exit;
}

echo '<TR><TD>' . _('In Stock Category') . ':</TD><TD><SELECT NAME="StockCat">';
if (!isset($_POST['StockCat'])){
	$_POST['StockCat']='All';
}
if ($_POST['StockCat']=='All'){
	echo '<OPTION SELECTED VALUE="All">' . _('All');
} else {
	echo '<OPTION VALUE="All">' . _('All');
}
while ($myrow1 = DB_fetch_array($result1)) {
	if ($myrow1['CategoryID']==$_POST['StockCat']){
		echo '<OPTION SELECTED VALUE="' . $myrow1['CategoryID'] . '">' . $myrow1['CategoryDescription'];
	} else {
		echo '<OPTION VALUE="' . $myrow1['CategoryID'] . '">' . $myrow1['CategoryDescription'];
	}
}

echo '</SELECT></TD></TR></TABLE>';



echo '</TD><TD VALIGN=CENTER><INPUT TYPE=SUBMIT NAME="ShowStatus" VALUE="' . _('Show Stock Status') . '">';

echo '</TD></TR></TABLE>';
echo '<HR>';


if (isset($_POST['ShowStatus'])){

	if ($_POST['StockCat']=='All') {
		$sql = "SELECT LocStock.StockID,
				StockMaster.Description,
				Materialcost + Labourcost + Overheadcost AS COGS,
				LocStock.LocCode,
				Locations.LocationName,
				LocStock.Quantity,
				LocStock.ReorderLevel,
				StockMaster.DecimalPlaces,
				StockMaster.Serialised,
				StockMaster.Controlled
			FROM LocStock, StockMaster, Locations
			WHERE LocStock.StockID=StockMaster.StockID
			AND LocStock.LocCode = '$_POST[StockLocation]'
			AND LocStock.LocCode=Locations.LocCode
			AND (StockMaster.MBFlag='B' OR StockMaster.MBFlag='M')
			ORDER BY LocStock.StockID";
	} else {
		$sql = "SELECT LocStock.StockID,
				StockMaster.Description,
				Materialcost + Labourcost + Overheadcost AS COGS,
				LocStock.LocCode,
				Locations.LocationName,
				LocStock.Quantity,
				LocStock.ReorderLevel,
				StockMaster.DecimalPlaces,
				StockMaster.Serialised,
				StockMaster.Controlled
			FROM LocStock, StockMaster, Locations
			WHERE LocStock.StockID=StockMaster.StockID
			AND LocStock.LocCode = '$_POST[StockLocation]'
			AND LocStock.LocCode=Locations.LocCode
			AND (StockMaster.MBFlag='B' OR StockMaster.MBFlag='M')
			AND StockMaster.CategoryID='" . $_POST['StockCat'] . "'
			ORDER BY LocStock.StockID";
	}


	$ErrMsg =  _('The stock held at each location cannot be retrieved because');
	$DbgMsg = _('The SQL that failed was');
	$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	echo '<TABLE CELLPADDING=5 CELLSPACING=4 BORDER=0>';

	$tableheader = '<TR>
			<TD class="tableheader">' . _('StockID') . '</TD>
			<TD class="tableheader">' . _('Description') . '</TD>
                        <TD class="tableheader">' . _('Quantity On Hand') . '</TD>
			<TD class="tableheader">' . _('Per StockMoves') . '</TD>
			<TD class="tableheader">' . _('Valuation') . '</TD>
			<TD class="tableheader">' . _('Re-Order Level') . '</FONT></TD>
			<TD class="tableheader">' . _('Demand') . '</TD>
			<TD class="tableheader">' . _('Available') . '</TD>
			<TD class="tableheader">' . _('On Order') . '</TD>
			</TR>';
	echo $tableheader;
	$j = 1;
	$k=0; //row colour counter
	$sumCOGS = 0;
	while ($myrow=DB_fetch_array($LocStockResult)) {

		if ($k==1){
			$rostarter = '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			$rostarter =  '<tr bgcolor="#EEEEEE">';
			$k=1;
		}

		$StockID = $myrow['StockID'];

		$sql = "SELECT Sum(SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced) AS DEM
                   	FROM SalesOrderDetails,
                        	SalesOrders
                   	WHERE SalesOrders.OrderNo = SalesOrderDetails.OrderNo
			AND SalesOrders.FromStkLoc='" . $myrow["LocCode"] . "'
			AND SalesOrderDetails.Completed=0
			AND SalesOrderDetails.StkCode='" . $StockID . "'";

		$ErrMsg = _('The demand for this product from') . ' ' . $myrow["LocCode"] . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($sql,$db,$ErrMsg);

		if (DB_num_rows($DemandResult)==1){
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty =  $DemandRow[0];
		} else {
			$DemandQty =0;
		}

		//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.
		$sql = "SELECT Sum((SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced)*BOM.Quantity) AS DEM
                   	FROM SalesOrderDetails,
                        	SalesOrders,
                        	BOM,
                        	StockMaster
                   	WHERE SalesOrderDetails.StkCode=BOM.Parent
			AND SalesOrders.OrderNo = SalesOrderDetails.OrderNo
			AND SalesOrders.FromStkLoc='" . $myrow["LocCode"] . "'
			AND SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced > 0
			AND BOM.Component='" . $StockID . "'
			AND StockMaster.StockID=BOM.Parent
			AND StockMaster.MBflag='A'";

		$ErrMsg = _('The demand for this product from') . ' ' . $myrow["LocCode"] . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($sql,$db, $ErrMsg);

		if (DB_num_rows($DemandResult)==1){
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty += $DemandRow[0];
		}

		$sql = "SELECT Sum(PurchOrderDetails.QuantityOrd - PurchOrderDetails.QuantityRecd) AS QOO
                   	FROM PurchOrderDetails
                   	INNER JOIN PurchOrders
                   		ON PurchOrderDetails.OrderNo=PurchOrders.OrderNo
                   	WHERE PurchOrders.IntoStockLocation='" . $myrow["LocCode"] . "'
			AND PurchOrderDetails.ItemCode='" . $StockID . "'";

		$ErrMsg = _('The quantity on order for this product to be received into') . ' ' . $myrow["LocCode"] . ' ' . _('cannot be retrieved because');
		$QOOResult = DB_query($sql,$db,$ErrMsg);

		if (DB_num_rows($QOOResult)==1){
			$QOORow = DB_fetch_row($QOOResult);
			$QOO =  $QOORow[0];
		} else {
			$QOOQty = 0;
		}

		$QOH=getQOHfromStockMoves($myrow['StockID'], $db, $myrow['LocCode'] );


		if (round($myrow['Quantity'],0)==0 || round($QOH, 0)==0 ) {
		} else {

		if ( round($myrow['Quantity'] - $QOH, 0) != 0 ) {
			$rostarter = '<tr bgcolor="#CC22FF">';
	 	}

		echo $rostarter;

		printf("<td><a target='_blank' href='/index.php/record/Stock/StockLevel/?stockItem=%s'>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
                        <td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>",
			strtoupper($myrow['StockID']),
			strtoupper($myrow['StockID']),
			$myrow['Description'],
			number_format($myrow['Quantity'],0),
			number_format($QOH,0),
			number_format($myrow['COGS']*$myrow['Quantity'],0),
			number_format($myrow['ReorderLevel'],0),
			number_format($DemandQty,0),
			number_format($myrow['Quantity'] - $DemandQty,0),
			number_format($QOO,0));
		$sumCOGS += $myrow['COGS']*$myrow['Quantity'];
		if ($myrow['Serialised'] ==1){ /*The line is a serialised item*/

			echo '<TD><A target="_blank" HREF="' . $rootpath . '/StockSerialItems.php?' . SID . '&Serialised=Yes&Location=' . $myrow['LocCode'] . '&StockID=' . $StockID . '">' . _('Serial Numbers') . '</A></TD></TR>';
		} elseif ($myrow['Controlled']==1){
			echo '<TD><A target="_blank" HREF="' . $rootpath . '/StockSerialItems.php?' . SID . '&Location=' . $myrow['LocCode'] . '&StockID=' . $StockID . '">' . _('Batches') . '</A></TD></TR>';
		}

		$j++;
		If ($j == 12){
			$j=1;
			echo $tableheader;
		}
		}
	//end of page full new headings if
	}
	//end of while loop
	printf("<tr><td></td>
			<td></td>
			<td ALIGN=RIGHT></td>
			<td ALIGN=RIGHT></td>
                        <td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT></td>
			<td ALIGN=RIGHT></td>
			<td ALIGN=RIGHT></td>
			<td ALIGN=RIGHT></td></tr>",
			number_format($sumCOGS,0) ) ;

	echo '</TABLE><HR>';
	echo '</form>';
} /* Show status button hit */
include('includes/footer.inc');

?>
