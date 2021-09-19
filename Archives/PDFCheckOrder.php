<?php
/* $Revision: 1.2 $ */
use Rialto\GeographyBundle\Model\Country;
$PageSecurity = 3;
include('config.php');
$title = _('Stock Location Transfer Selection');
include('includes/ConnectDB.inc');
session_start();
include('includes/PDFStarter_ros.inc');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
require_once('gumstix/tools/I18n.php'); // 00044 - utf8ToAscii()

$InputError=0;
$sql= "SELECT SalesOrders.OrderNo,
        SalesOrders.DebtorNo,
	SalesOrders.DebtorNo,
        SalesOrders.BranchCode,
	SalesOrders.CustomerRef,
	SalesOrders.deliverto,
	SalesOrders.CompanyName DelCompanyName,
	SalesOrders.Addr1 DelAddr1,
	SalesOrders.Addr2 DelAddr2,
	SalesOrders.MailStop DelMailStop,
	SalesOrders.City DelCity,
	SalesOrders.State DelState,
	SalesOrders.Zip DelZip,
	SalesOrders.Country DelCountry,
        SalesOrders.OrdDate,
        SalesOrders.FromStkLoc,
        SalesOrders.PrintedPackingSlip,
        SalesOrders.DatePackingSlipPrinted,
        SalesOrderDetails.StkCode,
        StockMaster.Description,
        StockMaster.Units,
        StockMaster.DecimalPlaces,
        SalesOrderDetails.Quantity,
        SalesOrderDetails.QtyInvoiced,
        SalesOrderDetails.Completed
        FROM SalesOrders
        INNER JOIN SalesOrderDetails ON SalesOrders.OrderNo = SalesOrderDetails.OrderNo
        INNER JOIN StockMaster ON SalesOrderDetails.StkCode = StockMaster.StockID
        WHERE SalesOrders.OrderNo ='" . $_GET['OrderNo']  . "'";
$Result=DB_query($sql,$db,'','',false,false); //dont trap errors here

$CompanyRecord = ReadInCompanyRecord($db);

/*PDFStarter_ros.inc has all the variables for page size and width set up depending on the users default preferences for paper size */

$line_height=12;
$PageNumber = 1;
$TotalDiffs = 0;
$OrderNo = 0;

//	$_GET['OrderNo'];
while ($myrow=DB_fetch_array($Result)) {
    // 00044 - our PDF library can't do UTF-8, so we need to strip out foreign characters.
    foreach ($myrow as &$column) {
        $column = utf8ToAscii($column);
    }
    if ($myrow['OrderNo']!=$OrderNo	) {
        include('includes/PDFCheckOrderHeader.inc');
        $Left_Margin +=10;
        $FontSize -=2;
        /*
        $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,$myrow['OrderNo'], 'left');
        $LeftOvers = $pdf->addTextWrap($Left_Margin+40,$YPos,80,$FontSize,$myrow['DebtorNo'], 'left');
        $LeftOvers = $pdf->addTextWrap($Left_Margin+120,$YPos,80,$FontSize,$myrow['BranchCode'], 'left');

        $LeftOvers = $pdf->addTextWrap($Left_Margin+200,$YPos,100,$FontSize,$myrow['CustomerRef'], 'left');
        $LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos,80,$FontSize,ConvertSQLDate($myrow['OrdDate']), 'left');
        $LeftOvers = $pdf->addTextWrap($Left_Margin+380,$YPos,20,$FontSize,$myrow['FromStkLoc'], 'left');

        if ($myrow['PrintedPackingSlip']==1){
            $PackingSlipPrinted = _('Printed') . ' ' .
            ConvertSQLDate($myrow['DatePackingSlipPrinted']);
        } else {
            $PackingSlipPrinted =_('Not yet printed');
        }

        $LeftOvers = $pdf->addTextWrap($Left_Margin+400,$YPos,100,$FontSize,$PackingSlipPrinted, 'left');
        */

        $YPos -= ($line_height*2);

        /*Its not the first line */
        $OrderNo = $myrow['OrderNo'];
        $LeftOvers = $pdf->addTextWrap($Left_Margin,	 $YPos,100,$FontSize,_('Code'), 'center');
        $LeftOvers = $pdf->addTextWrap($Left_Margin+100,$YPos,100,$FontSize,_('Description'), 'center');
        $LeftOvers = $pdf->addTextWrap($Left_Margin+200,$YPos,150,$FontSize,_('Ordered'), 'center');
        $LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos, 50,$FontSize,_('Invoiced'), 'centre');
        $LeftOvers = $pdf->addTextWrap($Left_Margin+400,$YPos,100,$FontSize,_('Outstanding'), 'center');
        $YPos -= ($line_height);
        $pdf->line($Left_Margin-10, $YPos,$Page_Width-$Right_Margin, $YPos);
        $YPos -= ($line_height);
    }

    $LeftOvers = $pdf->addTextWrap($Left_Margin,    $YPos,100,$FontSize,$myrow['StkCode'], 'left');
    $LeftOvers = $pdf->addTextWrap($Left_Margin+100,$YPos,150,$FontSize,$myrow['Description'], 'left');
    $LeftOvers = $pdf->addTextWrap($Left_Margin+230,$YPos, 50,$FontSize,number_format($myrow['Quantity'],$myrow['DecimalPlaces']), 'right');
    $LeftOvers = $pdf->addTextWrap($Left_Margin+325,$YPos, 50,$FontSize,number_format($myrow['QtyInvoiced'],$myrow['DecimalPlaces']), 'right');

    if ($myrow['Quantity']>$myrow['QtyInvoiced']){
        $LeftOvers = $pdf->addTextWrap($Left_Margin+400,$YPos,100,$FontSize,number_format($myrow['Quantity']-$myrow['QtyInvoiced'],$myrow['DecimalPlaces']), 'right');
    }
    else {
        $LeftOvers = $pdf->addTextWrap($Left_Margin+400,$YPos,100,$FontSize-2,_('(Completed)'), 'center');
    }

    $YPos -= ($line_height);
    if ($YPos - (2 *$line_height) < $Bottom_Margin){
        /*Then set up a new page */
        $PageNumber++;
        include ('includes/PDFCheckOrderHeader.inc');
    } /*end of new page header  */
} /* end of while there are delivery differences to print */

$pdfcode = $pdf->output();
$len = strlen($pdfcode);
header('Content-type: application/pdf');
header('Content-Length: ' . $len);
header('Content-Disposition: inline; filename=OrderStatus.pdf');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

$pdf->stream();

?>
