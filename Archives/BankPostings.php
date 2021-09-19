<?php

/* $Revision: 1.4 $ */

use Rialto\AccountingBundle\Entity\Period;use Rialto\AccountingBundle\Entity\BankAccount;$PageSecurity = 7;

include ('includes/session.inc');

$title = _('Bank Transactions');

include('includes/header.inc');
include('includes/DateFunctions.inc');
include_once('includes/CommonGumstix.inc');


include('includes/PDFStarter_ros.inc');
$FontSize = 9;
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
    $BalanceResult = DB_query($SQL, $db, $ErrMsg);

    $myrow = DB_fetch_row($BalanceResult);
    $Balance = $myrow[0];

    echo '<CENTER><TABLE><TR><TD COLSPAN=6><B>' . _('Current bank account balance as at') . ' ' . Date($DefaultDateFormat) . '</B></TD><TD VALIGN=BOTTOM ALIGN=RIGHT><B>' . number_format($Balance, 2) . '</B></TD></TR>';

    $SQL = "SELECT Amount/ExRate As Amt,
			AmountCleared,
			(Amount/ExRate)-AmountCleared AS Outstanding,
			Ref,
			TransDate,
			BankTrans.Type,
			SysTypes.TypeName,
			TransNo,
            ChequeNo
		FROM BankTrans
		INNER JOIN SysTypes ON BankTrans.Type = SysTypes.TypeID
		WHERE BankTrans.BankAct=" . $_POST["BankAccount"] . "
			AND Amount < 0
			AND Left(TransDate,7) LIKE '" . $_POST['TransMonth'] . "'
		ORDER BY ChequeNo ASC ";
    echo "<TR><CENTER><TD COLSPAN=7><B>Statement for " . $_POST['TransMonth'] . "</B></TD></TR>";
    echo '<TR></TR>'; /* Bang in a blank line */

    $ErrMsg = _('The unpresented cheques could not be retrieved by the SQL because');
    $UPChequesResult = DB_query($SQL, $db, $ErrMsg);

    echo '<TR><TD COLSPAN=6><B>' . _('Cheques') . ':</B></TD></TR>';

    $TableHeader = '<TR>
			<TD class="tableheader">' . _('Date') . '</TD>
			<TD class="tableheader">' . _('Type') . '</TD>
			<TD class="tableheader">' . _('Number') . '</TD>
			<TD class="tableheader">' . _('Reference') . '</TD>
			<TD class="tableheader">' . _('Orig Amount') . '</TD>
			<TD class="tableheader">' . _('Outstanding') . '</TD>
			</TR>';
    echo $TableHeader;
    $j = 1;
    $k = 0; //row colour counter
    $TotalUnpresentedCheques = 0;

    while ( $myrow = DB_fetch_array($UPChequesResult) ) {
        if ( $k == 1 ) {
            echo "<tr bgcolor='#CCCCCC'>";
            $k = 0;
        }
        else {
            echo "<tr bgcolor='#EEEEEE'>";
            $k ++;
        }
        if ( $myrow['Type'] == 22 && ! empty($myrow['ChequeNo']) ) {
            $thisLink = '<A  target="_blank" HREF="' .
                $rootpath . '/GLTransCorrection.php?' . SID .
                'TypeID=' . $myrow['Type'] .
                '&TransNo=' . $myrow['TransNo'] .
                '&CheckNum=' . $myrow['ChequeNo'] .
                '">CK# ' . $myrow['ChequeNo'] . "</A>";
        }
        else {
            $thisLink = '<A  target="_blank" HREF="' .
                $rootpath . '/GLTransCorrection.php?' . SID .
                'TypeID=' . $myrow['Type'] .
                '&TransNo=' . $myrow['TransNo'] .
                '">' . $myrow['TransNo'] . "</A>";
        }
        printf("<td>%s</td>
		        <td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%01.2f</td>
			<td ALIGN=RIGHT>%01.2f</td>
			</tr>", ConvertSQLDate($myrow['TransDate']), $myrow['TypeName'], $thisLink, $myrow['Ref'], $myrow['Amt'], $myrow['Outstanding']);

        $YPos -=13;
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 100, $YPos, 50, $FontSize, ConvertSQLDate($myrow["TransDate"]));
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 150, $YPos, 50, $FontSize, $myrow["TypeName"]);
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 200, $YPos, 50, $FontSize, $myrow["TransNo"]);
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 250, $YPos, 150, $FontSize, $myrow["Ref"]);
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 400, $YPos, 50, $FontSize, number_format($myrow["Amt"], 2), 'right');

        $TotalUnpresentedCheques +=$myrow['Outstanding'];

        $j ++;
        If ( $j == 18 ) {
            $j = 1;
            echo $TableHeader;
        }
    }
    //end of while loop
    echo '<TR></TR><TR><TD COLSPAN=6>' . _('Total of all cheques') . '</TD><TD ALIGN=RIGHT>' . number_format($TotalUnpresentedCheques, 2) . '</TD></TR>';
    $LeftOvers = $pdf->addTextWrap($Left_Margin + 100, $YPos-=15, 250, $FontSize + 1, "Total of all cheques: ");
    $LeftOvers = $pdf->addTextWrap($Left_Margin + 420, $YPos, 100, $FontSize + 1, number_format($TotalUnpresentedCheques, 2), 'right');
    $YPos-=15;

    $SQL = "SELECT Amount/ExRate As Amt,
			AmountCleared,
			(Amount/ExRate)-AmountCleared AS Outstanding,
			Ref,
			TransDate,
			BankTrans.Type,
			SysTypes.TypeName,
			TransNo
		FROM BankTrans,
			SysTypes
		WHERE BankTrans.Type = SysTypes.TypeID
		AND BankTrans.BankAct=" . $_POST["BankAccount"] . "
		AND Amount > 0
                AND Left(TransDate,7) LIKE '" . $_POST['TransMonth'] . "'
                ORDER BY TransNo ASC ";

    echo '<TR></TR>'; /* Bang in a blank line */

    $ErrMsg = _('The deposits could not be retrieved by the SQL because');

    $UPChequesResult = DB_query($SQL, $db, $ErrMsg);

    echo '<TR><TD COLSPAN=6><B>' . _('Less deposits') . ':</B></TD></TR>';

    $TableHeader = '<TR>
			<TD class="tableheader">' . _('Date') . '</TD>
			<TD class="tableheader">' . _('Type') . '</TD>
			<TD class="tableheader">' . _('Number') . '</TD>
			<TD class="tableheader">' . _('Reference') . '</TD>
			<TD class="tableheader">' . _('Orig Amount') . '</TD>
			<TD class="tableheader">' . _('Outstanding') . '</TD>
			</TR>';

    echo '<TR>' . $TableHeader;

    $j = 1;
    $k = 0;   //row colour counter
    $TotalUnclearedDeposits = 0;

    while ( $myrow = DB_fetch_array($UPChequesResult) ) {
        if ( $k == 1 ) {
            echo "<tr bgcolor='#CCCCCC'>";
            $k = 0;
        }
        else {
            echo "<tr bgcolor='#EEEEEE'>";
            $k ++;
        }
        $thisLink = '<A target="_blank" HREF="' .
            $rootpath . '/GLTransCorrection.php?' . SID .
            'TypeID=' . $myrow['Type'] .
            '&TransNo=' . $myrow['TransNo'] .
            '">' . $myrow['TransNo'] . "</A>";

        printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%01.2f</td>
			<td ALIGN=RIGHT>%01.2f</td>
			</tr>", ConvertSQLDate($myrow["TransDate"]), $myrow["TypeName"],
//			$myrow["TransNo"],
            $thisLink, $myrow["Ref"], $myrow["Amt"], $myrow["Outstanding"]
        );

        $YPos -=13;
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 100, $YPos, 50, $FontSize, ConvertSQLDate($myrow["TransDate"]));
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 150, $YPos, 50, $FontSize, $myrow["TypeName"]);
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 200, $YPos, 50, $FontSize, $myrow["TransNo"]);
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 250, $YPos, 150, $FontSize, $myrow["Ref"]);
        $LeftOvers = $pdf->addTextWrap($Left_Margin + 400, $YPos, 50, $FontSize, number_format($myrow["Amt"], 2), 'right');

        $TotalUnclearedDeposits +=$myrow["Outstanding"];

        $j ++;
        If ( $j == 18 ) {
            $j = 1;
            echo $TableHeader;
        }
        if ( $YPos < 50 ) {
            $PageNumber+=1;
            include ('includes/PDF_BankStatement.inc');
            $line_height = 14;
            $YPos = 700;
        }
    }
    //end of while loop
    $LeftOvers = $pdf->addTextWrap($Left_Margin + 100, $YPos-=15, 250, $FontSize + 1, "Total of all deposits: ");
    $LeftOvers = $pdf->addTextWrap($Left_Margin + 420, $YPos, 100, $FontSize + 1, number_format($TotalUnclearedDeposits, 2), 'right');
    $YPos-=15;
    $LeftOvers = $pdf->addTextWrap($Left_Margin + 100, $YPos-=15, 250, $FontSize + 1, "Bank statement should be: ");
    $LeftOvers = $pdf->addTextWrap($Left_Margin + 420, $YPos, 100, $FontSize + 1, number_format($Balance - $TotalUnclearedDeposits, 2), 'right');
    $YPos-=15;
    echo '<TR></TR><TR><TD COLSPAN=6>' . _('Total of all deposits') . '</TD><TD ALIGN=RIGHT>' . number_format($TotalUnclearedDeposits, 2) . '</TD></TR>';
    echo '<TR></TR><TR><TD COLSPAN=6><B>' . _('Bank statement balance should be') . '</B></TD><TD ALIGN=RIGHT>' . number_format(($Balance - $TotalUnpresentedCheques - $TotalUnclearedDeposits), 2) . '</TD></TR>';


    $banks_dir = 'reports/bank_statements/';
    $buf = $pdf->output();
    $len = strlen($buf);
    $SaveAs = 'SVB' . $TransMonth;
    $pdfcode = $buf;
    $fp = fopen($banks_dir . $SaveAs . '.pdf', 'wb');
    fwrite($fp, $pdfcode);
    fclose($fp);

    echo "<TR><TD COLSPAN=6><CENTER><A target='_blank' HREF='" . $rootpath . $banks_dir . $SaveAs . ".pdf'>Show PDF Report</A></TD></TR>";
    echo '</TABLE>';
}
echo '</form>';
include('includes/footer.inc');
?>
