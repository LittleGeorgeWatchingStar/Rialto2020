<?php

use Rialto\SalesBundle\Entity\SalesOrder;
use Rialto\StockBundle\Entity\Location;
$PageSecurity = 2;

include('includes/session.inc');

$title = _('PDF Product Status Report');

include('includes/DateFunctions.inc');
include('includes/WO_ui_input.inc');
include_once("includes/CommonGumstix.inc");
include('includes/PDFStarter_ros.inc');

$_POST['StockLocation'] = 7;
$_POST['StockCat'] = 2;
$PageNumber=1;
include('includes/DailyInventoryHeader.inc');
//	$FontSize-=2;

$sql = "SELECT LocStock.StockID,
				StockMaster.Description,
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
			AND StockMaster.CategoryID='" . $_POST['StockCat'] . "'";
$sql .= " AND Discontinued=0 ";
$sql .= " ORDER BY StockMaster.Description ";

$ErrMsg =  _('The stock held at each location cannot be retrieved because');
$DbgMsg = _('The SQL that failed was');
$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
$line_height = 18;


while ($myrow=DB_fetch_array($LocStockResult)) {
	unset( $DemandTotal );
	$StockID = $myrow['StockID'];
	$sql = "SELECT OrderType, Sum(SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced) AS DEM
		FROM SalesOrderDetails
                LEFT JOIN SalesOrders ON SalesOrders.OrderNo = SalesOrderDetails.OrderNo
		WHERE SalesOrders.FromStkLoc='" . $myrow["LocCode"] . "'
		AND SalesOrders.SalesStage = '". SalesOrder::ORDER ."'
		AND SalesOrderDetails.Completed=0
		AND SalesOrderDetails.StkCode='" . $StockID . "'
		GROUP BY OrderType";

	$ErrMsg = _('The demand for this product from') . ' ' . $myrow["LocCode"] . ' ' . _('cannot be retrieved because');
	$DemandResult = DB_query($sql,$db,$ErrMsg);
	$DemandQty = array();
	while ($DemandRow = DB_fetch_array($DemandResult)) {
		$DemandQty[$DemandRow['OrderType']] = $DemandRow['DEM'];
		$DemandTotal += $DemandRow['DEM'];
	}

//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.
	$sql = "SELECT OrderType, Sum((SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced)*BOM.Quantity) AS DEM
              	FROM SalesOrderDetails
                LEFT JOIN SalesOrders	ON SalesOrders.OrderNo = SalesOrderDetails.OrderNo
                LEFT JOIN BOM		ON SalesOrderDetails.StkCode=BOM.Parent
                LEFT JOIN StockMaster	ON StockMaster.StockID=BOM.Parent
		WHERE	SalesOrders.FromStkLoc='" . $myrow["LocCode"] . "'
		  AND	SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced > 0
		  AND	BOM.Component='" . $StockID . "'
		  AND	StockMaster.MBflag='A'
                GROUP BY OrderType";
	$ErrMsg = _('The demand for this product from') . ' ' . $myrow["LocCode"] . ' ' . _('cannot be retrieved because');
	$DemandResult = DB_query($sql,$db, $ErrMsg);

        while ($DemandRow = DB_fetch_array($DemandResult)) {
        	$DemandQty[$DemandRow['OrderType']] += $DemandRow['DEM'];
		$DemandTotal += $DemandRow['DEM'];
        }

	$sql = "SELECT Sum(PurchOrderDetails.QuantityOrd - PurchOrderDetails.QuantityRecd) AS QOO
                FROM PurchOrderDetails
                INNER JOIN PurchOrders ON PurchOrderDetails.OrderNo=PurchOrders.OrderNo
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

	$OSC_Stock_Calc = $myrow['Quantity'] - $DemandQty['OS'];

	$osc_sql = "select products_quantity from osc_dev.products where products_model='" . strtoupper($myrow['StockID']) . "'";
	$OSC_Stock_Act = DB_fetch_array( DB_query( $osc_sql, $db ) );

//	$LeftOvers = $pdf->addTextWrap($Left_Margin      ,$YPos, 70,$FontSize,	strtoupper($myrow['StockID']),  'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin +  00,$YPos,160,$FontSize,	$myrow['Description'],  'left');
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 150,$YPos,050,$FontSize,  number_format($DemandQty['DI'],0),  'right');
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 200,$YPos,050,$FontSize,  number_format($DemandQty['OS'],0),  'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 250,$YPos,050,$FontSize,	number_format($DemandTotal,0),  'right');

	$LeftOvers = $pdf->addTextWrap($Left_Margin + 300,$YPos,050,$FontSize,	number_format($myrow['Quantity'],0),  'right');
//	$LeftOvers = $pdf->addTextWrap($Left_Margin + 350,$YPos,050,$FontSize,	number_format($myrow['ReorderLevel'],0),  'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 400,$YPos,050,$FontSize,	number_format($QOO,0),	  'right');

        $LeftOvers = $pdf->addTextWrap($Left_Margin + 450,$YPos,050,$FontSize,  number_format($OSC_Stock_Calc,0),    'right');
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 500,$YPos,050,$FontSize,  number_format($OSC_Stock_Act['products_quantity'],0),    'right');


	if ($myrow['Controlled']==1){
		$available_reels = GetAvailableReels($StockID, $db,  $myrow['LocCode'] );
		$leader = "";
		$add_bins = "";
                foreach ($available_reels as $id => $qty ) {
			if ($qty>0) {
                                $add_bins .= "$leader $id ("  . number_format($qty,0) . ")";
				$leader = ",";
			}
                }
/*		while (strlen($add_bins) > 0) {
			$add_bins = $pdf->addTextWrap($Left_Margin + 390,$YPos,160,$FontSize,$add_bins, 'left');
			if (strlen($add_bins) > 0) {
				$YPos -= ($line_height-4);
			}
		}
*/
	}
	$pdf->line($Left_Margin, $YPos-4,$Page_Width-$Right_Margin, $YPos-4);
        $YPos -= $line_height;
	unset ($DemandQty);
	if ($YPos < $Bottom_Margin + $line_height) {
        	$PageNumber++;
                include('includes/DailyInventoryHeader.inc');
        }

}
$pdfcode = $pdf->output();
$len = strlen($pdfcode);
if (isset($_GET['DontPrintMe'])) {
	header('Content-type: application/pdf');
	header('Content-Length: ' . $len);
	header('Content-Disposition: inline; filename=StockLocTrfShipment.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	$pdf->Stream();
} else {
	$filename = $reports_dir . '/' . 'DailyStockReportForTony';
	$fn = fopen($filename.'.pdf', 'wb');
	fwrite ($fn, $pdfcode);
	fclose ($fn);

	system("/usr/bin/pdftops $filename" . '.pdf');
	$fp = fsockopen("upsdymo.gumstix.com", 9100, $errno, $errstr);
	if ($fp) {
		$pshandle = fopen($filename . '.ps','rb');
	        $psdata=fread($pshandle, filesize($filename . '.ps'));
	        fwrite($fp, $psdata);
	        fclose($pshandle);
	}
	fclose($fp);
}
?>
