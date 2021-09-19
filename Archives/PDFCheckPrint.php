<?php

use Rialto\AccountingBundle\Entity\BankTransaction;
use Rialto\UtilBundle\Fonts;

$PageSecurity = 3;

$typesToPrint = '22';

function fatalError($msg, $SQL=null)
{
    global $Debug, $DefaultCharset, $db, $rootpath, $SecurityGroups, $DefaultDateFormat;
    $title = _('Check Printing');
    include('includes/header.inc');
    prnMsg($msg, 'error');
    if ( $Debug == 1 && $SQL ) {
        prnMsg(
            _('The SQL used to get the check information that failed was') .
            ':<BR>' .
            $SQL,
            'error'
        );
    }
    include('includes/footer.inc');
    exit;
}

function getAllUnprintedCheckNumbers($accountCode)
{
    global $db, $typesToPrint;
    $SQL = " SELECT ChequeNo FROM BankTrans
		WHERE Printed = 0
        AND ChequeNo > 0
        AND BankTransType = '". BankTransaction::TYPE_CHEQUE ."'
		AND BankTrans.BankAct=" . $accountCode . "
        AND BankTrans.Type in ($typesToPrint) ";
    $Result = DB_query($SQL, $db, '', '', false, false);
    if ( DB_error_no($db) != 0 ) {

        fatalError('An error occurred getting the payments', $SQL);
    }
    $checkNumbers = array();
    while ( $myrow = DB_fetch_array($Result) ) {
        $checkNumbers[] = $myrow['ChequeNo'];
    }
    return $checkNumbers;
}

/* Format a check or checks for printing
  List of checks passed in as $_POST['BankAccount'] and $_POST['CheckNumbers'] as a string of comma-separated check numbers */

require_once 'includes/session.inc';

include('includes/SQL_CommonFunctions.inc');

