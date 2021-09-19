<?php
/* $Revision: 1.4 $ */
/*PDF page header for inventory valuation report */

$PageNumber++;
/* first time round $PageNumber will only be 1 and page created on initiation of $pdf */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=8;
$YPos= $Page_Height-$Top_Margin;
$YPos -=$line_height;

$FontSize =14;
$pdf->addText($Left_Margin + 250, $YPos, $FontSize, $_POST["OrderNumber"]. $_POST["VersionNumber"] );

$FontSize =10;
$pdf->addText($Left_Margin, $YPos, $FontSize, _('Bill Of Material Listing') ); 

$FontSize = 8;
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, _('Printed') . ': ' . Date($DefaultDateFormat) . '   ' . _('Page') . ' ' . $PageNumber);

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */
$pdf->line($Page_Width-$Right_Margin, $YPos-5,$Left_Margin, $YPos-5);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Left_Margin, $YPos+$line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos-5);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos-5);

/*set up the headings */
$Xpos = $Left_Margin+1;
if ( $PageNumber == 1 ) {
	$LeftOvers = $pdf->addTextWrap($Xpos+ 30,$YPos, 20 ,$FontSize,_('Tool'),'center');
	$LeftOvers = $pdf->addTextWrap($Xpos+ 50,$YPos,100 ,$FontSize,_('Drill'),'center');
	$LeftOvers = $pdf->addTextWrap($Xpos+150,$YPos, 50 ,$FontSize,_('Count'),'left');
	$LeftOvers = $pdf->addTextWrap($Xpos+180,$YPos, 50 ,$FontSize,_('Plated'),'left');
}		
$YPos =$YPos - (2*$line_height);
$FontSize=10;
?>
