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
$BoxHeight =20;

$pdf->line($Left_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos+$BoxHeight);
$pdf->line($Left_Margin, $YPos+$BoxHeight,$Left_Margin, $YPos- $BoxHeight);
$pdf->line($Left_Margin, $YPos-$BoxHeight,$Page_Width-$Right_Margin, $YPos-$BoxHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos-$BoxHeight);

/*set up the headings */
$Xpos = $Left_Margin+1;
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 100,  $YPos, 250, $FontSize,"Statement for ".$_POST['TransMonth'] );
$YPos-=15;
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 100,  $YPos,  50, $FontSize,'Date');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 150,  $YPos,  50, $FontSize,'Type');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 200,  $YPos,  50, $FontSize,'Number');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 250,  $YPos, 150, $FontSize,'Reference');
$LeftOvers = $pdf->addTextWrap( $Left_Margin + 400,  $YPos,  50, $FontSize,'Amount');

$FontSize=10;
$YPos -=($line_height);
?>
