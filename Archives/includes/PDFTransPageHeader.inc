<?php
/* $Revision: 1.5 $ */

/* R&OS PHP-pdf class code to head up a new page */

$YPos = $Page_Height - $Top_Margin;

$pdf->addJpegFromFile('logo.jpg',$Page_Width/2-100,$YPos-40);
$FontSize =15;
if ($InvOrCredit=='Invoice') {

        $pdf->addText($Page_Width - 200, $YPos, $FontSize, _('INVOICE') . ' ');
} else {
	$pdf->addText($Page_Width - 200, $YPos, $FontSize, _('CREDIT NOTE') . ' ');
}

$pdf->addPngFromFile('images/'.$_SESSION['UserID'].'.png',$Left_Margin+20,$Bottom_Margin + ($line_height*3), 140);

$XPos = $Page_Width - 285;
$YPos -=  85;
$AddrOffset = 11;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+225,$YPos+93,0,90,10,10);
/*line to the top left */
$pdf->line($XPos+225, $YPos+103,$XPos, $YPos+103);
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+93,$XPos-10, $YPos);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-10,$XPos+225, $YPos-10);
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+225, $YPos,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($XPos+235, $YPos,$XPos+235, $YPos+93);

$YPos = $Page_Height - $Top_Margin - 10;

$FontSize = 10;

$pdf->addText($Page_Width-268, $YPos- 1 *  $AddrOffset, $FontSize, _('Invoice Number'));
$pdf->addText($Page_Width-180, $YPos- 1 *  $AddrOffset, $FontSize, $FromTransNo);
$pdf->addText($Page_Width-268, $YPos- 2 *  $AddrOffset, $FontSize, _('Customer Code'));
$pdf->addText($Page_Width-180, $YPos- 2 *  $AddrOffset, $FontSize, $myrow['DebtorNo'] . ' ' . _('Branch') . ' ' . $myrow['BranchCode']);
$pdf->addText($Page_Width-268, $YPos- 3 *  $AddrOffset, $FontSize, _('Invoice Date'));
$pdf->addText($Page_Width-180, $YPos- 3 *  $AddrOffset, $FontSize, ConvertSQLDate($myrow['TranDate']));


if ($InvOrCredit=='Invoice') {

    $pdf->addText($Page_Width-268, $YPos - 4 * $AddrOffset, $FontSize, _('Order No'));
    // 00100 - Use the osCommerce order number in preference to the WebERP number.
    //$pdf->addText($Page_Width-180, $YPos - 4 * $AddrOffset, $FontSize, $myrow['OrderNo']);
    $user_friendly_order_no = $myrow['CustomerRef'] ?
        $myrow['CustomerRef'] : $myrow['OrderNo'];
    $pdf->addText($Page_Width-180, $YPos - 4 * $AddrOffset, $FontSize, $user_friendly_order_no);
    $pdf->addText($Page_Width-268, $YPos - 5 * $AddrOffset, $FontSize, _('Order Date'));
    $pdf->addText($Page_Width-180, $YPos - 5 * $AddrOffset, $FontSize, ConvertSQLDate($myrow['OrdDate']));
    $pdf->addText($Page_Width-268, $YPos - 6 * $AddrOffset, $FontSize, _('Tracking Number'));
    $pdf->addText($Page_Width-180, $YPos - 6 * $AddrOffset, $FontSize, $myrow['ShipperName'] . ': ' . $myrow['Consignment']);
}

/*End of the text in the right side box */

/*Now print out the company name and address in the middle under the logo */
$XPos = $Page_Width/2 -80;
$YPos = $Page_Height - $Top_Margin-40;
//	$pdf->addText($XPos, $YPos, $FontSize, $CompanyName);

