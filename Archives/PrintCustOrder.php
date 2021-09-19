<?php

use Rialto\StockBundle\Model\Version;
use Rialto\ShippingBundle\Entity\Shipper;


use Rialto\GeographyBundle\Model\Country;
$PageSecurity = 2;

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/DateFunctions.inc');
include('includes/class.pdf.php');
include('includes/CommonGumstix.inc');
include('includes/WO_ui_input.inc');
require_once('gumstix/tools/I18n.php'); // 00044 - utf8ToAscii()




function PrintLinesToBottom ( $TopOfColHeadings, $Left_Margin, $Bottom_Margin, $line_height )
{
    global $pdf;

    $pdf->line($Left_Margin+105, $TopOfColHeadings,$Left_Margin+105,$Bottom_Margin + $line_height * 9);
    $pdf->line($Left_Margin+330, $TopOfColHeadings,$Left_Margin+330,$Bottom_Margin + $line_height * 9);
    $pdf->line($Left_Margin+405, $TopOfColHeadings,$Left_Margin+405,$Bottom_Margin + $line_height * 9);
    $pdf->line($Left_Margin+495, $TopOfColHeadings,$Left_Margin+495,$Bottom_Margin /* + $line_height * 9*/ );
    $pdf->line($Left_Margin+545, $TopOfColHeadings,$Left_Margin+545,$Bottom_Margin + $line_height * 9 );
    $pdf->line($Left_Margin+630, $TopOfColHeadings,$Left_Margin+630,$Bottom_Margin + $line_height * 9);
}

//Get Out if we have no order number to work with
If (!isset($_GET['TransNo']) || $_GET['TransNo']==""){
    if (isset($_POST['TransNo']) && ($_POST['TransNo']!='') ) {
        $ThisTransNo = $_POST['TransNo'];
    } else { $title = _('Select Order To Print');
        include('includes/header.inc');
        echo '<div align=center><br><br><br>';
        prnMsg( _('Select an Order Number to Print before calling this page') , 'error');
        echo '<BR><BR><BR><table class="table_index"><tr><td class="menu_group_item">
            <li><a href="'. $rootpath . '/index.php/record/Sales/SalesOrder/">' . _('Outstanding Sales Orders') . '</a></li>
            <li><a href="'. $rootpath . '/index.php/record/Sales/SalesOrder/?shipped=yes">' . _('Completed Sales Orders') . '</a></li>
            </td></tr></table></DIV><BR><BR><BR>';
        include('includes/footer.inc');
        exit();
    }
} else {
    $ThisTransNo = $_GET['TransNo'];
}

/*retrieve the order details from the database to print */
$ErrMsg = _('There was a problem retrieving the order header details for Order Number') . ' ' . $ThisTransNo . ' ' . _('from the database');
$sql = "SELECT CustomerRef,
        Comments,
        OrdDate,
        DeliverTo,
        SalesOrders.CompanyName,
        SalesOrders.Addr1,
        SalesOrders.Addr2,
        SalesOrders.MailStop,
        SalesOrders.City,
        SalesOrders.State,
        SalesOrders.Zip,
        SalesOrders.Country,
        SalesOrders.DebtorNo,
        SalesOrders.BranchCode,
        SalesOrders.FreightCost,
		SalesOrders.ShipmentType,
        DebtorsMaster.Name,
        DebtorsMaster.Addr1,
        DebtorsMaster.Addr2,
        DebtorsMaster.MailStop,
        DebtorsMaster.City,
        DebtorsMaster.State,
        DebtorsMaster.Zip,
        DebtorsMaster.Country,
        ShipperName,
        PrintedPackingSlip,
        DatePackingSlipPrinted,
        LocationName
    FROM SalesOrders INNER JOIN DebtorsMaster
        ON SalesOrders.DebtorNo=DebtorsMaster.DebtorNo
    INNER JOIN Shippers
        ON SalesOrders.ShipVia=Shippers.Shipper_ID
    INNER JOIN Locations
        ON SalesOrders.FromStkLoc=Locations.LocCode
    WHERE SalesOrders.OrderNo=" . $ThisTransNo;

