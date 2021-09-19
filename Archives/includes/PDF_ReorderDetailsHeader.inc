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

$LeftOvers = $pdf->addTextWrap( $Left_Margin +  55, $YPos, 100, $FontSize-4,		_('Item'), 'centre');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 125, $YPos,  50, $FontSize-4,		_('QOH'), 'centre');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 175, $YPos,  50, $FontSize-4,		_('Velocity'), 'centre');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 225, $YPos,  50, $FontSize-4,		_('Order Pt'), 'centre');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 275, $YPos,  50, $FontSize-4,		_('EOQ'), 'centre');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 325, $YPos,  50, $FontSize-4,		_('Days Left'), 'centre');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 375, $YPos,  50, $FontSize-4,            _('Lead time'), 'centre');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 450, $YPos,  50, $FontSize-4,            _('Capital'), 'centre');

$FontSize=10;
$YPos -=($line_height);
?>