$FontSize=8;
//	$pdf->addText($XPos, $YPos-10, $FontSize, _('Tax No') . ': ' . $CompanyRecord['GSTNo']);
//	$pdf->addText($XPos, $YPos-00, $FontSize, $CompanyRecord['PostalAddress']);
$pdf->addText($XPos, $YPos-10, $FontSize, $CompanyRecord['RegOffice1']);
$pdf->addText($XPos, $YPos-20, $FontSize, $CompanyRecord['RegOffice2']);
$pdf->addText($XPos, $YPos-30, $FontSize, _('Email') . ': ' . $CompanyRecord['Email']);

/*Now the customer charged to details top left */

$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin;

$FontSize=10;

$pdf->addText($XPos, $YPos, $FontSize, _('Sold To') . ':');
$XPos +=60;
$add_coname = (($myrow['CompanyName']=='') ? 0 : 1);
if ($myrow['InvAddrBranch']==0)
{
    $skip_line = (($myrow['Addr2']. $myrow['MailStop']== '') ? $add_coname : $add_coname + 1);
    $pdf->addText($XPos, $YPos,                              $FontSize, $myrow['CompanyName']);
    $pdf->addText($XPos, $YPos-(0+$add_coname) * $AddrOffset,	 $FontSize, $myrow['Name']);
    $pdf->addText($XPos, $YPos-(1+$add_coname) * $AddrOffset, $FontSize, $myrow['Addr1']);
    $pdf->addText($XPos, $YPos-(2+$add_coname) * $AddrOffset, $FontSize, $myrow['Addr2']. '   '  .$myrow['MailStop']);
    $pdf->addText($XPos, $YPos-(2+$skip_line)  * $AddrOffset, $FontSize, $myrow['City'] . ', ' . $myrow['State']. ' ' . $myrow['Zip']);
    $pdf->addText($XPos, $YPos-(3+$skip_line)  * $AddrOffset, $FontSize, $myrow['Country']);
}
else
{
    $skip_line = (($myrow['BrAddr2']. $myrow['BrMailStop']== '') ? $add_coname : $add_coname + 1);
    $pdf->addText($XPos, $YPos,                             $FontSize, $myrow['CompanyName']);
    $pdf->addText($XPos, $YPos-(0+$add_coname) * $AddrOffset,        $FontSize, $myrow['Name']);
    $pdf->addText($XPos, $YPos-(1+$add_coname) * $AddrOffset, $FontSize, $myrow['BrPostAddr1']);
    $pdf->addText($XPos, $YPos-(2+$add_coname) * $AddrOffset, $FontSize, $myrow['BrAddr2']. $myrow['BrMailStop']);
    $pdf->addText($XPos, $YPos-(2+$skip_line)  * $AddrOffset, $FontSize, $myrow['BrCity'] . ', ' . $myrow['BrState']. ' ' . $myrow['BrZip']);
    $pdf->addText($XPos, $YPos-(3+$skip_line)  * $AddrOffset, $FontSize, $myrow['BrCountry']);
}

