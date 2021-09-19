<?php
/* $Revision: 1.3 $ */
if ($PageNumber>1){
	$pdf->newPage();
}

$Top_Margin = 15;
$YPos = $Page_Height - $Top_Margin - 15;
$FontSize=15;

$pdf->addJpegFromFile('logo.jpg',$Left_Margin,$YPos,90,30);
$pdf->addText( $Left_Margin + 150,           $YPos + $FontSize - 6, $FontSize, $_POST['LedgerName'] . ': ' . $_POST['FromDate'] . ' ' . _('to') . ' ' .  $_POST['ToDate']);
$pdf->addText( $Page_Width-$Right_Margin-50, $YPos + $FontSize - 6, $FontSize, _('Page') . ': ' . $PageNumber);

/*Now print out the company name and address */
$XPos = $Left_Margin - 2;

/*draw a square grid for entering line items */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);
$pdf->line($Page_Width-$Right_Margin, $YPos,$Page_Width-$Right_Margin, $Bottom_Margin);
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin,$XPos, $Bottom_Margin);
$pdf->line($XPos, $Bottom_Margin,$XPos, $YPos);

$YPos -= $line_height;
/*Set up headings */
$FontSize=8;

$XPos +=2;

$i = 0;
foreach ( $columns as $col_name => $field ) {
	switch ( $col_name ) {
                        case 'Date' :                   $field_width =  70;       break;
                        case 'Reference':               $field_width = 250;       break;
                        case 'Comments':                $field_width = 100;       break;
                        case 'Type':                    $field_width =  80;       break;
                        case 'ID':                      $field_width =  30;       break;
                        default:                        $field_width =  35;       break;
        }
        switch ( $col_name ) {
               case 'Type':
	       case 'ID':    
		case 'Order':
               case 'Amount':  $LeftOvers = $pdf->addTextWrap($Left_Margin+ $i,  $YPos, $field_width - 4,$FontSize,  $col_name , 'right'); break;
               default:        $LeftOvers = $pdf->addTextWrap($Left_Margin+ $i,  $YPos, $field_width,    $FontSize,  $col_name , 'left'); break;
        }
	$i += $field_width;
}

$YPos-=$line_height;

/*draw a line */
$pdf->line( $XPos, $YPos + $line_height/2, $Page_Width-$Right_Margin, $YPos + $line_height/2 );

$YPos-=$line_height/2

?>
