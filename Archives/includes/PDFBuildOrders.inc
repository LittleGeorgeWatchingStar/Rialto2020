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
$pdf->addText($Left_Margin + 10, $YPos, $FontSize+2, _('Build Order Instructions') . '  -  ' .$WOID  ); 

$FontSize = 8;
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, Date($DefaultDateFormat) . '  (' . _('Page') . ' ' . $PageNumber . ')');
$box_height = $line_height * 8;
$YPos -=(6*$line_height);

/*Draw a rectangle to put the headings in     */
$pdf->line($Page_Width-$Right_Margin,	$YPos,			$Left_Margin, 			$YPos);
$pdf->line($Page_Width-$Right_Margin,	$YPos+$box_height,	$Left_Margin, 			$YPos+$box_height);
$pdf->line($Page_Width-$Right_Margin,	$YPos+$box_height,	$Page_Width-$Right_Margin,	$YPos);
$pdf->line($Left_Margin,		$YPos+$box_height,	$Left_Margin, 			$YPos);
$FontSize=10;

