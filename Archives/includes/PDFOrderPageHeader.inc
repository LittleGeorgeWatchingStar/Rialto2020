<?php
use Gumstix\GeographyBundle\Model\Country;


$pdf->addJpegFromFile('logo.jpg', $XPos = ($Page_Width / 2 - 80) , $YPos = 525 );

$FontSize =13;
$YPos -= 10;
$XPos += 20;

$pdf->addText($XPos, $YPos,	$FontSize, _('Gumstix,  Inc.'));
$pdf->addText($XPos, $YPos-13,	$FontSize, _('3130 Alpine Road, Suite 288-606'));
$pdf->addText($XPos, $YPos-26,	$FontSize, _('Portola Valley, CA 94028'));
$pdf->addText($XPos, $YPos-39,	$FontSize, _('http://www.gumstix.com'));

$XPos = 40;

/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+400,0,90,10,10);
/*line to the top left */
$pdf->line($Page_Width-$Right_Margin-10, $Bottom_Margin+410,$Left_Margin+10, $Bottom_Margin+410);
/*Dow top left corner */
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+400,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($Left_Margin, $Bottom_Margin+400,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($Left_Margin+10, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $Bottom_Margin+400);

$YPos = 550;

$pdf->addText($XPos, $YPos,$FontSize, $myrow['Name']);
$pdf->addText($XPos, $YPos-13,$FontSize, $myrow['CompanyName']);
$pdf->addText($XPos, $YPos-26,$FontSize, $myrow['Addr1']);
$pdf->addText($XPos, $YPos-39,$FontSize, $myrow['Addr2']);
$pdf->addText($XPos, $YPos-52,$FontSize, $myrow['City'] . ', ' . $myrow['State'] . ' ' . $myrow['Zip']);
$pdf->addText($XPos, $YPos-65,$FontSize, Country::resolveCountryName($myrow['Country']));

$XPos = $Left_Margin+10;
$YPos -= 75;

/*draw a nice curved corner box around the billing details from the top right */
$pdf->partEllipse($XPos+225,$YPos+93,0,90,10,10);
/*line to the top left */
$pdf->line($XPos+225, $YPos+103,$XPos, $YPos+103);
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+93,$XPos-10, $YPos);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-10,$XPos+225, $YPos-10);
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+225, $YPos,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($XPos+235, $YPos,$XPos+235, $YPos+93);


$XPos=550;
$YPos=500;

$pdf->addText($XPos, $YPos,    $FontSize,  _('Order No')      . ': ' . $TransNo                );
$pdf->addText($XPos, $YPos-14, $FontSize,  _('Your Ref')      . ': ' . $myrow['CustomerRef']   );
$pdf->addText($XPos, $YPos+14, $FontSize,  _('Order Date')    . ': ' . $myrow['OrdDate']        );
$pdf->addText($XPos, $YPos-28, $FontSize,  _('Todays Date')   . ': ' . Date('Y-m-d')        );



/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+400,0,90,10,10);
/*line to the top left */
$pdf->line($Page_Width-$Right_Margin-10, $Bottom_Margin+410,$Left_Margin+10, $Bottom_Margin+410);
/*Dow top left corner */
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+400,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($Left_Margin, $Bottom_Margin+400,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($Left_Margin+10, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $Bottom_Margin+400);

$XPos=110;
$YPos=86;
$index=0;
$LeftOvers = $myrow['Comments'];
while (strlen($LeftOvers)>2){
	$LeftOvers = $pdf->addTextWrap($Left_Margin+10,$Bottom_Margin + ( 8 - $index++) *16 ,400,$FontSize,$LeftOvers);
}

$YPos = 414+20;

?>
