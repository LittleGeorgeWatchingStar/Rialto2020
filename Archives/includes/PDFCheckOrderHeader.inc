<?php
/* $Revision: 1.3 $ */
/* pdf-php by R&OS code to set up a new sales order page */
//if ($PageNumber>1){
//	$pdf->newPage();
//}

$pdf->addJpegFromFile('logo.jpg', $XPos = ($Page_Width / 4 - 80) , $YPos = 700 ); 

$FontSize =13;
$YPos -= 10;
$XPos += 20;

$pdf->addText($XPos, $YPos,	$FontSize, _('gumstix inc.'));
$pdf->addText($XPos, $YPos-13,	$FontSize, _('PO Box 7187')); 
$pdf->addText($XPos, $YPos-26,	$FontSize, _('Menlo Park, CA 94026-7187'));
$pdf->addText($XPos, $YPos-39,	$FontSize, _('http://www.gumstix.com'));

$YPos = 550;
$pdf->addText($XPos, $YPos,$FontSize, $myrow['Name']);
$pdf->addText($XPos, $YPos-13,$FontSize, $myrow['Addr1']);
$pdf->addText($XPos, $YPos-26,$FontSize, $myrow['Addr2']);
$pdf->addText($XPos, $YPos-39,$FontSize, $myrow['City'] . ', ' . $myrow['State'] . ' ' . $myrow['Zip']);
$pdf->addText($XPos, $YPos-52,$FontSize, $myrow['Country']);

$XPos=350;
$YPos=740;
$FontSize-=1;
$pdf->addText($XPos, $YPos,    $FontSize,  _('Order No')      . ': ' . $_GET['OrderNo']                );
$pdf->addText($XPos, $YPos-14, $FontSize,  _('Reference')      . ': ' . $myrow['CustomerRef']   );
$pdf->addText($XPos, $YPos+14, $FontSize,  _('Order Date')    . ': ' . $myrow['OrdDate']        );
$pdf->addText($XPos, $YPos-28, $FontSize,  _('Todays Date')   . ': ' . Date('Y-m-d')        );

$pdf->addText($XPos, $YPos-50, $FontSize,  _('Deliver To')      . ': '. $myrow['deliverto']                );
$pdf->addText($XPos, $YPos-61, $FontSize,  _('Deliver To')      . ': '. $myrow['CompanyName']                );
$pdf->addText($XPos, $YPos-73, $FontSize,  _('Address  ')      . ': ' . $myrow['DelAddr1']   . "   " . $myrow['DelAddr2']   );
$pdf->addText($XPos, $YPos-84, $FontSize,  _('City State')    . ': '  . $myrow['DelCity']    . "   " . $myrow['DelState']        );
$pdf->addText($XPos, $YPos-95, $FontSize,  _('Country ZIP')   . ': '  . $myrow['DelCountry'] . "   " . $myrow['DelZip']         );


$topper = 150;

/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+$topper+400,0,90,10,10);
/*line to the top left */
$pdf->line($Page_Width-$Right_Margin-10, $Bottom_Margin+$topper+410,$Left_Margin+10, $Bottom_Margin+$topper+410);
/*Dow top left corner */
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+$topper+400,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($Left_Margin, $Bottom_Margin+$topper+400,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($Left_Margin+10, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $Bottom_Margin+$topper+400);

$XPos=110;
$YPos=86;
$LeftOvers = $pdf->addTextWrap($XPos,$YPos,300,$FontSize,$myrow['Comments']);

if (strlen($LeftOvers)>1){
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos-14,170,$FontSize,$LeftOvers);
	if (strlen($LeftOvers)>1){
		$LeftOvers = $pdf->addTextWrap($XPos,$YPos-28,170,$FontSize,$LeftOvers);
		if (strlen($LeftOvers)>1){
			$LeftOvers = $pdf->addTextWrap($XPos,$YPos-42,170,$FontSize,$LeftOvers);
			if (strlen($LeftOvers)>1){
				$LeftOvers = $pdf->addTextWrap($XPos,$YPos-56,170,$FontSize,$LeftOvers);
			}
		}
	}
}
$YPos = 600;
?>
