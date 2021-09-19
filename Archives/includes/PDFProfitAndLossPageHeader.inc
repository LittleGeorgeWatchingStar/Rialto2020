<?php
	/*
	 * PDF page header for the profit and loss report.
	 * Suren Naidu 28/08/2005
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
	$FontSize =9;
	$pdf->selectFont(Fonts::find('Helvetica-Bold'));
	$Heading = _('Profit and loss for the ') . $NumberOfMonths . _(' months to ') . $PeriodToDate;
	$pdf->addText($Left_Margin+200, $YPos +2* $line_height, $FontSize, $Heading);

	$FontSize = 8;
	$pdf->selectFont(Fonts::find('Helvetica'));

	$YPos -= (2 * $line_height);
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Account'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,100,$FontSize,_('Account Name'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,_('Period Actual'),'right');
//	$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,_('Period Budget'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,_('Prior Year'),'right');
	$YPos -= (1 * $line_height);
?>
