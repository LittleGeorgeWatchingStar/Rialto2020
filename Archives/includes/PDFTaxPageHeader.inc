<?php
/* $Revision: 1.3 $ */
/*PDF page header for aged analysis reports */
$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=8;
$YPos= $Page_Height-$Top_Margin;

$pdf->addText($Left_Margin, $YPos,$FontSize, $CompanyName);

$YPos -=$line_height;

$FontSize =10;
$pdf->addText($Left_Margin, $YPos, $FontSize, $ReportTitle);

$FontSize = 8;
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, _('Printed') . ': ' . Date("d M Y") . '    ' . _('Page') . ' ' . $PageNumber);

$YPos -=(3*$line_height);

/*Draw a rectangle to put the headings in     */
$pdf->line($Page_Width-$Right_Margin, $YPos-5,$Left_Margin, $YPos-5);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Left_Margin, $YPos+$line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos-5);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos-5);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Left_Margin +  10,$YPos,150 - $Left_Margin,	$FontSize,	_('Regime and Code'),	'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin + 200,$YPos, 50,			$FontSize,	_('Sales'),		'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin + 300,$YPos, 50,			$FontSize,	_('Rate'),		'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin + 400,$YPos, 50,			$FontSize,	_('Tax'),		'right');

$YPos =$YPos - (2*$line_height);

?>
