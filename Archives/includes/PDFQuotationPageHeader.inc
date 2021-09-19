<?php

use Rialto\SalesBundle\Entity\Customer;
use Gumstix\GeographyBundle\Model\Country;
require_once 'includes/DateFunctions.inc';

/* $Revision: 1.7 $ */
/* pdf-php by R&OS code to set up a new sales order page */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=18;
//	$pdf->addText($XPos-40, 540,$FontSize+30, _('Quotation'));

$FontSize=14;
$leftStart  =  50;
$midStart   = 300;
$rightStart = 550;

$YPos = 586;
$pdf->addJpegFromFile('logo.jpg', $rightStart-20 , $YPos - 40 );
$pdf->addText($rightStart, $YPos-40,  $FontSize, _('3130 Alpine Road, Suite 288-606'));
$pdf->addText($rightStart, $YPos-55,  $FontSize, _('Portola Valley, CA 94028'));
$pdf->addText($rightStart, $YPos-70,  $FontSize, _('http://www.gumstix.com'));

$YPos -= 10;
$pdf->addText($leftStart, $YPos-10,$FontSize, _('Shipping Address').':');
$pdf->addText($leftStart, $YPos-20,$FontSize-5, $myrow['deliverto']);
$pdf->addText($leftStart, $YPos-30,$FontSize-5, $myrow['DelCompanyName']);
$pdf->addText($leftStart, $YPos-40,$FontSize-5, $myrow['DelAddr1']);
$additionalOffset = 0;
if ($myrow['addr2']) {
	$pdf->addText($leftStart, $YPos-50,$FontSize-5, $myrow['DelAddr2']);
	$additionalOffset = 10;
}
$pdf->addText($leftStart, $YPos-50-$additionalOffset,$FontSize-5, $myrow['DelCity'] . ' ' . $myrow['DelState'] . ' ' . $myrow['DelZip']);
$pdf->addText($leftStart, $YPos-60-$additionalOffset,$FontSize-5, Country::resolveCountryName($myrow['DelCountry']));


$pdf->addText($midStart, $YPos-10,$FontSize, _('Quotation For').':');
$pdf->addText($midStart, $YPos-20,$FontSize-5, $myrow['name']);
$pdf->addText($midStart, $YPos-30,$FontSize-5, $myrow['CompanyName']);
$pdf->addText($midStart, $YPos-40,$FontSize-5, $myrow['addr1']);
$additionalOffset = 0;
if ($myrow['addr2']) {
	$pdf->addText($midStart, $YPos-50,$FontSize-5, $myrow['addr2']);
	$additionalOffset = 10;
	}
$pdf->addText($midStart, $YPos-50-$additionalOffset,$FontSize-5, $myrow['City'] . ' ' . $myrow['State'] . ' ' . $myrow['Zip']);
$pdf->addText($midStart, $YPos-60-$additionalOffset,$FontSize-5, Country::resolveCountryName($myrow['Country']));


$XPos= 100;
$YPos= 500;
$pdf->line($XPos-50, $YPos,   $XPos+650, $YPos   );
$pdf->line($XPos-50, $YPos-20,$XPos+650, $YPos-20);

$ny	= $YPos - 15;
$pdf->addText($leftStart     , $ny, $FontSize-2, _('Quote#'). ':');
$pdf->addText($leftStart+50  , $ny, $FontSize-2, $_GET['QuotationNo']);
$pdf->addText($midStart-100  , $ny, $FontSize-2, _('Customer Order No'). ':');
$pdf->addText($midStart+15   , $ny, $FontSize-2, $myrow['customerref']);
$pdf->addText($midStart+130  , $ny, $FontSize-2, _('VAT/Tax ID'). ':');
$pdf->addText($midStart+205  , $ny, $FontSize-2, $myrow['CustomerTaxID']);
$pdf->addText($rightStart+50 , $ny, $FontSize-2, _('Quote Date'). ':');
$pdf->addText($rightStart+121, $ny, $FontSize-2, ConvertSQLDate($myrow['orddate']));

$YPos -= 50;
$XPos =  40;
$tableTop = $YPos + 15;
$LeftOvers = $pdf->addTextWrap( $Left_Margin +  14,	$YPos,127,$FontSize, _('Stock Code'),'left');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 120,	$YPos,250,$FontSize, _('Product Description'),'left');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 340,	$YPos,85,$FontSize, _('Quantity'),'left');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 408,	$YPos,85,$FontSize,_('List Price Per'));
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 500,	$YPos,85,$FontSize, _('Disc%'));
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 546, $YPos,85,$FontSize, _('Net Price Per'));
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 637,	$YPos,90,$FontSize, _('Product Total') );

/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $tableTop - 10,0,90,10,10);
/*line to the top left */
$pdf->line($Page_Width-$Right_Margin-10, $tableTop,$Left_Margin+10, $tableTop );
/*line under headings to top left */
$pdf->line($Page_Width-$Right_Margin, $tableTop-20,$Left_Margin, $tableTop-20 );
/*Dow top left corner */
$pdf->partEllipse($Left_Margin+10, $tableTop - 10,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($Left_Margin, $tableTop-10,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($Left_Margin+10, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $tableTop-10);

$FontSize =12;

?>
