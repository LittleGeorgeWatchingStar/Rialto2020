<?php

/*PDF page header for inventory check report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=12;
$YPos= $Page_Height-$Top_Margin;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$CompanyName);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize, _('Printed'). ': ' . Date($DefaultDateFormat) . '   ' . _('Page'). ' ' . $PageNumber);

$YPos -=15;
$FontSize=12;
$LeftOvers = $pdf->addTextWrap(
    $Left_Margin,
    $YPos,
    550,
    $FontSize,
    _('Transfer No') .': ' . $_GET['TransferNo'] . '  ' .
        _('From').' ' . $TransferRow['ShipLocName'] .
        ' to ' . $TransferRow['RecLocName'] . ' ' .
        _('Shipped'). ' ' . ConvertSQLDate($TransferRow['ShipDate'])
);

$YPos -=25;
$sql = "SELECT LocTransfersDetail.WORef,
        WorksOrders.OrderNo
		FROM LocTransfersDetail
		LEFT JOIN WorksOrders ON WorksOrders.WORef=LocTransfersDetail.WORef
		WHERE LocTransfersID=" . $_GET['TransferNo'];
$ret = DB_query( $sql,  $db );
while ($row=DB_fetch_array( $ret )) {
	$BOList .= '   ' . $row['WORef'] . ' (PO: ' . $row['OrderNo'] . ')';
}
if ($BOList != '') {
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize+2, "For use in build orders: $BOList" );
	$YPos -=45;
}
/*Draw a rectangle to put the headings in     */
$BoxHeight =20;
$FontSize=12;
$pdf->line($Left_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos+$BoxHeight);
$pdf->line($Left_Margin, $YPos+$BoxHeight,$Left_Margin, $YPos);
$pdf->line($Left_Margin, $YPos,$Page_Width-$Right_Margin, $YPos);
$pdf->line($Page_Width-$Right_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos);

$YPos +=5;

/*set up the headings */
$Xpos = $Left_Margin+1;
$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,100-$Left_Margin,$FontSize,_('Code'), 'left');
$LeftOvers = $pdf->addTextWrap(150,$YPos,150-$Left_Margin,$FontSize,_('Description'), 'left');
$LeftOvers = $pdf->addTextWrap(300,$YPos,50,$FontSize,_('Qty Shipped'), 'right');
$LeftOvers = $pdf->addTextWrap(380,$YPos,50,$FontSize,_('ReelID#'), 'right');
$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,_("Qty Rec'd"), 'right');
$LeftOvers = $pdf->addTextWrap(520,$YPos,60,$FontSize,_("Qty Ret'd"), 'right');

$FontSize=10;
$YPos -= 30;

?>
