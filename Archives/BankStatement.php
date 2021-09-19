<?php

/* $Revision: 1.4 $ */

use Rialto\AccountingBundle\Entity\Period;
use Rialto\AccountingBundle\Entity\BankAccount;

$PageSecurity = 7;

include ('includes/session.inc');

$title = _('Uploaded Bank Statement');

include('includes/header.inc');
include('includes/DateFunctions.inc');


include('includes/PDFStarter_ros.inc');
$FontSize = 8;
$pdf->addinfo('Title', _('Total Stock Report'));
$pdf->addinfo('Subject', _('Stock Sheet'));
include ('includes/PDF_BankStatement.inc');
$PageNumber = 1;
$line_height = 14;
$YPos = 700;


echo '<FORM METHOD="POST" ACTION="' . $_SERVER["PHP_SELF"] . '?' . SID . '">';
echo '<CENTER><TABLE>';
$SQL = 'SELECT BankAccountName, AccountCode FROM BankAccounts';
$ErrMsg = _('The bank accounts could not be retrieved by the SQL because');
$DbgMsg = _('The SQL used to retrieve the bank acconts was');
$AccountsResults = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

echo '<TR><TD>' . _('Bank Account') . ':</TD><TD><SELECT name="BankAccount">';
if ( DB_num_rows($AccountsResults) == 0 ) {
    echo '</SELECT></TD></TR><P>' . _('Bank Accounts have not yet been defined') . '. ' . _('You must first') . "<A HREF='" . $rootpath . "/BankAccounts.php'>" . _('define the bank accounts') . '</A>' . ' ' . _('and general ledger accounts to be affected') . '.';
    include('includes/footer.inc');
    exit;
}
else {
    while ( $myrow = DB_fetch_array($AccountsResults) ) {
        /* list the bank account names */
        if ( $_POST["BankAccount"] == '' )
            $_POST["BankAccount"] = 10200;
        if ( $_POST["BankAccount"] == $myrow["AccountCode"] ) {
            echo '<OPTION SELECTED VALUE="' . $myrow["AccountCode"] . '">' . $myrow["BankAccountName"];
        }
        else {
            echo '<OPTION VALUE="' . $myrow["AccountCode"] . '">' . $myrow["BankAccountName"];
        }
    }
    echo '</SELECT></TD></TR>';
}

/* Show a form to allow input of criteria for TB to show */
if ( ! isset($_POST['TransMonth']) ) {
    $_POST['TransMonth'] = substr(LastDateInThisPeriod($db), 0, 7);
}

echo '<CENTER><TR><TD>' . _('Select the balance date') . ":</TD><TD><SELECT Name='TransMonth'>";
$sql = 'SELECT PeriodNo, LastDate_In_Period FROM Periods ORDER BY LastDate_In_Period DESC';
$Periods = DB_query($sql, $db);
while ( $myrow = DB_fetch_array($Periods, $db) ) {
    if ( $_POST['TransMonth'] == substr($myrow['LastDate_In_Period'], 0, 7) ) {
        echo '<OPTION SELECTED VALUE=' . substr($myrow['LastDate_In_Period'], 0, 7) . '>' . ConvertSQLDate($myrow['LastDate_In_Period']);
    }
    else {
        echo '<OPTION VALUE=' . substr($myrow['LastDate_In_Period'], 0, 7) . '>' . ConvertSQLDate($myrow['LastDate_In_Period']);
    }
}
echo '</SELECT></TD></TR>';

/* Now do the posting while the user is thinking about the bank account to select */

include ('includes/GLPostings.inc');

echo '</TABLE><P><INPUT TYPE=SUBMIT Name="ShowRec" Value="' . _('Show bank reconciliation statement') . '"></CENTER>';


