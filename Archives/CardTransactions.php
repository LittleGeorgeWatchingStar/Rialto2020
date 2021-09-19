<?php

/* $Revision: 1.5 $ */

$PageSecurity = 7;

include("includes/session.inc");

$title = _('Card Transaction Posting');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

$typesToSweep = '12, 101'; // receipt, customer refund

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

if (isset($_POST['PostTransactions'])) {
    echo '<CENTER><TABLE WIDTH=30%>';
    echo "<TR><TH>Date</TH><TH COLSPAN=2>Card</TH><TH>Amount Posted</TH></TR>";
    echo "<INPUT TYPE=HIDDEN NAME='RowCounter' VALUE='" . $_POST['RowCounter'] . "'>";
    echo "<COLGROUP><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='1*'>";
    for ($Counter = 1; $Counter < $_POST['RowCounter']; $Counter++) {
        /* Update the cardTrans record to match it off */
        $thisApproval = ($_POST["Approve_" . $Counter] == True) ? 1 : 0;
        /* 	THREE TRANSACTION ENTRIES:
          DR: GLTrans -- Bank Account
          CR: GLTrans -- Authorize.net Account
          Journal BankTrans
         */
        if ($_POST["Approved_" . $Counter] != '') {
            $TransNo = GetNextTransNo(102, $db);
            $TransDate = $_POST['PostDate_' . $Counter];
            $PeriodNo = GetPeriod(ConvertSQLDate($TransDate), $db);
            $Amount = $_POST['Amount_' . $Counter];
            $Ref = "Sweep " . $_POST['CardType_' . $Counter] . " - " . $_POST['PostDate_' . $Counter];

            $sql = "INSERT INTO GLTrans (Type,TypeNo,TranDate,PeriodNo,Amount,Account,Narrative) VALUES
				(102, '$TransNo','$TransDate','$PeriodNo', '" . (-$Amount) . "', '10600', '$Ref'  )";
            $ErrMsg = _('Could not match off this payment beacause');
            $result = DB_query($sql, $db, $ErrMsg);

            $sql = "INSERT INTO GLTrans (Type,TypeNo,TranDate,PeriodNo,Amount,Account,Narrative) VALUES
				(102, '$TransNo','$TransDate','$PeriodNo', '$Amount', '10200', '$Ref'  )";
            $ErrMsg = _('Could not match off this payment beacause');
            $result = DB_query($sql, $db, $ErrMsg);

            $sql = "INSERT INTO BankTrans ( Type, TransNo, Amount, BankAct, Ref, TransDate ) VALUES( 102, '$TransNo', '$Amount', '10200' , '$Ref' , '$TransDate' )";
            $ErrMsg = _('Could not match off this payment beacause');
            $result = DB_query($sql, $db, $ErrMsg);

            if ($_POST['CardType_' . $Counter] == 'AmEx') {
                $sql = "UPDATE CardTrans SET Posted=1 WHERE CardID='AMEX' AND Type in ($typesToSweep) AND PostDate='" . $_POST['PostDate_' . $Counter] . "'";
                $ErrMsg = _('Could not match off this payment beacause');
                $result = DB_query($sql, $db, $ErrMsg);
                echo $sql . "<BR>";

                $fees = $Amount * 0.03500;
                $sql = "INSERT INTO GLTrans (Type,TypeNo,TranDate,PeriodNo,Amount,Account,Narrative) VALUES
	                                (102, '$TransNo','$TransDate','$PeriodNo', '-$fees', '10200', 'FEES: $Ref'  )";
                $ErrMsg = _('Could not match off this payment beacause');
                $result = DB_query($sql, $db, $ErrMsg);
                echo $sql;

                $sql = "INSERT INTO GLTrans (Type,TypeNo,TranDate,PeriodNo,Amount,Account,Narrative) VALUES
	                                (102, '$TransNo','$TransDate','$PeriodNo', '$fees', '21000', 'FEES: $Ref'  )";
                $ErrMsg = _('Could not match off this payment beacause');
                $result = DB_query($sql, $db, $ErrMsg);
                echo $sql;

                $sql = "INSERT INTO BankTrans ( Type, TransNo, Amount, BankAct, Ref, TransDate ) VALUES
					( 102, '$TransNo', '-$fees', '10200' , 'FEES: $Ref' , '$TransDate' )";
                $ErrMsg = _('Could not match off this payment beacause');
                $result = DB_query($sql, $db, $ErrMsg);
                echo $sql;
            }
            else {
                $sql = "UPDATE CardTrans SET Posted=1 WHERE CardID='VISA' AND Type in ($typesToSweep) AND PostDate='" . $_POST['PostDate_' . $Counter] . "'";
                $ErrMsg = _('Could not match off this payment beacause');
                $result = DB_query($sql, $db, $ErrMsg);
                echo $sql . "<BR>";
                $sql = "UPDATE CardTrans SET Posted=1 WHERE CardID='MCRD' AND Type in ($typesToSweep) AND PostDate='" . $_POST['PostDate_' . $Counter] . "'";
                $ErrMsg = _('Could not match off this payment beacause');
                $result = DB_query($sql, $db, $ErrMsg);
                echo $sql . "<BR>";
            }

            echo "<INPUT TYPE=HIDDEN NAME='Approved_$Counter' VALUE='" . $_POST['Approved_' . $Counter] . "'>";
            echo "<INPUT TYPE=HIDDEN NAME='PostDate_$Counter' VALUE='" . $_POST['PostDate_' . $Counter] . "'>";
            echo "<TR>";
            echo "<TD><CENTER>" . $_POST["PostDate_" . $Counter] . "</TD>";
            echo "<INPUT TYPE=HIDDEN NAME='CardType_$Counter' VALUE='" . $_POST['CardType_' . $Counter] . "'>";
            echo "<TD COLSPAN=2><CENTER>" . $_POST["CardType_" . $Counter] . "</TD>";
            echo "<INPUT TYPE=HIDDEN NAME='Amount_$Counter' VALUE='" . $_POST['Amount_' . $Counter] . "'>";
            echo "<TD><CENTER>" . $_POST["Amount_" . $Counter] . "</TD>";
            echo "</TR>";
        }
    }
    /* Show the updated position with the same criteria as previously entered */
    $_POST["ShowTransactions"] = True;
    echo "<TR>";
    echo "<TD COLSPAN=4><CENTER><INPUT TYPE=SUBMIT NAME='CancelTransactions' VALUE='Done'></TD>";
    echo "</TR>";
    echo '</TABLE>';
    echo "</FORM>";
    include('includes/footer.inc');
    exit;
}

