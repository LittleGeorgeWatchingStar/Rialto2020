<?php

use Rialto\StockBundle\Model\Version;
use Rialto\SalesBundle\Entity\SalesOrder;
use Rialto\ShippingBundle\Entity\Shipper;

use Rialto\GeographyBundle\Model\Country;
$PageSecurity = 2;

require_once 'gumstix/tools/I18n.php'; // 00044 - utf8ToAscii()

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CommonGumstix.inc');

function error($msg)
{
    $title = _('Select Quotation To Print');
    include('includes/header.inc');
    echo '<div align=center><br><br><br>';
    prnMsg( $msg, 'error');
    echo '<BR><BR><BR><table class="table_index"><tr><td class="menu_group_item">';
    $uri = new ErpUri('/index.php/record/Sales/SalesOrder/', array(
        'salesStage' => SalesOrder::QUOTATION
    ));
    echo '<li><a href="'. $uri->render() .'">' . _('Quotations') . '</a></li>
        </td></tr></table></DIV><BR><BR><BR>';
    include('includes/footer.inc');
    exit();
}


function PrintLinesToBottom ( $TopOfColHeadings, $Left_Margin, $Bottom_Margin, $line_height )
{
    global $pdf;
    $pdf->line($Left_Margin+105, $TopOfColHeadings,$Left_Margin+105,$Bottom_Margin + $line_height * 9 );
    $pdf->line($Left_Margin+330, $TopOfColHeadings,$Left_Margin+330,$Bottom_Margin + $line_height * 9 );
    $pdf->line($Left_Margin+405, $TopOfColHeadings,$Left_Margin+405,$Bottom_Margin + $line_height * 9 );
    $pdf->line($Left_Margin+495, $TopOfColHeadings,$Left_Margin+495,$Bottom_Margin + $line_height * 0 );
    $pdf->line($Left_Margin+545, $TopOfColHeadings,$Left_Margin+545,$Bottom_Margin + $line_height * 9 );
    $pdf->line($Left_Margin+630, $TopOfColHeadings,$Left_Margin+630,$Bottom_Margin + $line_height * 9 );
}


//Get Out if we have no order number to work with
If (!isset($_GET['QuotationNo']) || $_GET['QuotationNo']==""){
    error(_('Select a Quotation to Print before calling this page'));
}


/*retrieve the order details from the database to print */
$ErrMsg = _('There was a problem retrieving the quotation header details for Order Number') . ' ' . $_GET['QuotationNo'] . ' ' . _('from the database');

$sql = "SELECT  SalesOrders.customerref,
		SalesOrders.comments,
		SalesOrders.orddate,
		SalesOrders.deliverto,
		SalesOrders.CompanyName DelCompanyName,
		SalesOrders.Addr1 DelAddr1,
		SalesOrders.Addr2 DelAddr2,
		SalesOrders.MailStop DelMailStop,
		SalesOrders.City DelCity,
		SalesOrders.State DelState,
		SalesOrders.Zip DelZip,
		SalesOrders.Country DelCountry,
		SalesOrders.freightcost,
		SalesOrders.SalesTaxes,
		SalesOrders.Prepayment,
		SalesOrders.ShipmentType,
		SalesOrders.CustomerTaxID,
		DebtorsMaster.name,
		DebtorsMaster.CompanyName,
		DebtorsMaster.addr1,
		DebtorsMaster.addr2,
		DebtorsMaster.MailStop,
		DebtorsMaster.City,
		DebtorsMaster.State,
		DebtorsMaster.Zip,
		DebtorsMaster.Country,
		Shippers.Shippername,
		SalesOrders.printedpackingslip,
		SalesOrders.datepackingslipprinted,
		Locations.locationname
	FROM	SalesOrders,
		DebtorsMaster,
		Shippers,
		Locations
	WHERE SalesOrders.debtorno=DebtorsMaster.debtorno
	AND SalesOrders.shipvia=Shippers.shipper_id
	AND SalesOrders.fromstkloc=Locations.loccode
	AND SalesOrders.SalesStage != '" . SalesOrder::ORDER . "'
	AND SalesOrders.orderno=" . $_GET['QuotationNo'];