$result=DB_query($sql,$db, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($result)==0){
    $title = _('Print Packing Slip Error');
        include('includes/header.inc');
        echo '<div align=center><br><br><br>';
    echo $sql;
    prnMsg( _('Unable to Locate Order Number') . ' : ' . $ThisTransNo . ' ', 'error');
        echo '<BR><BR><BR><table class="table_index"><tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/index.php/record/Sales/SalesOrder/">' . _('Outstanding Sales Orders') . '</a></li>
                <li><a href="'. $rootpath . '/index.php/record/Sales/SalesOrder/?shipped=yes">' . _('Completed Sales Orders') . '</a></li>
                </td></tr></table></DIV><BR><BR><BR>';
        include('includes/footer.inc');
        exit();
} elseif (DB_num_rows($result)==1 ) { /*There is only one order header returned - thats good! */

    $myrow = DB_fetch_array($result);
}
/* Then there's an order to print and its not been printed already (or its been flagged for reprinting)
LETS GO */




/* Now ... Has the order got any line items still outstanding to be invoiced */
if (!isset($_POST['TotalPayments'])) {
    $total_payments = 0;
    $payment_list = GetReceipts_AttachedToSalesOrder( $ThisTransNo , $db);
    while ( $this_payment = DB_fetch_array( $payment_list ) ) {
        $total_payments -= $this_payment['OvAmount'];
    }
} else {
    $total_payments = $_POST['TotalPayments'];
}

$ErrMsg = _('There was a problem retrieving the tax details for Order Number') . ' ' . $ThisTransNo . ' ' . _('from the database');
$sqlTaxes =  "SELECT SalesTaxes, Prepayment FROM SalesOrders WHERE OrderNo=" . $ThisTransNo;
$resultTaxes=DB_query($sqlTaxes, $db, $ErrMsgTaxes);
if ($mytaxes=DB_fetch_array($resultTaxes)){
    $taxes        = $mytaxes['SalesTaxes'];
}

if (!isset($_POST['Confirmed'])) {
    include('includes/header.inc');
    echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";
    echo '<br><br>' .
        '<b>Please confirm the total prepayments for Order ' . $ThisTransNo . '</b><br>';
    Input_Text( 'Total Payments','TotalPayments', $total_payments,22,22);
    echo '<br>';
    Input_Submit('Confirmed','Confirmed');
    Input_Hidden('TransNo',$ThisTransNo);
    echo '</form>';
    include('includes/footer.inc');
    exit;
}

$PageNumber = 1;
$ErrMsg = _('There was a problem retrieving the details for Order Number') . ' ' . $ThisTransNo . ' ' . _('from the database');
$sql =     "SELECT StkCode, Description,Quantity,QtyInvoiced,UnitPrice,DiscountPercent,CustomizationID, Version
        FROM SalesOrderDetails LEFT JOIN StockMaster
        ON SalesOrderDetails.StkCode=StockMaster.StockID
        WHERE SalesOrderDetails.OrderNo=" . $ThisTransNo;
$result=DB_query($sql, $db, $ErrMsg);