if (isset($_POST['Update']) AND $_POST['RowCounter'] > 1) {
    echo '<CENTER><TABLE WIDTH=30%>';
    echo "<TR><TH>Date</TH><TH COLSPAN=2>Card</TH><TH>Amount to Post</TH></TR>";
    echo "<COLGROUP><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='1*'>";
    echo "<INPUT TYPE=HIDDEN NAME='RowCounter' VALUE='" . $_POST['RowCounter'] . "'>";
    for ($Counter = 1; $Counter < $_POST['RowCounter']; $Counter++) {
        /* Update the cardTrans record to match it off */
        $thisApproval = ($_POST["Approve_" . $Counter] == True) ? 1 : 0;
        if ($_POST["Approved_" . $Counter] != '') {
            echo "<INPUT TYPE=HIDDEN NAME='Approved_$Counter' VALUE='" . $_POST['Approved_' . $Counter] . "'>";
            echo "<INPUT TYPE=HIDDEN NAME='PostDate_$Counter' VALUE='" . $_POST['PostDate_' . $Counter] . "'>";
            echo "<TR>";
            echo "<TD><CENTER>" . $_POST["PostDate_" . $Counter] . "</TD>";
            echo "<INPUT TYPE=HIDDEN NAME='CardType_$Counter' VALUE='" . $_POST['CardType_' . $Counter] . "'>";
            echo "<TD COLSPAN=2><CENTER>" . $_POST["CardType_" . $Counter] . "</TD>";
            echo "<INPUT TYPE=HIDDEN NAME='Amount_$Counter' VALUE='" . $_POST['Amount_' . $Counter] . "'>";
            echo "<TD><CENTER>" . $_POST["Amount_" . $Counter] . "</TD>";
            echo "</TR>";
        }
    }
    /* Show the updated position with the same criteria as previously entered */
    $_POST["ShowTransactions"] = True;
    echo "<TR>";
    echo "<TD COLSPAN=2><CENTER><INPUT TYPE=SUBMIT NAME='PostTransactions' VALUE='Post These'></TD>";
    echo "<TD COLSPAN=2><CENTER><INPUT TYPE=SUBMIT NAME='CancelTransactions' VALUE='Cancel'></TD>";
    echo "</TR>";
    echo '</TABLE>';
    echo "</FORM>";
    include('includes/footer.inc');
    exit;
}

echo "<INPUT TYPE=HIDDEN Name=Type Value=$Type>";
echo '<CENTER><TABLE WIDTH=40%>';

if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])) {
    $_POST['BeforeDate'] = Date($DefaultDateFormat);
}
if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])) {
    $_POST['AfterDate'] = Date($DefaultDateFormat, Mktime(0, 0, 0, Date("m") - 3, Date("d"), Date("y")));
}

