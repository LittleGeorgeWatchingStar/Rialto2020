<?php

/* Format a check or checks for printing
  List of checks passed in as $_POST['BankAccount'] and $_POST['CheckNumbers'] as a string of comma-separated check numbers */

use Rialto\UtilBundle\Fonts;

$PageSecurity = 3;

require_once 'includes/session.inc';
include('includes/SQL_CommonFunctions.inc');

if ( ! isset($_POST['AccountCode']) OR ! isset($_POST['CheckNumbers']) ) {
    $title = _('Select customer refund checks to print');
    include ('includes/header.inc');

    echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '>';
    echo '<CENTER><TABLE>';
    echo '<TR><TD>' . _('Enter the check numbers to print, comma separated') . ":</TD>
     		<TD><INPUT TYPE=text NAME='CheckNumbers' MAXLENGTH=100 SIZE=100 VALUE='XXXXX,YYYYY,ZZZZZ,....'></TD>
	</TR>";
    echo '<TR><TD>' . _('Bank Account') . '</TD><TD>';

    $sql = "SELECT BankAccountName, AccountCode FROM BankAccounts WHERE AccountCode='10200' order by 2";
    $result = DB_query($sql, $db);

    echo "<SELECT NAME='AccountCode'>";
    $first_time = 0;
    while ( $myrow = DB_fetch_array($result) ) {
        echo '<OPTION ' . ($first_time ++ ? '' : 'SELECTED ') . 'VALUE=' . $myrow['AccountCode'] . '>' . $myrow['BankAccountName'] . '</OPTION>';
    }


    echo '</SELECT></TD></TR>';

    echo "</TABLE><BR><INPUT TYPE=SUBMIT NAME='Go' VALUE='" . _('The list') . "'>          ";
    echo '<INPUT TYPE=Submit Name="CheckNumberData" Value="' . _('All unprinted checks') . '"></CENTER>';

    include('includes/footer.inc');
    exit;
}