$XPos -=60;
$YPos -=((5+$skip_line)  * $AddrOffset);
$add_coname = (($myrow['DelCompanyName']=='') ? 0 : 1);
if ($InvOrCredit=='Invoice') {
	$pdf->addText($XPos, $YPos,				 $FontSize, _('Deliver To') . ':');
	$XPos +=60;
	$DeliverTo = ( ($myrow['DeliverTo'] != '')? $myrow['DeliverTo'] : $myrow['Name'] );
        $skip_line = (($myrow['DelAddr2']. $myrow['DelMailStop']== '') ? $add_coname : $add_coname + 1);
	$pdf->addText($XPos, $YPos,				 $FontSize, $DeliverTo);
        $pdf->addText($XPos, $YPos-(0+$add_coname) * $AddrOffset, $FontSize, $myrow['DelCompanyName']);
	$pdf->addText($XPos, $YPos-(1+$add_coname) * $AddrOffset, $FontSize, $myrow['DelAddr1']);
	$pdf->addText($XPos, $YPos-(2+$add_coname) * $AddrOffset, $FontSize, $myrow['DelAddr2']) . $myrow['DelMailStop'];
	$pdf->addText($XPos, $YPos-(2+$skip_line)  * $AddrOffset, $FontSize, $myrow['DelCity'] . ', ' . $myrow['DelState']. ' ' . $myrow['DelZip']);
        $pdf->addText($XPos, $YPos-(3+$skip_line)  * $AddrOffset, $FontSize, $myrow['DelCountry']);
	$XPos -=60;
}
if ($InvOrCredit=='Credit'){
/* then its a credit note */

	$pdf->addText($XPos, $YPos,		  $FontSize, _('Charge Branch') . ':');
	$XPos +=60;
	$pdf->addText($XPos, $YPos,		  $FontSize, $myrow['BrName']);
	$pdf->addText($XPos, $YPos-$AddrOffset,	  $FontSize, $myrow['BrAddr1']);
	$pdf->addText($XPos, $YPos-2*$AddrOffset, $FontSize, $myrow['BrAddr2']) . $myrow['BrMailStop'];
        $pdf->addText($XPos, $YPos-3*$AddrOffset, $FontSize, $myrow['BrCity'] . ', ' . $myrow['BrState']. ' ' . $myrow['BrZip']);
        $pdf->addText($XPos, $YPos-4*$AddrOffset, $FontSize, $myrow['BrCountry']);
	$XPos -=60;
}

$XPos = $Left_Margin;

$YPos = $Page_Height - $Top_Margin - 90;
/*draw a line under the company address and charge to address
$pdf->line($XPos, $YPos,$Right_Margin, $YPos); */

$XPos = $Page_Width/2;

$XPos = $Left_Margin;
$YPos -= ($line_height*2);

//$pdf->addText($Left_Margin, $YPos-8, $FontSize+2, _('All amounts stated in') . ' - ' . $myrow['CurrCode']);

/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+390,0,90,10,10);
/*line to the top left */
$pdf->line($Page_Width-$Right_Margin-10, $Bottom_Margin+400,$Left_Margin+10, $Bottom_Margin+400);
/*Do top left corner */
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+390,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($Left_Margin, $Bottom_Margin+390,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($Left_Margin+10, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $Bottom_Margin+390);


$YPos -= ($line_height*2);
/*Set up headings */
$FontSize=10;
$pdf->addText($Left_Margin + 2, $YPos, $FontSize, _('Customer ABN') . ':');
/* Need a field to enter the ABN into to print out here */

/*Print a vertical line */
$pdf->line($Left_Margin+248, $YPos+$line_height+3,$Left_Margin+248, $YPos - 8);

$pdf->addText($Left_Margin + 252, $YPos, $FontSize, $myrow['CustomerRef']);

/*Print a vertical line */
$pdf->line($Left_Margin+450, $YPos+$line_height+3,$Left_Margin+450,$YPos-8);

$pdf->addText($Left_Margin+453, $YPos, $FontSize, _('Sales Person') . ':');
$pdf->addText($Left_Margin+530, $YPos, $FontSize, $myrow['SalesmanName']);

$YPos -= 8;
/*draw a line */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);

$YPos -= 12;

$TopOfColHeadings = $YPos;

$pdf->addText($Left_Margin+5, $YPos, $FontSize, _('Item Code'));
$pdf->addText($Left_Margin+100, $YPos, $FontSize, _('Description'));
$pdf->addText($Left_Margin+390, $YPos, $FontSize, _('Origin'));
$pdf->addText($Left_Margin+435, $YPos, $FontSize, _('Harmonization'));
$pdf->addText($Left_Margin+520, $YPos, $FontSize, _('Unit Price'));
$pdf->addText($Left_Margin+585, $YPos, $FontSize, _('Quantity'));
$pdf->addText($Left_Margin+632, $YPos, $FontSize, _('UOM'));
$pdf->addText($Left_Margin+665, $YPos, $FontSize, _('Disc%'));
$pdf->addText($Left_Margin+705, $YPos, $FontSize, _('Extd Price'));

$YPos-=8;

/*draw a line */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);

$YPos -= ($line_height);

?>