if ( isset($_POST['ShowRec']) AND $_POST['ShowRec'] != '' ) {

    /* Get the balance of the bank account concerned */

    $sql = "SELECT Max(Period) FROM ChartDetails WHERE AccountCode=" . $_POST["BankAccount"];
    $PrdResult = DB_query($sql, $db);
    $myrow = DB_fetch_row($PrdResult);
    $LastPeriod = $myrow[0];

    $SQL = "SELECT BFwd+Actual AS Balance FROM ChartDetails WHERE Period=$LastPeriod AND AccountCode=" . $_POST["BankAccount"];
    $ErrMsg = _('The bank account balance could not be returned by the SQL because');
    $myrow = DB_fetch_row(DB_query($SQL, $db, $ErrMsg));
    $Balance = $myrow[0];

    echo '<CENTER><TABLE WIDTH=100%>';
    echo "<COLGROUP><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='2*'><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='1*'>";

    $SQL = " SELECT * FROM BankStatements WHERE Left(BankPostDate,7) LIKE '" . $_POST['TransMonth'] . "' ORDER BY " .
//		" SIGN(Amount), " .
        " LEFT(BankDescription,4), " .
        " BankPostDate ASC ";
    $ErrMsg = _('The deposits could not be retrieved by the SQL because');
    $UPChequesResult = DB_query($SQL, $db, $ErrMsg);
    $TableHeader = '<TR>
                        <TD class="tableheader">' . _('Bank Date') . '</TD>
                        <TD class="tableheader">' . _('Type') . '</TD>
                        <TD class="tableheader">' . _('BankRef') . '</TD>
                        <TD class="tableheader">' . _('CustRef') . '</TD>
                        <TD class="tableheader">' . _('Bank Amount') . '</TD>
                        <TD class="tableheader">' . _('Linked') . '</TD>
                        <TD class="tableheader">' . _('Linked Amount') . '</TD>
                        </TR>';


    $k = 0;   //row colour counter
    $TotalUnclearedDeposits = 0;
    while ( $myrow = DB_fetch_array($UPChequesResult) ) {
        if ( substr($lastType, 0, 3) != substr($myrow["BankDescription"], 0, 3) ) {
            echo $TableHeader;
            $pdf->line($Left_Margin, $YPos - 4, $Left_Margin + 550, $YPos - 4);
            $YPos -= 18;
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 10, $YPos, 50, $FontSize, "PostDate");
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 50, $FontSize, "Type");
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 110, $YPos, 50, $FontSize, "BankRef");
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 160, $YPos, 50, $FontSize, "CustRef");
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 410, $YPos, 50, $FontSize, "Amount");
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 460, $YPos, 50, $FontSize, "TransID");
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 510, $YPos, 50, $FontSize, "Amount");
            $pdf->line($Left_Margin, $YPos - 4, $Left_Margin + 550, $YPos - 4);
            $YPos -= 12;
            $lastType = $myrow["BankDescription"];
        }

        $btsum = '';
        $btlist = '';

        if ( ($myrow["BankTransID"] != '0') && ($myrow["BankTransID"] != '') ) {
            $btsql = "SELECT group_concat(transactionID), SUM(amountCleared)
                FROM BankStatementMatch
                WHERE statementID = ". $myrow["BankStatementID"] ."
                GROUP BY statementID";
            $btres = DB_query($btsql, $db);
            if ( DB_num_rows($btres) == 1 ) {
                $btres = DB_fetch_row($btres);
                $btlist = $btres[0];
                $btsum = $btres[1];
            }
        }
        if ( $btlist != '' ) {
            if ( $k == 1 ) {
                echo "<tr bgcolor='#DDDDDD'>";
                $k = 0;
            }
            else {
                echo "<tr bgcolor='#EEEEEE'>";
                $k ++;
            }
        }
        else {
            echo "<tr bgcolor='#FF8888'>";
        }
        printf("<td>%s</td>
			<td>%s</td>
                        <td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%01.2f</td>
                        <td ALIGN=RIGHT>%s</td>
                        <td ALIGN=RIGHT>%s</td>
			</tr>", ConvertSQLDate($myrow["BankPostDate"]), $myrow["BankDescription"], $myrow["BankRef"], $myrow["CustRef"], $myrow["Amount"], $btlist, $btsum
        );

        $YPos -=13;
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 0, $YPos, 50, $FontSize, ConvertSQLDate($myrow["BankPostDate"]));
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 50, $YPos, 60, $FontSize, $myrow["BankDescription"]);
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 110, $YPos, 50, $FontSize, $myrow["BankRef"]);
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 160, $YPos, 50, $FontSize, $myrow["CustRef"]);
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 210, $YPos, 200, $FontSize, $myrow["BankText"]);
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 410, $YPos, 45, $FontSize, number_format($myrow["Amount"], 2), 'right');
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 460, $YPos, 50, $FontSize, ($myrow["BankTransID"] != '0') ? $myrow["BankTransID"] : '' );
//              $LeftOvers = $pdf->addTextWrap( $Left_Margin+510,$YPos, 50,$FontSize,($myrow["BankTransID"]!='Z')?number_format($myrow['Amount'],2):'','right');
        if ( $YPos < 50 ) {
            $PageNumber+=1;
            include ('includes/PDF_BankStatement.inc');
            $line_height = 14;
            $YPos = 700;
        }
    }
    //end of while loop
    $banks_dir = 'reports/bank_statements/';
    $buf = $pdf->output();
    $len = strlen($buf);
    $SaveAs = 'SVB' . $TransMonth;
    $pdfcode = $buf;
    $fp = fopen($banks_dir . $SaveAs . '.pdf', 'wb');
    fwrite($fp, $pdfcode);
    fclose($fp);

    echo "<TR><TD COLSPAN=6><CENTER><A HREF='" . $rootpath . $banks_dir . $SaveAs . ".pdf'>Show PDF Report</A></TD></TR>";
    echo '</TABLE>';
}
echo '</form>';
include('includes/footer.inc');
?>