if ( ! isset($_POST['AccountCode']) OR ! isset($_POST['CheckNumbers']) )
{
    $title = _('Select checks to print');
    include ('includes/header.inc');

    echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '>';
    echo '<CENTER><TABLE>';
    echo '<TR><TD>' . _('Check numbers to print, comma separated') . ":</TD>
     	<TD><INPUT TYPE=text NAME='CheckNumbers' MAXLENGTH=100 SIZE=100 VALUE='XXXXX,YYYYY,ZZZZZ,....'></TD>
	</TR>";
    echo '<TR><TD>Upswing</TD><TD><INPUT TYPE=text NAME="Upswing" MAXLENGTH=5 SIZE=5 VALUE="3"></TD></TR>';
    echo '<TR><TD>Rightswing</TD><TD><INPUT TYPE=text NAME="Rightswing" MAXLENGTH=5 SIZE=5 VALUE="0"></TD></TR>';
    echo '<TR><TD>' . _('Bank Account') . '</TD><TD>';

    $sql = 'SELECT BankAccountName, AccountCode FROM BankAccounts order by 2';
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

$sql = 'SELECT BankAccountName, AccountCode FROM BankAccounts WHERE AccountCode="' . $_POST['AccountCode'] . '"';
$dbr = DB_query($sql, $db);
$result = DB_fetch_array($dbr);
$BankAccountName = $result['BankAccountName'];

if ( isset($_POST['CheckNumberData']) ) {
    $checkNumbers = getAllUnprintedCheckNumbers($_POST['AccountCode']);
    if ( empty($checkNumbers) ) {
        fatalError('No unprinted checks found.');
    }
    $_POST['CheckNumbers'] = implode(', ', $checkNumbers);
}

$SQL = "SELECT ABS(bt.Amount) CheckAmount,
		supp.SuppName,
		supp.PaymentAddr1,
		supp.PaymentAddr2,
		concat(supp.PaymentCity, ', ', supp.PaymentState, ' ', supp.PaymentZip)
        AS CityStateZip,
		supp.CustomerAccount,
		bt.TransDate,
        bt.TransNo,
        bt.Type,
		bt.ChequeNo
	FROM BankTrans bt
    JOIN SuppTrans st ON bt.Type = st.Type AND bt.TransNo = st.TransNo
    JOIN Suppliers supp on st.SupplierNo = supp.SupplierID
	WHERE bt.BankAct=" . $_POST['AccountCode'] . "
	AND bt.Type in ($typesToPrint)
	AND ChequeNo IN (" . $_POST['CheckNumbers'] . ")";

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
elseif ( DB_num_rows($Result) == 0 ) {
    $title = _('Check Printing');
    include('includes/header.inc');
    prnMsg(_('There were no bank transactions found in the database for account') . $BankAccountName . _('matching check numbers:') . ' ' . $_POST['CheckNumbers'] . '. ' . _('Please try again selecting a different account or set of check numbers'), 'error');
    include('includes/footer.inc');
    exit;
}

$updateSQL = "UPDATE BankTrans Set Printed = 1
		WHERE BankTrans.BankAct=" . $_POST['AccountCode'] . "
		AND BankTrans.Type in ($typesToPrint)
		AND ChequeNo IN (" . $_POST['CheckNumbers'] . ")";

$updateResult = DB_query($updateSQL, $db, '', '', false, false);
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
elseif ( DB_num_rows($Result) == 0 ) {
    $title = _('Check Printing');
    include('includes/header.inc');
    prnMsg(_('No bank transactions needed to toggle to printed.'));
}

include('includes/PDFStarter_ros.inc');

/* PDFStarter_ros.inc has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addinfo('Title', _('Cheque Printing'));
$pdf->addinfo('Subject', _('Cheque printing for') . " $BankAccountName" . _('numbers') . ' ' . $_POST['CheckNumbers']);
include('includes/PDFCheckStarter.inc');

$baseline = 0;
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

    if ( $page_num ++ != 0 ) {
        $pdf->newPage();
        include('includes/PDFCheckStarter.inc');
    }

    $pdf->selectFont(Fonts::find('Times-Bold'));
    $fontSize = 16;
    $pdf->addText($tab_4 - 10, $line_2, $fontSize, '$ ' . number_format($myrow['CheckAmount'], 2));
    $pdf->addText($tab_2 + 20, $line_2, $fontSize, $myrow['SuppName']);
    $pdf->addText($tab_0, $line_3, $fontSize, numtotext($myrow['CheckAmount']));
    $pdf->selectFont(Fonts::find('Times-Roman'));
    $fontSize -= 5;
    $pdf->addText($tab_3, $line_1, $fontSize, $myrow['TransDate']);
    $pdf->addText($tab_1, $line_4, $fontSize, $myrow['SuppName']);
    $pdf->addText($tab_1, $line_4 - $line_spacing * 1, $fontSize, $myrow['PaymentAddr1']);
    if ( $myrow['PaymentAddr2'] != "" ) {
        $pdf->addText($tab_1, $line_4 - $line_spacing * 2, $fontSize, $myrow['PaymentAddr2']);
        $pdf->addText($tab_1, $line_4 - $line_spacing * 3, $fontSize, $myrow['CityStateZip']);
    }
    else {
        $pdf->addText($tab_1, $line_4 - $line_spacing * 2, $fontSize, $myrow['CityStateZip']);
    }
    if ( strlen($myrow['CustomerAccount']) > 0 )
        $pdf->addText($tab_1 - 10, $line_4 - $line_spacing * 6, $fontSize - 1, 'ACCOUNT#' . $myrow['CustomerAccount']);

    $pdf->line($tab_2 + 15, $line_2 - 2, $tab_4, $line_2 - 2);
    $pdf->line($tab_3, $line_1 - 2, $tab_4 + 30, $line_1 - 2);
    $pdf->line($tab_0, $line_3 - 2, $tab_4, $line_3 - 2);
    $pdf->line($tab_2 + 230, $line_5 + 12, $tab_4, $line_5 + 12);

    $pdf->addText(380, 792 - 210 - 4, $fontSize, 'W. Gordon Kruberg, M.D.'); // endorser


    $partSQL = "SELECT ST_To.SuppReference InvoiceNo,
        SA.Amt,
        ST_From.SuppReference,
        ST_To.TranDate
        FROM SuppTrans ST_To
        INNER JOIN SuppAllocs SA ON SA.TransID_AllocTo = ST_To.ID
        INNER JOIN SuppTrans ST_From ON SA.TransID_AllocFrom = ST_From.ID
        WHERE SA.TransID_AllocFrom = ST_From.ID
        AND ST_From.TransNo='" . $myrow["TransNo"] . "'
        AND ST_From.Type = '". $myrow['Type'] ."'";

    $myYPos = 5;
    $pdf->line(170, 792 - 300 + $myYPos, $Page_Width - $Right_Margin - 10, 792 - 300 + $myYPos);
    $pdf->line(170, 792 - 600 + $myYPos, $Page_Width - $Right_Margin - 10, 792 - 600 + $myYPos);

    $myYPos -= 8;
    $pdf->addText(175, 790 - 300 + $myYPos, $fontSize, "Invoice Number");
    $pdf->addText(175, 790 - 600 + $myYPos, $fontSize, "Invoice Number");
    $pdf->addText(300, 790 - 300 + $myYPos, $fontSize, "Invoice Date");
    $pdf->addText(300, 790 - 600 + $myYPos, $fontSize, "Invoice Date");
    $pdf->addText(468, 790 - 300 + $myYPos, $fontSize, "Invoice Amount");
    $pdf->addText(468, 790 - 600 + $myYPos, $fontSize, "Invoice Amount");

    $myYPos -= 4;
    $pdf->line(170, 792 - 300 + $myYPos, $Page_Width - $Right_Margin - 10, 792 - 300 + $myYPos);
    $pdf->line(170, 792 - 600 + $myYPos, $Page_Width - $Right_Margin - 10, 792 - 600 + $myYPos);

    $myInfoResults = DB_query($partSQL, $db, '', '', false, false);
    $myYPos -= 14;
    while ( $myInfo = DB_fetch_array($myInfoResults) ) {
        $pdf->addText(175, 792 - 300 + $myYPos, $fontSize, $myInfo['InvoiceNo']);
        $pdf->addText(175, 792 - 600 + $myYPos, $fontSize, $myInfo['InvoiceNo']);
        $pdf->addText(300, 792 - 300 + $myYPos, $fontSize, $myInfo['TranDate']);
        $pdf->addText(300, 792 - 600 + $myYPos, $fontSize, $myInfo['TranDate']);
        $dummy = $pdf->addTextWrap(468, 792 - 300 + $myYPos, 50, $fontSize, number_format($myInfo['Amt'], 2), 'right');
        $dummy = $pdf->addTextWrap(468, 792 - 600 + $myYPos, 50, $fontSize, number_format($myInfo['Amt'], 2), 'right');
        $myYPos -=15;
    }

    $pdf->addText(175, 800 - 300, $fontSize, 'Account #' . $myrow['CustomerAccount']);
    $pdf->addText(175, 800 - 600, $fontSize, 'Account #' . $myrow['CustomerAccount']);
    $pdf->addText(30, 800 - 300, $fontSize, $myrow['SuppName']);
    $pdf->addText(30, 800 - 600, $fontSize, $myrow['SuppName']);
    $pdf->addText(300, 800 - 300, $fontSize, $myrow['TransDate']);
    $pdf->addText(300, 800 - 600, $fontSize, $myrow['TransDate']);

    $pdf->selectFont(Fonts::find('Helvetica'));
    $pdf->addText(500, 800 - 060, $fontSize + 3, $myrow['ChequeNo']);

    $upswing = $_POST['Upswing'];
    $rightswing = $_POST['Rightswing'];
    $pdf->selectFont(Fonts::find('GnuMICR-0.30/GnuMICR'));
    $micr_line_1 = 792 - 237 + $upswing;
    $micr_line_2 = 792 - 245 + $upswing;
    $micr_line_3 = 792 - 245 + $upswing;

    $micr_tab_1 = 192 + $rightswing;
    $micr_tab_2 = 250 + $rightswing;
    $micr_tab_3 = 400 + $rightswing;

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
$pdf->stream(array('Content-Disposition' => 'CheckPrintout.pdf'));
