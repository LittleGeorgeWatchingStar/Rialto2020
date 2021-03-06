<?php

/* $Revision: 1.5 $ */
use Rialto\AccountingBundle\Entity\Currency;use Rialto\AccountingBundle\Entity\BankAccount;$PageSecurity = 3;
include('includes/SQL_CommonFunctions.inc');
include('includes/DateFunctions.inc');
include ('includes/session.inc');

$InputError = 0;
if ( isset($_POST['FromDate']) AND ! Is_Date($_POST['FromDate']) ) {
    $msg = _('The date from must be specified in the format') . ' ' . $DefaultDateFormat;
    $InputError = 1;
    unset($_POST['FromDate']);
}
if ( ! Is_Date($_POST['ToDate']) AND isset($_POST['ToDate']) ) {
    $msg = _('The date to must be specified in the format') . ' ' . $DefaultDateFormat;
    $InputError = 1;
    unset($_POST['ToDate']);
}

if ( ! isset($_POST['FromDate']) OR ! isset($_POST['ToDate']) ) {

    $title = _('Payment Listing');
    include ('includes/header.inc');

    if ( $InputError == 1 ) {
        prnMsg($msg, 'error');
    }

    echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '>';
    echo '<CENTER><TABLE>
     			<TR>
				<TD>' . _('Enter the date from which cheques are to be listed') . ":</TD>
				<TD><INPUT TYPE=text NAME='FromDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat) . "'></TD>
			</TR>";
    echo '<TR><TD>' . _('Enter the date to which cheques are to be listed') . ":</TD>
     		<TD><INPUT TYPE=text NAME='ToDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat) . "'></TD>
	</TR>";
    echo '<TR><TD>' . _('Bank Account') . '</TD><TD>';

    $sql = 'SELECT BankAccountName, AccountCode FROM BankAccounts';
    $result = DB_query($sql, $db);


    echo "<SELECT NAME='BankAccount'>";

    while ( $myrow = DB_fetch_array($result) ) {
        echo '<OPTION VALUE=' . $myrow['AccountCode'] . '>' . $myrow['BankAccountName'];
    }


    echo '</SELECT></TD></TR>';

    echo '<TR><TD>' . _('Email the report off') . ":</TD><TD><SELECT NAME='Email'>";
    echo "<OPTION SELECTED VALUE='No'>" . _('No');
    echo "<OPTION VALUE='Yes'>" . _('Yes');
    echo "</SELECT></TD></TR></TABLE><INPUT TYPE=SUBMIT NAME='Go' VALUE='" . _('Create PDF') . "'></CENTER>";


    include('includes/footer.inc');
    exit;
}
else {

    include('includes/ConnectDB.inc');
}


$SQL = 'SELECT BankAccountName
	FROM BankAccounts
	WHERE AccountCode = ' . $_POST['BankAccount'];
$BankActResult = DB_query($SQL, $db);
$myrow = DB_fetch_row($BankActResult);
$BankAccountName = $myrow[0];

$SQL = "SELECT Amount,
		Ref,
		TransDate,
		BankTransType,
		Type,
		TransNo,
        ChequeNo,
        TypeName
	FROM BankTrans
    JOIN SysTypes ON BankTrans.Type = SysTypes.TypeID
	WHERE BankTrans.BankAct=" . $_POST['BankAccount'] . "
	AND BankTrans.Type in (22, 101)
	AND TransDate >='" . FormatDateForSQL($_POST['FromDate']) . "'
	AND TransDate <='" . FormatDateForSQL($_POST['ToDate']) . "'
	ORDER BY TransDate";


$Result = DB_query($SQL, $db, '', '', false, false);
if ( DB_error_no($db) != 0 ) {
    $title = _('Payment Listing');
    include('includes/header.inc');
    prnMsg(_('An error occurred getting the payments'), 'error');
    if ( $Debug == 1 ) {
        prnMsg(_('The SQL used to get the receipt header information that failed was') . ':<BR>' . $SQL, 'error');
    }
    include('includes/footer.inc');
    exit;
}
elseif ( DB_num_rows($Result) == 0 ) {
    $title = _('Payment Listing');
    include('includes/header.inc');
    prnMsg(_('There were no bank transactions found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'] . '. ' . _('Please try again selecting a different date range or account'), 'error');
    include('includes/footer.inc');
    exit;
}

$CompanyRecord = ReadInCompanyRecord($db);

include('includes/PDFStarter_ros.inc');

