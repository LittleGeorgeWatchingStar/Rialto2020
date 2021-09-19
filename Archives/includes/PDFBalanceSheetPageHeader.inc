<?php
	/*
	 * PDF page header for the balance sheet report.
	 * Suren Naidu 10/08/2005
	 *
	 */

use Rialto\UtilBundle\Fonts;

$PageNumber++;
	if ($PageNumber>1){
		$pdf->newPage();
	}

	$FontSize = 8;
	$YPos = $Page_Height - $Top_Margin;
	$pdf->addText($Left_Margin,$YPos,$FontSize,$CompanyRecord['CoyName']);
        $pdf->addText($Left_Margin,$YPos-= $line_height,$FontSize,$CompanyRecord['RegOffice1']);
        $pdf->addText($Left_Margin,$YPos-= $line_height,$FontSize,$CompanyRecord['RegOffice2']);

	$YPos -= $line_height;
	$FontSize =10;
	$pdf->selectFont(Fonts::find('Helvetica-Bold'));
	$Heading = _('Balance Sheet as at ') . $BalanceDate;
	$pdf->addText($Left_Margin+225, $YPos + 2* $line_height, $FontSize+2, $Heading);

	$YPos -= (2 * $line_height);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,$BalanceDate,'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,'Last Year','right');
	$YPos -= (2 * $line_height);
?>
