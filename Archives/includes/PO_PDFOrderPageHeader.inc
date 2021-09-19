<?php
/* $Revision: 1.4 $ */
/*
use Rialto\PurchasingBundle\Entity\Supplier;
	R & OS PHP-PDF class code to set up a new page
	a new page is implicit on the establishment of a new pdf object so
	only for subsequent pages
*/

if ($PageNumber>1){
	$pdf->newPage();
}

$pdf->addJpegFromFile('logo.jpg',$Left_Margin, $Page_Height - $Top_Margin - 60, 200);

$FontSize=12;
$XPos = $Left_Margin+ 12;
$YPos = $Page_Height - $Top_Margin - 75; 
$pdf->addText($XPos,$YPos+$line_height,$FontSize, _('purchase order number'). ' ' . $OrderNo);


$FontSize =10;
$YPos    += 60;
$XPos     = $Page_Width-$Right_Margin-50;
$pdf->addText($XPos,$YPos, $FontSize, _('Page') . ': ' .$PageNumber);

/*Now the delivery details */
$XPos = $Page_Width/2 - 50;
$xtr = 0;
$line_height=10;
if ($POHeader['LocMailStop'] != '') {
	$pdf->addText($XPos+10,$YPos-(3*$line_height), $FontSize, $POHeader['LocMailStop'] );
	$xtr +=10;
}

$pdf->addText($XPos+05,$YPos, $FontSize, _('Shipping address') . ':' );
$pdf->addText($XPos+10,$YPos-     (1*$line_height), $FontSize, $POHeader['Contact']);	//	"W. Gordon Kruberg, M.D.");
$pdf->addText($XPos+10,$YPos-     (2*$line_height), $FontSize, $POHeader['LocationName'] );	//	"Gumstix, Inc.");
$pdf->addText($XPos+10,$YPos-$xtr-(3*$line_height), $FontSize, $POHeader['Addr1'] . (  ($POHeader['Addr2'] != '') ? (', ' . $POHeader['Addr2']) : ''      )  );
//if ($POHeader['Addr2'] != '') {
//        $pdf->addText($XPos+10,$YPos-$xtr-(4*$line_height), $FontSize, $POHeader['Addr2'] );
//        $xtr +=10;
//}
$pdf->addText($XPos+10,$YPos-$xtr-(4*$line_height), $FontSize, $POHeader['City'].', '.$POHeader['State'].' '.$POHeader['Zip']);
$pdf->addText($XPos+10,$YPos-$xtr-(5*$line_height), $FontSize, $POHeader['LocTelephone'] );

$pdf->addText($XPos+185,$YPos, $FontSize, _('Billing address') . ':' );
$pdf->addText($XPos+190,$YPos-(1*$line_height), $FontSize,      "W. Gordon Kruberg, M.D.");
$pdf->addText($XPos+190,$YPos-(2*$line_height), $FontSize,      "Gumstix, Inc.");
$pdf->addText($XPos+190,$YPos-(3*$line_height), $FontSize, 	"3130 Alpine Road, Suite 288-606" );
$pdf->addText($XPos+190,$YPos-(4*$line_height), $FontSize,	"Portola Valley, CA 94028  USA" );

$line_height=12;