echo "<TR>";
echo "<TD>Show transactions before<INPUT TYPE=TEXT NAME='BeforeDate' SIZE=12 MAXLENGTH=12 Value='" . $_POST['BeforeDate'] . "'></TD>";
echo "<TD>but after:<INPUT TYPE=TEXT NAME='AfterDate' SIZE=12 MAXLENGTH=12 Value='" . $_POST['AfterDate'] . "'></TD>";
echo "</TR>";

echo "<TR>";
echo "<TD><CENTER><INPUT TYPE=SUBMIT NAME='ShowTransactions' VALUE='Show selected transactions'></TD>";
echo "<TD><CENTER><INPUT TYPE=SUBMIT NAME='Update' VALUE='Post'></TD>";
echo "</TR>";
echo '</TABLE>';

$InputError = 0;
if (!Is_Date($_POST['BeforeDate'])) {
    $InputError = 1;
    prnMsg(_('The date entered for the field to show before') . ', ' . _('is not entered in a recognised date format') . '. ' . _('Entry is expected in the format') . ' ' . $DefaultDateFormat, 'error');
}
if (!Is_Date($_POST['AfterDate'])) {
    $InputError = 1;
    prnMsg(_('The date entered for the field to show after') . ', ' . _('is not entered in a recognised date format') . '. ' . _('Entry is expected in the format') . ' ' . $DefaultDateFormat, 'error');
}

$todate = date("Y-m-d");