$result=DB_query($sql,$db, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($result)==0){
    error(_('Unable to Locate Quotation Number') . ' : ' . $_GET['QuotationNo']);
}
elseif (DB_num_rows($result)==1) {
    /*There is only one order header returned - thats good! */
    $myrow = DB_fetch_array($result);
}
// 00044 - our PDF library can't do UTF-8, so we need to strip out foreign characters.
foreach ($myrow as &$column) {
    $column = utf8ToAscii($column);
}

/*retrieve the order details from the database to print */

/* Then there's an order to print and its not been printed already (or its been flagged for reprinting/ge_Width=807;
 )
 LETS GO */
$PaperSize = 'letter_landscape';
include('includes/PDFStarter_ros.inc');

$FontSize=12;
$pdf->selectFont(Fonts::find('Helvetica'));
$pdf->addinfo('Title', _('Customer Quotation') );
$pdf->addinfo('Subject', _('Quotation') . ' ' . $_GET['QuotationNo']);


$line_height=16;

/* Now ... Has the order got any line items still outstanding to be invoiced */

$PageNumber = 1;

$ErrMsg = _('There was a problem retrieving the quotation line details for quotation Number') . ' ' .
$_GET['QuotationNo'] . ' ' . _('from the database');

$sql = "SELECT  SalesOrderDetails.stkcode,
		StockMaster.description,
		SalesOrderDetails.quantity,
		SalesOrderDetails.qtyinvoiced,
		SalesOrderDetails.unitprice,
		SalesOrderDetails.discountpercent,
		SalesOrderDetails.narrative,
		SalesOrderDetails.CustomizationID,
        SalesOrderDetails.Version
	FROM SalesOrderDetails INNER JOIN StockMaster
		ON SalesOrderDetails.stkcode=StockMaster.stockid
	WHERE SalesOrderDetails.orderno=" . $_GET['QuotationNo'];
$result=DB_query($sql,$db, $ErrMsg);

/* Define format strings for money_format() */
$gMoneyFormatSign = '%(10#7.2n';
$gMoneyFormatNoSign = '%(!10#7.2n';

