<?php
/* $Revision: 1.4 $ */
/* PDF page header for kitting a work order */

$PageNumber++;
/* first time round $PageNumber will only be 1 and page created on initiation of $pdf */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=8;
$YPos= $Page_Height-$Top_Margin;
$YPos -=$line_height;

$FontSize =14;
//$wo_list = str_replace( array( '(', ')' ), array( '',''), $WO_List );
//$wo_list = split(",",  $wo_list );
//foreach ( $wo_list as $wo_i => $wo_n ) {
$index = 0;
foreach ( $this->selectedWOs as $wo ) {
	$pdf->addText(
	   $Left_Margin + 30 + 80 * $index,
	   $YPos - $line_height,
	   $FontSize-2,
	   //GetPO_AttachedToWO(trim($wo_n),$db) . '(' . $wo_n . ')'
	   sprintf('%s (%s)', $wo->getPurchaseOrderNumber(), $wo->getId())
   );
   $index ++;
}

$FontSize =10;
$pdf->addText($Left_Margin + 10, $YPos, $FontSize+2, _('Kitting:  Preshipping Checklist') );

$FontSize = 8;
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, Date('Y-m-d') . '  (' . _('Page') . ' ' . $PageNumber . ')');
$box_height = $line_height * 4;
$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */
$pdf->line($Page_Width-$Right_Margin,	$YPos,			$Left_Margin, 			$YPos);
$pdf->line($Page_Width-$Right_Margin,	$YPos+$box_height,	$Left_Margin, 			$YPos+$box_height);
$pdf->line($Page_Width-$Right_Margin,	$YPos+$box_height,	$Page_Width-$Right_Margin,	$YPos);
$pdf->line($Left_Margin,		$YPos+$box_height,	$Left_Margin, 			$YPos);
$FontSize=10;