/* PDFStarter_ros.inc has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addinfo('Title', _('Cheque Listing'));
$pdf->addinfo('Subject', _('Cheque listing from') . '  ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);

$line_height = 12;
$PageNumber = 1;

$TotalCheques = 0;

include ('includes/PDFChequeListingPageHeader.inc');

while ( $myrow = DB_fetch_array($Result) ) {

    $pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,number_format(-$myrow['Amount'],2),
        'right');
    $pdf->addTextWrap($Left_Margin + 60, $YPos,100, $FontSize, $myrow['TypeName']);
	$pdf->addTextWrap($Left_Margin + 160,$YPos, 90, $FontSize, $myrow['Ref'], 'left');

    $sql = "SELECT AccountName,Amount,SUBSTRING(Narrative,2+LOCATE('-',Narrative)) Narr, ChequeNo, SuppReference
		FROM GLTrans
		INNER JOIN ChartMaster ON ChartMaster.AccountCode=GLTrans.Account
		INNER JOIN SuppTrans ON SuppTrans.Type=GLTrans.Type AND SuppTrans.TransNo=" . $myrow['TransNo'] . '
		WHERE GLTrans.Amount > 0 AND GLTrans.TypeNo = SuppTrans.TransNo AND GLTrans.Type=' . $myrow['Type'];

    $GLTransResult = DB_query($sql, $db, '', '', false, false);
    if ( DB_error_no($db) != 0 ) {
        $title = _('Payment Listing');
        include('includes/header.inc');
        prnMsg(_('An error occurred getting the GL transactions'), 'error');
        if ( $debug == 1 ) {
            prnMsg(_('The SQL used to get the receipt header information that failed was') . ':<BR>' . $sql, 'error');
        }
        include('includes/footer.inc');
        exit;
    }
    $LeftOvers = $sql;
//	while ( strlen($LeftOvers) > 1) {
//		$LeftOvers = $pdf->addTextWrap($Left_Margin+150,$YPos,300,$FontSize,$LeftOvers);
//		$YPos -= ($line_height);
//	}
    while ( $GLRow = DB_fetch_array($GLTransResult) ) {
//		$LeftOvers = $pdf->addTextWrap($Left_Margin+150,$YPos,90,$FontSize,$GLRow['AccountName'], 'left');
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 150, $YPos, 60, $FontSize, "$" . number_format($GLRow['Amount'], 2), 'right');
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 220, $YPos, 500, $FontSize, $GLRow['Narr'], 'left');
//		$YPos -= ($line_height);
        if ( $YPos - (2 * $line_height) < $Bottom_Margin ) {
            /* Then set up a new page */
            $PageNumber ++;
            include ('includes/PDFChequeListingPageHeader.inc');
        } /* end of new page header  */
    }
    DB_free_result($GLTransResult);

    $YPos -= ($line_height);
    $TotalCheques = $TotalCheques - $myrow['Amount'];

    if ( $YPos - (2 * $line_height) < $Bottom_Margin ) {
        /* Then set up a new page */
        $PageNumber ++;
        include ('includes/PDFChequeListingPageHeader.inc');
    } /* end of new page header  */
} /* end of while there are customer receipts in the batch to print */


$YPos-=$line_height;
$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 50, $FontSize, number_format($TotalCheques, 2), 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 300, $FontSize, _('TOTAL') . ' ' . $Currency . ' ' . _('CHEQUES'), 'left');


$pdfcode = $pdf->output();
$len = strlen($pdfcode);
header('Content-type: application/pdf');
header('Content-Length: ' . $len);
header('Content-Disposition: inline; filename=ChequeListing.pdf');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

$pdf->stream();

if ( $_POST['Email'] == 'Yes' ) {
    if ( file_exists($reports_dir . '/PaymentListing.pdf') ) {
        unlink($reports_dir . '/PaymentListing.pdf');
    }
    $fp = fopen($reports_dir . '/PaymentListing.pdf', 'wb');
    fwrite($fp, $pdfcode);
    fclose($fp);

    include('includes/htmlMimeMail.php');

    $mail = new htmlMimeMail();
    $attachment = $mail->getFile($reports_dir . '/PaymentListing.pdf');
    $mail->setText(_('Please find herewith payments listing from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);
    $mail->addAttachment($attachment, 'PaymentListing.pdf', 'application/pdf');
    $mail->setFrom(array("$CompanyName <" . $CompanyRecord['Email'] . '>'));

    /* $ChkListingRecipients defined in config.php */
    $result = $mail->send($ChkListingRecipients);
}
?>
