<?php
/* $Revision: 1.3 $ */
/*PDF page header for inventory check report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=12;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$CompanyName);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,_('Printed') . ': ' . Date('d M Y') . '   ' . _('Page') . ' ' . $PageNumber);

$YPos -=15;
$YPos -=20;
/*Draw a rectangle to put the headings in     */
$BoxHeight =15;

$pdf->line($Left_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos+$BoxHeight);
$pdf->line($Left_Margin, $YPos+$BoxHeight,$Left_Margin, $YPos- $BoxHeight);
$pdf->line($Left_Margin, $YPos-$BoxHeight,$Page_Width-$Right_Margin, $YPos-$BoxHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos-$BoxHeight);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,300-$Left_Margin,$FontSize,_('Item'), 'centre');
$LeftOvers = $pdf->addTextWrap(280, $YPos,60,$FontSize,_('TechDrive'), 'right');
$LeftOvers = $pdf->addTextWrap(340, $YPos,60,$FontSize,_('Innerstep'), 'right');
$LeftOvers = $pdf->addTextWrap(400, $YPos,60,$FontSize,_('Bestek'), 'right');
$LeftOvers = $pdf->addTextWrap(460, $YPos,60,$FontSize,_('A&J'), 'right');
$LeftOvers = $pdf->addTextWrap(520, $YPos,60,$FontSize,_('CircuitCo'), 'right');

$FontSize=10;
$YPos -=($line_height);
?>