if (DB_num_rows($result)>0){
/*Yes there are line items to start the ball rolling with a page header */

    /*Set specifically for the stationery being used -needs to be modified for clients own
    packing slip 2 part stationery is recommended so storeman can note differences on and
    a copy retained */

    $Page_Width=792;
    $Page_Height=612;
    $Top_Margin=30;
    $Bottom_Margin=40;
    $Left_Margin=30;
    $Right_Margin=25;


    $PageSize = array(0,0,$Page_Width,$Page_Height);
    $pdf = & new Cpdf($PageSize);
    $FontSize=12;
    $pdf->selectFont(Fonts::find('Helvetica'));
    $pdf->addinfo('Author','webERP ' . $Version);
    $pdf->addinfo('Creator','webERP http://www.weberp.org - R&OS PHP-PDF http://www.ros.co.nz');
    $pdf->addinfo('Title', _('Customer Packing Slip') );
    $pdf->addinfo('Subject', _('Packing slip for order') . ' ' . $ThisTransNo);

    $line_height=16;

    // 00044 - our PDF library can't do UTF-8, so we need to strip out foreign characters.
    foreach ($myrow as &$column) {
        $column = utf8ToAscii($column);
    }
    include('includes/PDFOrderPageHeader.inc');
    $TopOfColHeadings = $YPos + 15;
    PrintLinesToBottom ( $TopOfColHeadings,  $Left_Margin, $Bottom_Margin, $line_height );
    $LeftOvers = $pdf->addTextWrap( $Left_Margin + 14,$YPos,135,$FontSize,    _('StkCode') );
    $LeftOvers = $pdf->addTextWrap( $Left_Margin +130,$YPos,239,$FontSize,    _('Description') );
    $LeftOvers = $pdf->addTextWrap( $Left_Margin +350,$YPos,90,$FontSize,    _('Quantity') );
    $LeftOvers = $pdf->addTextWrap( $Left_Margin +438,$YPos,90,$FontSize,    _('Each') );
    $LeftOvers = $pdf->addTextWrap( $Left_Margin +502,$YPos,90,$FontSize,   _('Disc%') );
    $LeftOvers = $pdf->addTextWrap( $Left_Margin +565,$YPos,90,$FontSize,    _('Discount') );
    $LeftOvers = $pdf->addTextWrap( $Left_Margin +661,$YPos,90,$FontSize,   _('Price') );
    $pdf->line( $Left_Margin, $TopOfColHeadings - 19, $Page_Width -$Right_Margin, $TopOfColHeadings - 19);

    $YPos -= 20;
    $totalPrice = 0;
    while ($myrow2=DB_fetch_array($result)){

        $LeftOvers = $pdf->addTextWrap( $Left_Margin + 14,$YPos,135,$FontSize, $myrow2['StkCode']);
        $LeftOvers = $pdf->addTextWrap( $Left_Margin +130,$YPos,239,$FontSize, $myrow2['Description']);
        $LeftOvers = $pdf->addTextWrap( $Left_Margin +300,$YPos,90,$FontSize,  number_format($Q=$myrow2['Quantity'], 0) , 'right' );
        $LeftOvers = $pdf->addTextWrap( $Left_Margin +380,$YPos,90,$FontSize,  number_format($P=$myrow2['UnitPrice'], 2) , 'right'  );
        $LeftOvers = $pdf->addTextWrap( $Left_Margin +440,$YPos,90,$FontSize,  number_format(100 * $DP=$myrow2['DiscountPercent'], 1) , 'right'  );
        $LeftOvers = $pdf->addTextWrap( $Left_Margin +530,$YPos,90,$FontSize,  number_format($DP * $P, 2 ) , 'right'  );
        $LeftOvers = $pdf->addTextWrap( $Left_Margin +630,$YPos,90,$FontSize,  number_format($T = ((1 - $DP) * $P * $Q), 2) , 'right'  );
        $totalPrice += ($T) ;
        if ( $myrow2['Version'] != Version::ANY )
        {
            $YPos -= $line_height;
            $pdf->addTextWrap( $Left_Margin + 25, $YPos, 239, $FontSize,
                '-R' . $myrow2['Version']
            );
        }

        if ($myrow2['CustomizationID']!=0) {
            $YPos -= ($line_height);
            $pdf->addTextWrap( $Left_Margin + 25, $YPos, 239, $FontSize,
                '-C' . $myrow2['CustomizationID']
            );
            $pdf->addTextWrap( $Left_Margin +130,$YPos,239,$FontSize,
                'Customization: ' . TypeNoName($myrow2['CustomizationID'],$db,300)
            );
        }
        if ($YPos-$line_height <= 136){            /* We reached the end of the page so finish off the page and start a new */
            PrintLinesToBottom ( $TopOfColHeadings,  $Left_Margin, $Bottom_Margin, $line_height );
            $PageNumber++;
                  include ('includes/PDFOrderPageHeader.inc');

       } //end if need a new page headed up

       /*increment a line down for the next line item */
       $YPos -= ($line_height);

      } //end while there are line items to print out

} /*end if there are order details to show on the order*/

$YPos = $Bottom_Margin + $line_height * 9;

