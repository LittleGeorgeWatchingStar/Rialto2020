<?php
/* $Revision: 1.3 $ */

$PageSecurity =1;

require 'includes/session.inc';
$title = _('Stock Location Transfer Selection');

include('includes/PDFStarter_ros.inc');
include('includes/DateFunctions.inc');



if (!isset($_GET['TransferNo'])){

    include ('includes/header.inc');
    echo '<P>';
    $ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' .
       '<P>'. _('This page must be called with a location transfer reference number').'.';
    $DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
    $sql = "SELECT DISTINCT LocTransfers.Reference,
               LocTransfers.ShipDate,
               Locations.LocationName AS ShipLocName,
               LocationsRec.LocationName AS RecLocName
               FROM LocTransfers
               LEFT JOIN Locations ON LocTransfers.ShipLoc=Locations.LocCode
               LEFT JOIN Locations AS LocationsRec ON LocTransfers.RecLoc = LocationsRec.LocCode
               WHERE LocTransfers.RecQty != LocTransfers.ShipQty";

    $result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
    echo "<CENTER><TABLE CELLPADDING=1 COLSPAN=7 BORDER=1 WIDTH=40% >";
    echo "<TR bgcolor='#CCCCCC'><td colspan=6><center>These are all uncompleted stock transfers.</td></tr>";
    echo "<TR><TH>Date</TH><TH>From</TH><TH>To</TH> <TH>PDF</TH><TH>Edit</TH></TR>";
    while ( $transferNo = DB_fetch_array($result)) {
        echo "<TR>";
        echo    "<TD>".  $transferNo['ShipDate']." </TD><TD> ".
        $transferNo['ShipLocName'] ." </TD><TD>  ".  $transferNo['RecLocName'] . " </TD>";
        echo "<TD><CENTER><A target='_blank' HREF=PDFStockLocTransfer.php?TransferNo=". $transferNo['Reference'] ."> " . $transferNo['Reference'] ."</TD>";

        //    START ADDING HERE
        echo '<TD><CENTER>';
        echo "<form target='_blank' method='post' action='EditStockLocTransfer.php'>";
        echo '<INPUT TYPE=SUBMIT NAME="Edit" VALUE="' . $transferNo['Reference'] . '">';
        echo '<INPUT TYPE=HIDDEN NAME=TransferID VALUE=' .$transferNo['Reference'] . '>';
        echo '</form>';
        echo "</td>";
        //    STOP ADDING HERE

        echo "</tr>";
    }
    echo "</TABLE>";
    include ('includes/footer.inc');
    exit;
}

$FontSize=10;
$pdf->addinfo('Title', _('Inventory Location Transfer BOL') );
$pdf->addinfo('Subject', _('Inventory Location Transfer BOL') . ' # ' . $_GET['Trf_ID']);

$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<P>'. _('This page must be called with a location transfer reference number').'.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
$sql = "SELECT  LocTransfers.Reference,
                LocTransfers.StockID,
                StockMaster.Description,
                LocTransfers.ShipQty,
                LocTransfers.ShipDate,
                LocTransfers.ShipLoc,
                LocTransfers.SerialNo,
                Locations.LocationName AS ShipLocName,
                LocTransfers.RecLoc,
                LocationsRec.LocationName AS RecLocName,
                StockSerialItems.Version
        FROM LocTransfers
        LEFT JOIN StockMaster ON LocTransfers.StockID=StockMaster.StockID
        LEFT JOIN Locations ON LocTransfers.ShipLoc=Locations.LocCode
        LEFT JOIN Locations AS LocationsRec ON LocTransfers.RecLoc = LocationsRec.LocCode
        LEFT JOIN StockSerialItems ON StockSerialItems.LocCode=LocationsRec.LocCode AND StockSerialItems.SerialNo=LocTransfers.SerialNo AND StockSerialItems.StockID=LocTransfers.StockID
        WHERE LocTransfers.Reference=" . $_GET['TransferNo'];

$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

If (DB_num_rows($result)==0){

    include ('includes/header.inc');
    prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'),'error');
    include ('includes/footer.inc');
    exit;
}

$TransferRow = DB_fetch_array($result);

$PageNumber=1;
include ('includes/PDFStockLocTransferHeader.inc');
$line_height=30;
$FontSize=10;
$reel_count = 0;
$nonreel_count = 0;

do {

    if ( is_numeric($TransferRow['Version'])) {
        $version_str = '-R' . $TransferRow['Version'];
    } else {
        $version_str = $TransferRow['Version'];
    }

    $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,140,$FontSize,$TransferRow['StockID'] . $version_str , 'left');
    $LeftOvers = $pdf->addTextWrap(100,$YPos,200,$FontSize,$TransferRow['Description'], 'left');
    $LeftOvers = $pdf->addTextWrap(300,$YPos,50,$FontSize,number_format($TransferRow['ShipQty']), 'right');
    $LeftOvers = $pdf->addTextWrap(380,$YPos,50,$FontSize,$TransferRow['SerialNo'], 'right');
    $pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);
    if ( $TransferRow['ShipQty'] != 0) {
        if ( $TransferRow['SerialNo'] == '') {
            $nonreel_count+=1;
        } else {
            $reel_count +=1;
        }
    }

    $YPos -= $line_height;

    if ($YPos < $Bottom_Margin + $line_height) {
        $PageNumber++;
        include('includes/PDFStockLocTransferHeader.inc');
    }

} while ($TransferRow = DB_fetch_array($result));
$line_height=17;
$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos-= $line_height, 300, $FontSize+5 ,'Verify item count', 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin + 400, $YPos, 300, $FontSize+5 ,'Initial & Date', 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos-= $line_height, 300, $FontSize+3 ,'Count of reels and trays with id numbers: ', 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 300, $FontSize+3 , number_format( $reel_count, 0) , 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos-= $line_height, 300, $FontSize+3 ,'Count of stock items without id numbers: ', 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 300, $FontSize+3 , number_format( $nonreel_count, 0), 'right');

$LeftOvers = $pdf->addTextWrap($Left_Margin +300, $YPos + $line_height *  0.5, 100, $FontSize+3 ,'Delivered', 'right' );
$LeftOvers = $pdf->addTextWrap($Left_Margin +300, $YPos + $line_height * -1.0  , 100, $FontSize+3 ,'Returned', 'right' );

$pdf->line($Left_Margin      , $YPos -2 + $line_height *  2.0, $Left_Margin+$Page_Width , $YPos -2  + $line_height *  2.0   );
$pdf->line($Left_Margin + 402, $YPos -2 + $line_height *  0.5, $Page_Width-$Right_Margin, $YPos -2  + $line_height *  0.5 );
$pdf->line($Left_Margin + 402, $YPos -2 + $line_height * -1.0, $Page_Width-$Right_Margin, $YPos -2  + $line_height * -1.0   );

$pdfcode = $pdf->output();
$len = strlen($pdfcode);


if ($len<=20){
    include('includes/header.inc');
    echo '<p>';
    prnMsg( _('There was no stock location transfer to print out'), 'warn');
    echo '<BR><A HREF="' . $rootpath. '/index.php?' . SID . '">'. _('Back to the menu'). '</A>';
    include('includes/footer.inc');
    exit;
} else {
    header('Content-type: application/pdf');
    header('Content-Length: ' . $len);
    header('Content-Disposition: inline; filename=StockLocTrfShipment.pdf');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    $pdf->Stream();
}
?>
