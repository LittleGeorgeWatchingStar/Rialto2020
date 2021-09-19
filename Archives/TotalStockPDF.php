<?php

/* $Revision: 1.5 $ */
$PageSecurity = 2;

require_once 'includes/session.inc';
include('includes/PDFStarter_ros.inc');
include('includes/DateFunctions.inc');
include('includes/WO_ui_input.inc');
include_once("includes/CommonGumstix.inc");


$FontSize = 10;
$pdf->addinfo('Title', _('Total Stock Report'));
$pdf->addinfo('Subject', _('Stock Sheet'));

$PageNumber = 1;
$line_height = 16;

$SQL = "SELECT StockMaster.CategoryID,
				LocStock.StockID,
				StockMaster.Description,
				LocStock.Quantity,
				Locations.LocationName,
				Locations.LocCode,
				LocStock.Quantity
              FROM LocStock
		     LEFT  JOIN StockMaster ON LocStock.StockID=StockMaster.StockID
		     LEFT  JOIN Locations   ON LocStock.LocCode=Locations.LocCode
                 WHERE StockMaster.MBflag='B'  AND StockMaster.Discontinued=0 AND  LocStock.LocCode !=99
                 ORDER BY StockMaster.StockID,	Locations.LocCode";

$InventoryResult = DB_query($SQL, $db, '', '', false, false);

if ( DB_error_no($db) != 0 ) {
    $title = _('Stock Sheets') . ' - ' . _('Problem Report') . '.... ';
    include('includes/header.inc');
    prnMsg(_('The inventory quantities could not be retrieved by the SQL because') . ' ' . DB_error_msg($db), 'error');
    echo '<BR><A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
    if ( $debug == 1 ) {
        echo '<BR>' . $SQL;
    }
    include ('includes/footer.inc');
    exit;
}

include ('includes/PDFTotalStockHeader.inc');

$Category = '';
$lastLocCode = 0;
While ( $InventoryPlan = DB_fetch_array($InventoryResult, $db) ) {

    $FontSize = 10;

    $SQL = "SELECT Sum(SalesOrderDetails.Quantity - SalesOrderDetails.QtyInvoiced)
                   AS QtyDemand
                   FROM SalesOrderDetails,
                        SalesOrders
                   WHERE SalesOrderDetails.OrderNo=SalesOrders.OrderNo AND
                   SalesOrders.FromStkLoc ='" . $_POST["Location"] . "' AND
                   SalesOrderDetails.StkCode = '" . $InventoryPlan['StockID'] . "'  AND
                   SalesOrderDetails.Completed = 0";

    $DemandResult = DB_query($SQL, $db, '', '', false, false);

    if ( DB_error_no($db) != 0 ) {
        $title = _('Stock Check Sheets') . ' - ' . _('Problem Report') . '.... ';
        include('includes/header.inc');
        prnMsg(_('The sales order demand quantities could not be retrieved by the SQL because') . ' ' . DB_error_msg($db), 'error');
        echo '<BR><A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
        if ( $debug == 1 ) {
            echo '<BR>' . $SQL;
        }
        echo '</body</html>';
        exit;
    }

    $DemandRow = DB_fetch_array($DemandResult);
    $DemandQty = $DemandRow['QtyDemand'];

    //Also need to add in the demand for components of assembly items
    $sql = "SELECT Sum((SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced)*BOM.Quantity)
                   AS DEM
                   FROM SalesOrderDetails,
                        SalesOrders,
                        BOM,
                        StockMaster
                   WHERE SalesOrderDetails.StkCode=BOM.Parent AND
                   SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND
                   SalesOrders.FromStkLoc='" . $myrow['LocCode'] . "' AND
                   SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced > 0 AND
                   BOM.Component='" . $StockID . "' AND
                   StockMaster.StockID=BOM.Parent AND
                   StockMaster.MBFlag='A'";

    $DemandResult = DB_query($sql, $db, '', '', false, false);
    if ( DB_error_no($db) != 0 ) {
        prnMsg(_('The demand for this product from') . ' ' . $myrow['LocCode'] . ' ' . _('cannot be retrieved because') . ' - ' . DB_error_msg($db), 'error');
        if ( $debug == 1 ) {
            echo '<BR>' . _('The SQL that failed was') . ' ' . $sql;
        }
        exit;
    }

    if ( DB_num_rows($DemandResult) == 1 ) {
        $DemandRow = DB_fetch_row($DemandResult);
        $DemandQty += $DemandRow[0];
    }

    $locOffset = 280 + 60 * ($InventoryPlan['LocCode'] - 7);
    if ( $lastStockID != $InventoryPlan['StockID'] ) {
        $YPos -= $running_inside_offset;
        $pdf->line($Left_Margin, $YPos - 5, $Page_Width - $Right_Margin, $YPos - 5);
        $running_inside_offset = 0;
        $lastStockID = $InventoryPlan['StockID'];

        if ( $YPos < $Bottom_Margin + $line_height + 50 ) {
            $PageNumber ++;
            include('includes/PDFTotalStockHeader.inc');
        }
        $YPos -= $line_height;
        $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 100, $FontSize, $InventoryPlan['StockID'], 'left');
        $LeftOvers = $pdf->addTextWrap(120, $YPos, 200, $FontSize, $InventoryPlan['Description'], 'left');
    }
    $LeftOvers = $pdf->addTextWrap($locOffset, $YPos, 60, $FontSize, number_format(floor($InventoryPlan['Quantity']), 0), 'right');
    $available_reels = GetAvailableReels($InventoryPlan['StockID'], $db, $InventoryPlan['LocCode']);
    $inside_offset = 0;
    foreach ( $available_reels as $id => $qty ) {
        $inside_offset+=$line_height - 2;
        $LeftOvers = $pdf->addTextWrap($locOffset, $YPos - $inside_offset, 60, $FontSize - 1, $id . '(' . number_format($qty, 0) . ')', 'right');
    }
    $running_inside_offset = max($running_inside_offset, $inside_offset);
} /* end STOCK SHEETS while loop */

$YPos -= (2 * $line_height);

$pdfcode = $pdf->output();
$len = strlen($pdfcode);

if ( $len <= 20 ) {
    $title = _('Print Price List Error');
    include('includes/header.inc');
    echo '<p>' . _('There were no stock check sheets to print out for the categories specified');
    echo '<BR><A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
    include('includes/footer.inc');
    exit;
}
else {
    header('Content-type: application/pdf');
    header('Content-Length: ' . $len);
    header('Content-Disposition: inline; filename=StockCheckSheets.pdf');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    $pdf->Stream();
}
?>