$LeftOvers = $pdf->addTextWrap( $Page_Width - 120, $YPos - $line_height * 1,  90, $FontSize,  '$'.number_format($totalPrice, 2) ,'right' );
$LeftOvers = $pdf->addTextWrap( $Left_Margin +500, $YPos - $line_height * 1, 239, $FontSize,  _('Subtotal') );

$shipper = Shipper::fetchByName($myrow['ShipperName']);
$shipping_text = $shipper->getName();
if ( $myrow['ShipmentType'] ) {
    $method = $shipper->getShippingMethod($myrow['ShipmentType']);
    $shipping_text = stripUtf8($method->getName());
}

$LeftOvers = $pdf->addTextWrap( $Page_Width - 120, $YPos - $line_height * 2,  90, $FontSize,  '$'.number_format($fc = $myrow['FreightCost'], 2),'right' );
$LeftOvers = $pdf->addTextWrap( $Left_Margin +500, $YPos - $line_height * 2, 239, $FontSize,  _($shipping_text) );

if ($taxes != 0) {
    $LeftOvers = $pdf->addTextWrap( $Page_Width - 120, $YPos - $line_height * 3,  90, $FontSize,  '$'.number_format($taxes, 2) ,'right' );
    $LeftOvers = $pdf->addTextWrap( $Left_Margin +500, $YPos - $line_height * 3, 239, $FontSize,  _('CA Sales Tax') );
}

$LeftOvers = $pdf->addTextWrap( $Page_Width - 120, $YPos - $line_height * 4,  90, $FontSize,  '$'.number_format($totalPrice + $fc + $taxes, 2) ,'right' );
$LeftOvers = $pdf->addTextWrap( $Left_Margin +500, $YPos - $line_height * 4, 239, $FontSize,  _('Total') );

if ($total_payments!=0) {
    $LeftOvers = $pdf->addTextWrap( $Page_Width - 120, $YPos - $line_height * 6,  90, $FontSize,  '$'.number_format($total_payments, 2) ,'right' );
    $LeftOvers = $pdf->addTextWrap( $Left_Margin +500, $YPos - $line_height * 6, 239, $FontSize,  _('Total prepayment') );
}

if ($total_payments==0) {
    $required_prepayment = ( $totalPrice + $fc + $taxes ) * 0.35;
        $LeftOvers = $pdf->addTextWrap( $Page_Width - 120, $YPos - $line_height * 6,  90, $FontSize,  '$'.number_format($required_prepayment, 2) ,'right' );
        $LeftOvers = $pdf->addTextWrap( $Left_Margin +500, $YPos - $line_height * 6, 239, $FontSize,  _('Commitment payment') );
} else {
    $amount_due = max($totalPrice + $fc + $taxes - $total_payments, 0);
    $LeftOvers = $pdf->addTextWrap( $Page_Width - 120, $YPos - $line_height * 8,  90, $FontSize,  '$'.number_format($amount_due, 2) ,'right' );
    $LeftOvers = $pdf->addTextWrap( $Left_Margin +500, $YPos - $line_height * 8, 239, $FontSize,  _('Remit prior to delivery') );
}
$pdf->line( $Left_Margin, $YPos, $Page_Width -$Right_Margin, $YPos);

$pdfcode = $pdf->output();
$len = strlen($pdfcode);

if ($len<=20){
    $title = _('Print Packing Slip Error');
    include('includes/header.inc');
    echo '<p>'. _('There were no oustanding items on the order to deliver. A dispatch note cannot be printed').
        '<BR><A HREF="' . $rootpath . '/index.php/record/Sales/SalesOrder/">'. _('Print Another Packing Slip/Order').
        '</A>' . '<BR>'. '<A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
    include('includes/footer.inc');
    exit;
} else {
    header('Content-type: application/pdf');
    header('Content-Length: ' . $len);
    header('Content-Disposition: inline; filename=PackingSlip.pdf');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    $pdf->Stream();

    $sql = "UPDATE SalesOrders SET PrintedPackingSlip=1, DatePackingSlipPrinted='" . Date($DefaultDateFormat) . "' WHERE SalesOrders.OrderNo=" .$ThisTransNo;
    $result = DB_query($sql,$db);
}

?>