if ( isset($_POST['CheckNumberData']) ) {
    $SQL = " SELECT TransNo, ChequeNo FROM BankTrans
		WHERE Printed = 0
		AND BankTrans.BankAct=" . $_POST['AccountCode'] . "
	        AND BankTrans.Type=101 ";
    $Result = DB_query($SQL, $db, '', '', false, false);
    if ( DB_error_no($db) != 0 ) {
        $title = _('Check Printing');
        include('includes/header.inc');
        prnMsg(_('An error occurred getting the payments'), 'error');
        if ( $Debug == 1 ) {
            prnMsg(_('The SQL used to get the check information that failed was') . ':<BR>' . $SQL, 'error');
        }
        include('includes/footer.inc');
        exit;
    }
    $myrow = DB_fetch_array($Result);
    $_POST['CheckNumbers'] = $myrow['ChequeNo'];
    while ( $myrow = DB_fetch_array($Result) ) {
        $_POST['CheckNumbers'] .= ', ' . $myrow['ChequeNo'];
    }
}
if ( $_POST['CheckNumbers'] != "" ) {
    $SQL = "SELECT ABS(Amount) CheckAmount,
		BrName SuppName,
		BrAddr1 PaymentAddr1,
		BrAddr2 PaymentAddr2,
		concat(concat(concat(concat(BrCity, ', '), BrState), ' '), BrZip) CityStateZip,
		BrCountry,
		TransDate,
		TransNo,
        ChequeNo
	FROM BankTrans JOIN CustBranch
    ON BankTrans.Ref = Concat(CustBranch.DebtorNo,', ',CustBranch.BranchCode)
	WHERE BankTrans.BankAct=" . $_POST['AccountCode'] . "
	AND BankTrans.Type=101
	AND ChequeNo IN (" . $_POST['CheckNumbers'] . ")";

    $Result = DB_query($SQL, $db, '', '', false, false);
    if ( DB_error_no($db) != 0 ) {
        $title = _('Customer Refund Check Printing');
        include('includes/header.inc');
        prnMsg(_('An error occurred getting the payments'), 'error');
        if ( $Debug == 1 ) {
            prnMsg(_('The SQL used to get the check information that failed was') . ':<BR>' . $SQL, 'error');
        }
        include('includes/footer.inc');
        exit;
    }
    elseif ( DB_num_rows($Result) == 0 ) {
        $title = _('Check Printing');
        include('includes/header.inc');
        prnMsg(_('There were no bank transactions found in the database for account') . $BankAccountName . _('matching check numbers:') . ' ' . $_POST['CheckNumbers'] . '. ' . _('Please try again selecting a different account or set of check numbers'), 'error');
        include('includes/footer.inc');
        exit;
    }

    $updateSQL = "UPDATE BankTrans Set Printed= 1
		WHERE BankTrans.BankAct=" . $_POST['AccountCode'] . "
		AND BankTrans.Type=101
		AND ChequeNo IN (" . $_POST['CheckNumbers'] . ")";

    $updateResult = DB_query($updateSQL, $db, '', '', false, false);
    if ( DB_error_no($db) != 0 ) {
        $title = _('Customer Refund Check Printing');
        include('includes/header.inc');
        prnMsg(_('An error occurred getting the payments'), 'error');
        if ( $Debug == 1 ) {
            prnMsg(_('The SQL used to get the check information that failed was') . ':<BR>' . $SQL, 'error');
        }
        include('includes/footer.inc');
        exit;
    }
    elseif ( DB_num_rows($Result) == 0 ) {
        $title = _('Customer Refund Check Printing');
        include('includes/header.inc');
        prnMsg(_('No bank transactions needed to toggle to printed.'));
    }

    include('includes/PDFStarter_ros.inc');

    /* PDFStarter_ros.inc has all the variables for page size and width set up depending on the users default preferences for paper size */

    $pdf->addinfo('Title', _('Customer Refund Cheque Printing'));
    $pdf->addinfo('Subject', _('Customer Refund Cheque printing for') . " $BankAccountName " . _('numbers') . ' ' . $_POST['CheckNumbers']);
    $baseline = 0;
    $line_1 = 792 - 65;
    $line_2 = 792 - 104;
    $line_3 = 792 - 127;
    $line_4 = 792 - 157;
    $line_1 = 792 - 65 - 10;
    $line_2 = 792 - 108 - 10;
    $line_3 = 792 - 129 - 10;
    $line_4 = 792 - 159 - 10;
    $line_5 = 792 - 207 - 10;
    $line_spacing = 10;
    $tab_0 = 20;
    $tab_1 = 72;
    $tab_2 = 100;
    $tab_3 = 490;
    $tab_4 = 512;

    $page_num = 0;
    while ( $myrow = DB_fetch_array($Result) ) {
        if ( $page_num ++ != 0 )
            $pdf->newPage();
        include('includes/PDFCheckStarter.inc');
        $baseline = 0;
        $line_1 = 792 - 65;
        $line_2 = 792 - 104;
        $line_3 = 792 - 127;
        $line_4 = 792 - 157;
        $line_1 = 792 - 65 - 10;
        $line_2 = 792 - 108 - 10;
        $line_3 = 792 - 129 - 10;
        $line_4 = 792 - 159 - 10;
        $line_5 = 792 - 207 - 10;
        $line_spacing = 10;
        $tab_0 = 20;
        $tab_1 = 72;
        $tab_2 = 100;
        $tab_3 = 490;
        $tab_4 = 512;

        $pdf->selectFont(Fonts::find('Times-Bold'));
        $fontSize = 16;
        $pdf->addText($tab_4, $line_2, $fontSize, '$ ' . number_format($myrow['CheckAmount'], 2));
        $pdf->addText($tab_2 + 20, $line_2, $fontSize, $myrow['SuppName']);
        $pdf->addText($tab_0, $line_3, $fontSize, numtotext($myrow['CheckAmount']));
        $pdf->selectFont(Fonts::find('Times-Roman'));
        $fontSize -= 5;
        $pdf->addText($tab_3, $line_1, $fontSize, $myrow['TransDate']);
        $pdf->addText($tab_1, $line_4, $fontSize, $myrow['SuppName']);
        $pdf->addText($tab_1, $line_4 - $line_spacing * 1, $fontSize, $myrow['PaymentAddr1']);
        $pdf->addText($tab_1, $line_4 - $line_spacing * 2, $fontSize, $myrow['PaymentAddr2']);
        $pdf->addText($tab_1, $line_4 - $line_spacing * 3, $fontSize, $myrow['CityStateZip']);
        $pdf->addText($tab_1, $line_4 - $line_spacing * 4, $fontSize, $myrow['BrCountry']);

        $pdf->line($tab_2 + 20, $line_2 - 2, $tab_4, $line_2 - 2);
        $pdf->line($tab_3, $line_1 - 2, $tab_4 + 30, $line_1 - 2);
        $pdf->line($tab_0, $line_3 - 2, $tab_4, $line_3 - 2);
        $pdf->line($tab_2 + 220, $line_5 + 12, $tab_4, $line_5 + 12);

        $pdf->addText(380, 792 - 210 - 4, $fontSize, 'W. Gordon Kruberg, M.D.'); // endorser

        $myYPos = -5;

        $pdf->addText($tab_1, $line_1 - 600, $fontSize, 'Refund cheque for ' . $myrow['SuppName']);
        $pdf->addText($tab_1, $line_1 - 590, $fontSize, 'Cheque #' . $myrow['ChequeNo']);
        $pdf->addText($tab_1, $line_1 - 580, $fontSize, 'Amount $' . $myrow['CheckAmount']);

        $pdf->addText($tab_1, $line_1 - 300, $fontSize, 'Refund cheque for ' . $myrow['SuppName']);
        $pdf->addText($tab_1, $line_1 - 290, $fontSize, 'Cheque #' . $myrow['ChequeNo']);
        $pdf->addText($tab_1, $line_1 - 280, $fontSize, 'Amount $' . $myrow['CheckAmount']);

        $pdf->selectFont(Fonts::find('Helvetica'));
        $pdf->addText(500, 800 - 060, $fontSize + 3, $myrow['ChequeNo']);

        $pdf->selectFont(Fonts::find('GnuMICR-0.30/GnuMICR'));
        $micr_line_1 = 792 - 237;
        $micr_line_2 = 792 - 245;
        $micr_line_3 = 792 - 245;

        $micr_tab_1 = 192;
        $micr_tab_2 = 250;
        $micr_tab_3 = 400;

        $micr_spacing = 9.0; //8.95;
        $micr_font_size = 12;

        $micr_text_1 = "1A121140399A" . $myrow['ChequeNo'] . "D3300417704C";
        $micr_text_2 = "";
        $micr_text_3 = "";
        for ( $ms = 1; $ms < 4; $ms ++  ) {
            for ( $dig = 0; $dig < strlen(${"micr_text_" . $ms}); $dig ++  ) {
                $pdf->addText(${"micr_tab_" . $ms} + $micr_spacing * $dig, ${"micr_line_" . $ms}, $micr_font_size, substr(${"micr_text_" . $ms}, $dig, 1));
            }
        }

        $pdf->selectFont(Fonts::find('Helvetica'));

        $pdf->addText(400, 800 - 600, $fontSize, 'CK#' . $myrow['ChequeNo']);
        $pdf->addText(480, 800 - 300, $fontSize, '$' . number_format($myrow['CheckAmount'], 2), 'right');
        $pdf->addText(480, 800 - 600, $fontSize, '$' . number_format($myrow['CheckAmount'], 2), 'right');
    }

    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    $pdf->stream(array('Content-Disposition' => 'CustomerRefundCheckPrintout.pdf'));
}
else {
    $title = _('Select customer refund checks to print');
    include ('includes/header.inc');
    echo "No cheques to print";
    include ('includes/footer.inc');
}
?>