if (DB_num_rows($result)>0){
    /*Yes there are line items to start the ball rolling with a page header */
    include('includes/PDFQuotationPageHeader.inc');
    $TopOfColHeadings = $YPos +15;
    $QuotationTotal =0;
    $YPos -= ($line_height + 5);

    /* Only show currency symbols on the first line, in accordance
     * with accounting standards. */
    $moneyFormat = $gMoneyFormatSign;

    while ($myrow2=DB_fetch_array($result))
    {
        if ((strlen($myrow2['narrative']) >200 AND $YPos-$line_height <= 75)
        OR (strlen($myrow2['narrative']) >1 AND $YPos-$line_height <= 62)
        OR $YPos-$line_height <= 50){
            /* We reached the end of the page so finsih off the page and start a newy */
            $PageNumber++;
            include ('includes/PDFQuotationPageHeader.inc');
            $moneyFormat = $gMoneyFormatSign;
        } //end if need a new page headed up

        $LeftOvers = $pdf->addTextWrap( $Left_Margin + 14,$YPos,135,$FontSize, $myrow2['stkcode']);
        $LeftOvers = $pdf->addTextWrap( $Left_Margin +120,$YPos,239,$FontSize, $myrow2['description']);
        $LeftOvers = $pdf->addTextWrap( $Left_Margin +300,$YPos,90,$FontSize,  number_format($Q=$myrow2['quantity'], 0) , 'right' );
//        $LeftOvers = $pdf->addTextWrap( $Left_Margin +380,$YPos,90,$FontSize,  number_format($P=$myrow2['unitprice'], 2) , 'right'  );
        $LeftOvers = $pdf->addTextWrap( $Left_Margin +390,$YPos,90,$FontSize,  money_format($moneyFormat, $P=$myrow2['unitprice']), 'right');
        $LeftOvers = $pdf->addTextWrap( $Left_Margin +440,$YPos,90,$FontSize,  number_format(100 * $DP=$myrow2['discountpercent'], 1) , 'right'  );
//        $LeftOvers = $pdf->addTextWrap( $Left_Margin +530,$YPos,90,$FontSize,  number_format($P - ($DP * $P), 2 ) , 'right'  );
        $LeftOvers = $pdf->addTextWrap( $Left_Margin +530,$YPos,90,$FontSize,  money_format($moneyFormat, $P - ($DP * $P)), 'right');
//        $LeftOvers = $pdf->addTextWrap( $Left_Margin +630,$YPos,90,$FontSize,  number_format($T = ((1 - $DP) * $P * $Q), 2) , 'right'  );
        $LeftOvers = $pdf->addTextWrap( $Left_Margin +630,$YPos,90,$FontSize,  money_format($moneyFormat, $T = ((1 - $DP) * $P * $Q)), 'right');
        $QuotationTotal += ($T) ;

        if (strlen($myrow2['narrative'])>4){
            $YPos -= 10;
            $LeftOvers = $pdf->addTextWrap($XPos+1,$YPos,750,10,$myrow2['narrative']);
            if (strlen($LeftOvers>1)){
                $YPos -= 10;
                $LeftOvers = $pdf->addTextWrap($XPos+1,$YPos,750,10,$LeftOvers);
            }
        }

        if ( $myrow2['Version'] != Version::ANY )
        {
            $YPos -= $line_height;
            $indent = 15;
            $pdf->addTextWrap( $Left_Margin + 14 + $indent, $YPos, 239, $FontSize,
                '-R' . $myrow2['Version']
            );
        }

        if ($myrow2['CustomizationID']!=0) {
            $YPos -= ($line_height);
            /* indent customization info so it does not look like another
             * line item. */
            $indent = 15;
            $LeftOvers = $pdf->addTextWrap(
                $Left_Margin + 14 + $indent,
                $YPos,
                135,
                $FontSize,
                sprintf('-C%s', $myrow2['CustomizationID'])
            );
            $LeftOvers = $pdf->addTextWrap(
                $Left_Margin + 120 + $indent,
                $YPos,
                239,
                $FontSize,
                '- ' . TypeNoName($myrow2['CustomizationID'],$db,300)
            );
        }
        /*increment a line down for the next line item */
        $YPos -= ($line_height);

        /* Only show currency symbols on the first line, in accordance
         * with accounting standards. */
        $moneyFormat = $gMoneyFormatNoSign;

    } //end while there are line items to print out
    $YPos -= 4*($line_height);
    if ((strlen($myrow['comments']) >200 AND $YPos-$line_height <= 75)
    OR (strlen($myrow['comments']) >1 AND $YPos-$line_height <= 62)
    OR $YPos-$line_height <= 50){
        /* We reached the end of the page so finish off the page and start a newy */
        $PageNumber++;
        include ('includes/PDFQuotationPageHeader.inc');

    } //end if need a new page headed up
} /*end if there are line details to show on the quotation*/

$line_height = 12;
$YPos = $Bottom_Margin + $line_height * 9;

/* subtotal */
$LeftOvers = $pdf->addTextWrap(
    $Page_Width - 120,
    $YPos - $line_height * 1,
    90,
    $FontSize,
    money_format($gMoneyFormatSign, $QuotationTotal),
    'right'
);
$LeftOvers = $pdf->addTextWrap(
    $Left_Margin +500,
    $YPos - $line_height * 1,
    239,
    $FontSize,
    _('Subtotal')
);