/*draw a nice curved corner box around the delivery to address */
/*from the top right */
$pdf->partEllipse($XPos+350-10,$YPos+10,0,90,10,10);
/*line to the top left */
$pdf->line($XPos+350-10, $YPos+20,$XPos+10, $YPos+20);
/*Do top left corner */
$pdf->partEllipse($XPos+10, $YPos+10,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($XPos+10, $YPos-60-10,$XPos+350-10, $YPos-60-10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos+10, $YPos-60,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-60,$XPos, $YPos+10);
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+350-10, $YPos-60,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($XPos+350, $YPos-60,$XPos+350, $YPos+10);
/*end of the small table showing the company name and delivery address details */

$XPos = $Left_Margin;
$YPos -= ($line_height*4.5);         /* 4 lines down */

$YPos -= 20;
$XPos += 5;
$pdf->addText($XPos+5,	$YPos-( 0*$line_height), $FontSize+3, $POHeader['SuppName']);
$pdf->addText($XPos+5,	$YPos-( 1*$line_height), $FontSize+3, $POHeader['OrderAddr1']);
$pdf->addText($XPos+5,  $YPos-( 2*$line_height), $FontSize+3, $POHeader['OrderAddr2']);
$pdf->addText($XPos+5,	$YPos-( (  ($POHeader['OrderAddr2']=='')? 2:3) *$line_height), $FontSize+3, $POHeader['OrderCity'].", ".$POHeader['OrderState']." ".$POHeader['OrderZip']);


$XPos = $Page_Width/2 + 25;
$pdf->addText($XPos,	$YPos-(1*$line_height), $FontSize, _('Order Date') . ':' );
$pdf->addText($XPos+70,	$YPos-(1*$line_height), $FontSize, ConvertSQLDate($POHeader['OrdDate']));

$pdf->addText($XPos,	$YPos-(2*$line_height), $FontSize, _('Initiator').': ');
$pdf->addText($XPos+70,	$YPos-(2*$line_height), $FontSize, $POHeader['Initiator']." ".$POHeader['RequisitionNo'] );

$pdf->addText($XPos,	$YPos-(3*$line_height), $FontSize, _('Comments') . ':' );
$LeftOvers = $pdf->addTextWrap($XPos + 70,		$YPos-(3*$line_height),275,$FontSize,$POHeader['Comments'], 'left');
$commentRow = 4;
while (strlen($LeftOvers)>1){
	$LeftOvers = $pdf->addTextWrap($XPos-25, $YPos-( $commentRow*$line_height),300,$FontSize,$LeftOvers, 'left');
	$commentRow ++;
}

$YPos -= ($line_height*2.5);
$XPos = $Left_Margin;
$pdf->addText($Left_Margin,$YPos-(5*$line_height), $FontSize, _('All amounts stated in').' - ' . $POHeader['CurrCode']);
if ($ViewingOnly!=0) {
	$pdf->addText($XPos+ 200,$YPos-(5*$line_height),$FontSize + 3, _('FOR VIEWING & TESTING ONLY')."--". _(' THIS IN NOT AN ORDER')  );
}


$YPos -= ($line_height*5.5);
/*draw a square grid for entering line items */
$pdf->line($Left_Margin, $YPos, $Page_Width-$Right_Margin, $YPos);
$pdf->line($Page_Width-$Right_Margin, $YPos, $Page_Width-$Right_Margin, $Bottom_Margin);
$pdf->line($Left_Margin, $Bottom_Margin, $Page_Width-$Right_Margin, $Bottom_Margin);
$pdf->line($Left_Margin, $Bottom_Margin, $Left_Margin, $YPos);

$YPos -= $line_height* .75;
/*Set up headings */
$FontSize=10;
$pdf->addText($Left_Margin+4,$YPos, $FontSize, _('Code') );
$pdf->addText($Left_Margin+65,    $YPos, $FontSize, _('Item Description') );
$pdf->addText($Left_Margin+65+150,$YPos, $FontSize, _('Manufacturer\'s Code') );
$pdf->addText($Left_Margin+65+245,$YPos, $FontSize, _('Supplier\'s Description') );
$pdf->addText($Left_Margin+450,$YPos, $FontSize, _('Qty') );
$pdf->addText($Left_Margin+473,$YPos, $FontSize, _('units') );
$pdf->addText($Left_Margin+505,$YPos, $FontSize, _('Date Reqd'));
$pdf->addText($Left_Margin+620,$YPos, $FontSize, _('Price') );
$pdf->addText($Left_Margin+700,$YPos, $FontSize, _('Total') );

$YPos-=$line_height*.35;

/*draw a line */
$pdf->line($Left_Margin, $YPos, $Page_Width-$Right_Margin, $YPos);

?>