if ($InputError != 1 AND isset($_POST["ShowTransactions"])) {

    $SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
    $SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);
    $sql = "SELECT * FROM CardTrans
		WHERE PostDate >= '" . $SQLAfterDate . "'
		AND PostDate <= '" . $SQLBeforeDate . "'
		AND Posted =0
        AND Type in ($typesToSweep)
		ORDER BY PostDate";
    $ErrMsg = _('The payments with the selected criteria could not be retrieved because');
    $PaymentsResult = DB_query($sql, $db, $ErrMsg);

    $TableHeader = ' <TR>
             <TD class="tableheader">' . _('PostDate') . '</TD>
             <TD class="tableheader">' . _('TransactionID') . '</TD>
			 <TD class="tableheader">' . _('AmEX') . '</TD>
			 <TD class="tableheader">' . _('VIMC') . '</TD>
			 <TD class="tableheader">' . _('Amount') . '</TD>
			 <TD class="tableheader">' . _('Approved') . '</TD>
			 </TR>';
    echo '<TABLE CELLPADDING=2 BORDER=2>' . $TableHeader;

    $j = 1;  //page length counter
    $k = 0; //row colour counter
    $i = 1; //no of rows counter
    $previousPostDate = -1;

    while ($myrow = DB_fetch_array($PaymentsResult)) {
        if ($previousPostDate == -1) {
            $previousPostDate = $myrow['PostDate'];
            $PostDateRunningPayTotal = 0;
            $PostDateRunningApprovedTotal = 0;
            $grandApprovedTotal = 0;
            $grandPayTotal = 0;
        }

        if ($myrow['PostDate'] == $previousPostDate) {
            if ($myrow['Approved'] == 1) {
                $PostDateRunningApprovedTotal += $myrow['Amount'];
                ${"PostDateRunningApprovedTotal" . $myrow['CardID']} +=$myrow['Amount'];
                $grandApprovedTotal += $myrow['Amount'];
                ${"grandApprovedTotal" . $myrow['CardID'] } +=$myrow['Amount'];
            }
            else {
                $PostDateRunningPayTotal += $myrow['Amount'];
                ${"PostDateRunningPayTotal" . $myrow['CardID']} +=$myrow['Amount'];
                $grandPayTotal += $myrow['Amount'];
                ${"grandPayTotal" . $myrow['CardID'] } +=$myrow['Amount'];
            }
        }
        else {
            if (${"PostDateRunningPayTotalVISA"} + ${"PostDateRunningPayTotalMCRD"} != 0) {
                printf("<td>%s</td>
                        	<td ALIGN=RIGHT>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=CENTER>%s</td>
	                        <td ALIGN=CENTER><INPUT TYPE='checkbox' NAME='Approved_%s' VALUE=1 %s>
                                         <INPUT TYPE=HIDDEN NAME='PostDate_%s' VALUE=%s></td>
                                         <INPUT TYPE=HIDDEN NAME='CardType_%s' VALUE=%s></td>
					 <INPUT TYPE=HIDDEN NAME='Amount_%s' VALUE=%s></td>
				</tr>", $previousPostDate, 'Visa & Mastercard', '', number_format($btamount = ${"PostDateRunningPayTotalVISA"} + ${"PostDateRunningPayTotalMCRD"}, 2), '', //	number_format($PostDateRunningPayTotal,2),
                    $i, ($_POST["Approved_" . $i] == 1 ? 'checked' : ''), $i, "'$previousPostDate'", $i, "'VIMC'", $i, "'$btamount'"
                );
                $i++;
            }
            if (${"PostDateRunningPayTotalAMEX"} != 0) {
                printf("<td>%s</td>
                        	<td ALIGN=RIGHT>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=CENTER>%s</td>
                                <td ALIGN=CENTER><INPUT TYPE='checkbox' NAME='Approved_%s' VALUE=1 %s>
                                         <INPUT TYPE=HIDDEN NAME='PostDate_%s' VALUE=%s></td>
                                         <INPUT TYPE=HIDDEN NAME='CardType_%s' VALUE=%s></td>
                                         <INPUT TYPE=HIDDEN NAME='Amount_%s' VALUE=%s></td>
				</tr>", $previousPostDate, 'American express', number_format($btamount = ${"PostDateRunningPayTotalAMEX"}, 2), '', '', //	number_format($PostDateRunningPayTotal,2),
                    $i, ($_POST["Approved_" . $i] == 1 ? 'checked' : ''), $i, "'$previousPostDate'", $i, "'AmEx'", $i, "'$btamount'"
                );
                $i++;
            }
            $previousPostDate = $myrow['PostDate'];
            if ($myrow['Approved'] == 1) {
                $PostDateRunningApprovedTotal = $myrow['Amount'];
                $grandApprovedTotal += $myrow['Amount'];
                $PostDateRunningPayTotal = 0;
                ${"PostDateRunningPayTotalAMEX"} = 0;
                ${"PostDateRunningPayTotalVISA"} = 0;
                ${"PostDateRunningPayTotalMCRD"} = 0;
                ${"PostDateRunningApprovedTotalAMEX"} = 0;
                ${"PostDateRunningApprovedTotalVISA"} = 0;
                ${"PostDateRunningApprovedTotalMCRD"} = 0;
                ${"PostDateRunningApprovedTotal" . $myrow['CardID']} +=$myrow['Amount'];
                ${"grandPayTotal" . $myrow['CardID'] } +=$myrow['Amount'];
            }
            else {
                $PostDateRunningPayTotal = $myrow['Amount'];
                $grandPayTotal += $myrow['Amount'];
                $PostDateRunningApprovedTotal = 0;
                ${"PostDateRunningPayTotalAMEX"} = 0;
                ${"PostDateRunningPayTotalVISA"} = 0;
                ${"PostDateRunningPayTotalMCRD"} = 0;
                ${"PostDateRunningApprovedTotalAMEX"} = 0;
                ${"PostDateRunningApprovedTotalVISA"} = 0;
                ${"PostDateRunningApprovedTotalMCRD"} = 0;
                ${"PostDateRunningPayTotal" . $myrow['CardID']} = $myrow['Amount'];
                ${"grandPayTotal" . $myrow['CardID'] } +=$myrow['Amount'];
            }
        }

        $DisplayTransDate = ConvertSQLDate($myrow["TransDate"]);
        if ($myrow["DueDate"] < $todate) {
            echo "<tr bgcolor='#CCCCCC'>";
        }
        else {
            echo "<tr bgcolor='#EEEEEE'>";
        }
        printf("<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
                        <td ALIGN=RIGHT>%s</td>
                        <td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			</tr>", $myrow['PostDate'], $myrow['TransactionID'], $myrow['CardID'] == 'AMEX' ? number_format($myrow['Amount'], 2) : '', $myrow['CardID'] != 'AMEX' ? number_format($myrow['Amount'], 2) : '', number_format($myrow['Amount'], 2), ''
        );

        $i++;
    }
    //end of while loop

    echo "<tr bgcolor='#EE4400'>";
    printf("<td>%s</td>
<td>%s</td>
<td ALIGN=RIGHT>%s</td>
<td ALIGN=RIGHT>%s</td>
<td ALIGN=RIGHT>%s</td>
<td ALIGN=CENTER>%s</td>
</tr>", '', 'Grand Totals', number_format(${"grandPayTotalAMEX"}, 2), number_format(${"grandPayTotalVISA"} + ${"grandPayTotalMCRD"}, 2), number_format($grandPayTotal, 2), number_format($grandApprovedTotal, 2), ''
    );

    echo "</TABLE><CENTER><INPUT TYPE=HIDDEN NAME='RowCounter' VALUE=$i>";
}

echo '</form>';
include('includes/footer.inc');
?>