/* shipping */
$shipper = Shipper::fetchByName($myrow['Shippername']);
$shipping_text = $shipper->getName();
if ( $myrow['ShipmentType'] ) {
    $method = $shipper->getShippingMethod($myrow['ShipmentType']);
    $shipping_text = stripUtf8($method->getName());
}
$LeftOvers = $pdf->addTextWrap(
    $Page_Width - 120,
    $YPos - $line_height * 2,
    90,
    $FontSize,
    money_format($gMoneyFormatNoSign, $fc = $myrow['freightcost']),
    'right'
);
$LeftOvers = $pdf->addTextWrap(
    $Left_Margin +500,
    $YPos - $line_height * 2,
    239,
    $FontSize,
    $shipping_text
);

/* taxes */
$LeftOvers = $pdf->addTextWrap(
    $Page_Width - 120,
    $YPos - $line_height * 3,
    90,
    $FontSize,
    money_format($gMoneyFormatNoSign, $taxes = $myrow['SalesTaxes']),
    'right'
);
$LeftOvers = $pdf->addTextWrap(
    $Left_Margin + 500,
    $YPos - $line_height * 3,
    239,
    $FontSize,
    _('CA state tax')
);

/* Total */
$LeftOvers = $pdf->addTextWrap(
    $Page_Width - 120,
    $YPos - $line_height * 4,
    90,
    $FontSize,
    money_format($gMoneyFormatSign, $QuotationTotal + $fc + $taxes),
    'right'
);
$LeftOvers = $pdf->addTextWrap(
    $Left_Margin +500,
    $YPos - $line_height * 4,
    239,
    $FontSize,
    _('Total')
);

/* Confirmation payment */
$LeftOvers = $pdf->addTextWrap(
    $Page_Width - 120,
    $YPos - $line_height * 7,
    90,
    $FontSize,
    money_format($gMoneyFormatSign, $prepayment = $myrow['Prepayment']),
    'right'
);
$LeftOvers = $pdf->addTextWrap(
    $Left_Margin +500,
    $YPos - $line_height * 7,
    239,
    $FontSize,
    _('Confirmation payment')
);

/* Delivery payment */
$LeftOvers = $pdf->addTextWrap(
    $Page_Width - 120,
    $YPos - $line_height * 8,
    90,
    $FontSize,
    money_format($gMoneyFormatSign, $QuotationTotal+ $fc + $taxes - $prepayment),
    'right'
 );
$LeftOvers = $pdf->addTextWrap(
    $Left_Margin +500,
    $YPos - $line_height * 8,
    239,
    $FontSize,
    _('Delivery payment')
);


$YPos -= ($line_height);
$LeftOvers = $pdf->addTextWrap($XPos + 30 ,$YPos,300,10,$myrow['comments']);
while (strlen($LeftOvers)>1){
    PrintLinesToBottom ( $TopOfColHeadings,  $Left_Margin, $Bottom_Margin, $line_height );
    $YPos -= 10;
    $LeftOvers = $pdf->addTextWrap($XPos +30 ,$YPos, 300,10,$LeftOvers);
}
$pdf->line( $Left_Margin, $Bottom_Margin + $line_height * 9, $Page_Width -$Right_Margin,  $Bottom_Margin + $line_height * 9 );
PrintLinesToBottom ( $TopOfColHeadings,  $Left_Margin, $Bottom_Margin, $line_height );

$pdfcode = $pdf->output();
$len = strlen($pdfcode);
if ($len<=20){
    $title = _('Print Quotation Error');
    include('includes/header.inc');
    echo '<p>'. _('There were no items on the quotation') . '. ' . _('The quotation cannot be printed').
                '<BR><A HREF="' . $rootpath . '//index.php/record/Sales/SalesOrder/?salesStage=quotation">'. _('Print Another Quotation').
                '</A>' . '<BR>'. '<A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
    include('includes/footer.inc');
    exit;
} else {
    header('Content-type: application/pdf');
    header('Content-Length: ' . $len);
    header('Content-Disposition: inline; filename=Quotation.pdf');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    //echo 'here';
    $pdf->Stream();

}

?>
