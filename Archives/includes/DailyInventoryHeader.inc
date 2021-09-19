<?php

/*PDF page header for inventory check report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=13;
$YPos= $Page_Height-$Top_Margin;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize, Date($DefaultDateFormat), 'left' );
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize, _('Daily Product Checklist') , 'center' );
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize, '   ' . _('Page'). ' ' . $PageNumber, 'right');

$YPos -=45;
/*Draw a rectangle to put the headings in     */
$BoxHeight =30;
$FontSize=8;
$pdf->line($Left_Margin-5, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos+$BoxHeight);
$pdf->line($Left_Margin-5, $YPos+$BoxHeight,$Left_Margin-5, $YPos);
$pdf->line($Left_Margin-5, $YPos,$Page_Width-$Right_Margin, $YPos);
$pdf->line($Page_Width-$Right_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos);

$YPos +=15;

/*set up the headings */
$Xpos = $Left_Margin+1;
//	$LeftOvers = $pdf->addTextWrap($Left_Margin      ,$YPos,    70,$FontSize,  'StockID',  'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin +  40,$YPos,   120,$FontSize,  'Product    ','left');
$LeftOvers = $pdf->addTextWrap($Left_Margin +  40,$YPos-10,120,$FontSize,  'Description','left');

$LeftOvers = $pdf->addTextWrap($Left_Margin + 150,$YPos,   050,$FontSize,  'webERP',  	 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin + 150,$YPos-10,050,$FontSize,  'orders',	 'right');

$LeftOvers = $pdf->addTextWrap($Left_Margin + 200,$YPos,   050,$FontSize,  'website',	 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin + 200,$YPos-10,050,$FontSize,  'orders',	 'right');

$LeftOvers = $pdf->addTextWrap($Left_Margin + 250,$YPos,   050,$FontSize,  'Total',	 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin + 250,$YPos-10,050,$FontSize,  'Orders',	 'right');

$LeftOvers = $pdf->addTextWrap($Left_Margin + 300,$YPos,   050,$FontSize,  'Total',      'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin + 300,$YPos-10,050,$FontSize,  'In Stock',   'right');

//	$LeftOvers = $pdf->addTextWrap($Left_Margin + 350,$YPos,   050,$FontSize,  'Order',	 'right');
//	$LeftOvers = $pdf->addTextWrap($Left_Margin + 350,$YPos-10,050,$FontSize,  'Point',	 'right');

 $LeftOvers = $pdf->addTextWrap($Left_Margin + 350,$YPos,   050,$FontSize,  'COUNT',      'right');

$LeftOvers = $pdf->addTextWrap($Left_Margin + 400,$YPos,   050,$FontSize,  'On',  	 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin + 400,$YPos-10,050,$FontSize,  'Order', 	 'right');

$add_bins  = $pdf->addTextWrap($Left_Margin + 450,$YPos,   050,$FontSize,  'website',	 'right');
$add_bins  = $pdf->addTextWrap($Left_Margin + 450,$YPos-10,054,$FontSize,  'availability','right');

$add_bins  = $pdf->addTextWrap($Left_Margin + 500,$YPos,   050,$FontSize,  'website',    'right');
$add_bins  = $pdf->addTextWrap($Left_Margin + 500,$YPos-10,050,$FontSize,  'shows',      'right');

$FontSize=10;
$YPos -= 40;

?>
