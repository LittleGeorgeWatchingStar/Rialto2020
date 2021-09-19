<?php

/* $Revision: 1.5 $ */

$PageSecurity = 7;

include("includes/session.inc");

$Type = 'Receipts';
$TypeName = _('Receipts');
$title = _('Bank Account Sweep transaction matching');

include('includes/header.inc');
include('includes/DateFunctions.inc');

if ( isset($_POST['Update']) AND $_POST['RowCounter'] > 1 ) {
    for ( $Counter = 1; $Counter <= $_POST['RowCounter']; $Counter ++  ) {
        if ( $_POST["Clear_" . $Counter] == True ) {
            /* Update the banktrans recoord to match it off */
            $sql = "UPDATE BankTrans SET AmountCleared=(Amount/ExRate)
					WHERE BankTransID IN (" . $_POST["BankTransMatches_" . $Counter] . ")";
            $ErrMsg = _('Could not match off this payment because');
            echo '1. ' . $sql . '<BR>';
            $result = DB_query($sql, $db, $ErrMsg);
            if ( isset($_POST["BankStatementID_" . $Counter]) && ($_POST["BankTransMatches_" . $Counter] != 0) ) {
                $msql = " UPDATE BankStatements " .
                    " SET    BankTransID = '" . $_POST["BankTransMatches_" . $Counter] . "'" .
                    " WHERE  BankStatementID='" . $_POST["BankStatementID_" . $Counter] . "'";
                $ErrMsg = _('Could not update the amount matched off this bank transaction because');
                echo '2. ' . $msql . '<BR>';
                $result = DB_query($msql, $db, $ErrMsg);
            }
        }
    }
    $_POST["ShowTransactions"] = True;
    $result = DB_query('COMMIT', $db);
}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo "<INPUT TYPE=HIDDEN Name=Type Value=$Type>";

echo '<TABLE BGCOLOR=RED><TR>';
echo '<TD ALIGN=RIGHT>' . _('Bank Account') . ':</TD><TD COLSPAN=3><SELECT name="BankAccount">';

$sql = "SELECT AccountCode, BankAccountName FROM BankAccounts";
$resultBankActs = DB_query($sql, $db);
while ( $myrow = DB_fetch_array($resultBankActs) ) {
    if ( $_POST["BankAccount"] == '' ) {
        $_POST["BankAccount"] = 10200;
    }
    if ( $myrow["AccountCode"] == $_POST['BankAccount'] ) {
        echo "<OPTION SELECTED Value='" . $myrow["AccountCode"] . "'>" . $myrow["BankAccountName"];
    }
    else {
        echo "<OPTION Value='" . $myrow["AccountCode"] . "'>" . $myrow["BankAccountName"];
    }
}

echo '</SELECT></TD></TR>';

if ( ! isset($_POST['BeforeDate']) OR ! Is_Date($_POST['BeforeDate']) ) {
    $_POST['BeforeDate'] = Date($DefaultDateFormat);
}
if ( ! isset($_POST['AfterDate']) OR ! Is_Date($_POST['AfterDate']) ) {
    $_POST['AfterDate'] = Date($DefaultDateFormat, Mktime(0, 0, 0, Date("m") - 3, Date("d"), Date("y")));
}

echo '<TR><TD>' . _('Show') . ' ' . $TypeName . ' ' . _('before') . ':</TD>
	<TD><INPUT TYPE=TEXT NAME="BeforeDate" SIZE=12 MAXLENGTH=12 Value="' . $_POST['BeforeDate'] . '"></TD>';
echo '<TD>' . _('but after') . ':</TD>
	<TD><INPUT TYPE=TEXT NAME="AfterDate" SIZE=12 MAXLENGTH=12 Value="' . $_POST['AfterDate'] . '"></TD></TR>';
echo '</TABLE><CENTER><INPUT TYPE=SUBMIT NAME="ShowTransactions" VALUE="' . _('Show selected') . ' ' . $TypeName . '">';
echo "<P><A HREF='$rootpath/BankReconciliation.php?" . SID . "'>" . _('Show reconciliation') . '</A>';
echo '<HR>';

$InputError = 0;
if ( ! Is_Date($_POST['BeforeDate']) ) {
    $InputError = 1;
    prnMsg(_('The date entered for the field to show') . ' ' . $TypeName . ' ' . _('before') . ', ' . _('is not entered in a recognised date format') . '. ' . _('Entry is expected in the format') . ' ' . $DefaultDateFormat, 'error');
}
if ( ! Is_Date($_POST['AfterDate']) ) {
    $InputError = 1;
    prnMsg(_('The date entered for the field to show') . ' ' . $Type . ' ' . _('after') . ', ' . _('is not entered in a recognised date format') . '. ' . _('Entry is expected in the format') . ' ' . $DefaultDateFormat, 'error');
}

if ( $InputError != 1 AND isset($_POST["BankAccount"]) AND $_POST["BankAccount"] != "" AND isset($_POST["ShowTransactions"]) ) {

    $SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
    $SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

    $sql = "SELECT BankTransID,TransNo,Ref,AmountCleared,TransDate,Amount/ExRate AS Amt,BankTransType
        FROM BankTrans
        WHERE Amount >0
        AND Ref LIKE '%Sweep %'
        AND TransDate >= '" . $SQLAfterDate . "'
        AND TransDate <= '" . $SQLBeforeDate . "'
        AND BankAct=" . $_POST["BankAccount"] . "
        AND  ABS(AmountCleared - (Amount / ExRate)) > 0.009
        ORDER BY Ref";
    $ErrMsg = _('The payments with the selected criteria could not be retrieved because');
    $PaymentsResult = DB_query($sql, $db, $ErrMsg);
    $TableHeader = ' <TR>
        <TD class="tableheader">' . _('Ref') . '</TD>
        <TD class="tableheader">' . _('Date') . '</TD>
        <TD class="tableheader">' . _('Amount') . '</TD>
        <TD class="tableheader">' . _('Outstanding') . '</TD>
        <TD class="tableheader">' . _('Yes') . '</TD>
        <TD class="tableheader">' . _('Potential match') . '</TD>
        </TR>';
    echo "<TABLE BGCOLOR='RED'>" . $TableHeader;

    $j = 1;  //page length counter
    $k = 0; //row colour counter
    $i = 1; //no of rows counter
    $last_rows_amount = 0;
    $transaction_rows = array();
    while ( $myrow = DB_fetch_array($PaymentsResult) ) {
        $transaction_rows[$i]['TransNo'] = $myrow["TransNo"];
        $transaction_rows[$i]['Amt'] = $myrow["Amt"];
        $transaction_rows[$i]['Ref'] = $myrow["Ref"];
        $transaction_rows[$i]['BankTransType'] = $myrow["BankTransType"];
        $transaction_rows[$i]['BankTransID'] = $myrow["BankTransID"];
        $transaction_rows[$i]['DisplayDate'] = ConvertSQLDate($myrow["TransDate"]);
        $transaction_rows[$i]['Outstanding'] = $myrow["Amt"] - $myrow["AmountCleared"];

        $target_amount_1 = $myrow['Amt'];
        $target_amount_2 = $myrow['Amt'] + $last_rows_amount;
        $target_amount_3 = $myrow['Amt'] + $last_last_rows_amount + $last_rows_amount;

        $last_last_rows_amount = $last_rows_amount;
        $last_rows_amount = $myrow['Amt'];

        $statementSQL_1 = " SELECT * FROM BankStatements WHERE " .
            " DATEDIFF(BankPostDate,'" . $myrow[TransDate] . "') >=  0 AND " .
            " DATEDIFF(BankPostDate,'" . $myrow[TransDate] . "') <=  6 AND " .
            " (
                (ABS(Amount-" . $target_amount_1 . ") < 0.10)
            ) ";
        $statementSQL_2 = " SELECT * FROM BankStatements WHERE " .
            " DATEDIFF(BankPostDate,'" . $myrow[TransDate] . "') >=  0 AND " .
            " DATEDIFF(BankPostDate,'" . $myrow[TransDate] . "') <=  6 AND " .
            " (
                (ABS(Amount-" . $target_amount_1 . ") < 0.10) OR
                (ABS(Amount-" . $target_amount_2 . ") < 0.10)
            ) ";
        $statementSQL_3 = " SELECT * FROM BankStatements WHERE " .
            " DATEDIFF(BankPostDate,'" . $myrow[TransDate] . "') >=  0 AND " .
            " DATEDIFF(BankPostDate,'" . $myrow[TransDate] . "') <=  6 AND " .
            " (
                (ABS(Amount-" . $target_amount_1 . ") < 0.10) OR
                (ABS(Amount-" . $target_amount_2 . ") < 0.10) OR
                (ABS(Amount-" . $target_amount_3 . ") < 0.10)
            ) ";

        if ( $statement = DB_fetch_array(DB_query($statementSQL_1, $db)) ) {
            $transaction_rows[$i]['BankStatementID'] = $statement['BankStatementID'];
            $transaction_rows[$i]['BankTransMatches'] = $transaction_rows[$i]['BankTransID'];
        }
        else {
            $transaction_rows[$i]['BankTransMatches'] = 0;
            if ( $transaction_rows[$i - 1]['BankTransMatches'] == 0 ) {
                if ( $statement = DB_fetch_array(DB_query($statementSQL_2, $db)) ) {
                    $transaction_rows[$i]['BankStatementID'] = $statement['BankStatementID'];
                    $transaction_rows[$i - 1]['BankStatementID'] = $statement['BankStatementID'];
                    $transaction_rows[$i]['BankTransMatches'] = $transaction_rows[$i]['BankTransID'] . ',' .
                        $transaction_rows[$i - 1]['BankTransID'];
                    $transaction_rows[$i - 1]['BankTransMatches'] = $transaction_rows[$i]['BankTransMatches'];
                }
                else {
                    if ( $transaction_rows[$i - 2]['BankTransMatches'] == 0 ) {
                        if ( $statement = DB_fetch_array(DB_query($statementSQL_3, $db)) ) {
                            $transaction_rows[$i]['BankStatementID'] = $statement['BankStatementID'];
                            $transaction_rows[$i - 1]['BankStatementID'] = $statement['BankStatementID'];
                            $transaction_rows[$i - 2]['BankStatementID'] = $statement['BankStatementID'];
                            $transaction_rows[$i]['BankTransMatches'] = $transaction_rows[$i]['BankTransID'] . ',' .
                                $transaction_rows[$i - 1]['BankTransID'] . ',' .
                                $transaction_rows[$i - 2]['BankTransID'];
                            $transaction_rows[$i - 1]['BankTransMatches'] = $transaction_rows[$i]['BankTransMatches'];
                            $transaction_rows[$i - 2]['BankTransMatches'] = $transaction_rows[$i]['BankTransMatches'];
                        }
                    }
                }
            }
        }
        $i ++;
    }

    foreach ( $transaction_rows as $i => $thisrow )
        if ( $thisrow['BankTransMatches'] != '' ) {
            if ( ($thisrow['BankTransMatches'] == $last_rows_BankTransMatches) && ($last_rows_BankTransMatches != '') ) {
                if ( $k == 0 ) {
                    echo "<tr bgcolor='#CCCCCC'>";
                }
                else {
                    echo "<tr bgcolor='#EEEEEE'>";
                }
                printf("<td>%s</td>
				<td>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=RIGHT>%s</td>" .
                    "<td></td><td></td>
				</tr>", $thisrow['TransNo'] . ": " . $thisrow['Ref'], $thisrow['DisplayDate'], number_format($thisrow['Amt'], 2), number_format($thisrow['Outstanding'], 2)
                );
            }
            else {
                if ( $k == 1 ) {
                    echo "<tr bgcolor='#CCCCCC'>";
                    $k = 0;
                }
                else {
                    echo "<tr bgcolor='#EEEEEE'>";
                    $k = 1;
                }

                $stateAmount = "";
                $stateCustRef = "";
                $stateBankRef = "";
                $stateDescription = "";
                $stateDate = "";

                if ( $thisrow['BankTransMatches'] != '' ) {
                    $statementSQL = " SELECT * FROM BankStatements WHERE BankStatementID = " . $thisrow['BankStatementID'];
                    if ( $statement = DB_fetch_array(DB_query($statementSQL, $db)) ) {
                        $stateAmount = $statement['Amount'];
                        $stateCustRef = $statement['CustRef'];
                        $stateBankRef = $statement['BankRef'];
                        $stateDescription = $statement['BankDescription'];
                        $stateDate = $statement['BankPostDate'];
                    }
                }

                printf("<td>%s</td>
				<td>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=RIGHT>%s</td> " .
                    "<td ALIGN=CENTER><INPUT TYPE='checkbox' NAME='Clear_%s'><INPUT TYPE=HIDDEN NAME='BankTrans_%s' VALUE=%s></td> " .
                    "<td>%s<INPUT TYPE=HIDDEN NAME='BankStatementID_%s' VALUE=%s>
				      <INPUT TYPE=HIDDEN NAME='BankTransMatches_%s' VALUE=%s></td>
				</tr>", $thisrow['TransNo'] . ": " . $thisrow['Ref'], $thisrow['DisplayDate'], number_format($thisrow['Amt'], 2), number_format($thisrow['Outstanding'], 2), $i, $i, $thisrow['BankTransID'], "\$$stateAmount<I> $stateDate</I> " .
                    (($thisrow['Amt'] < 0) ? "(CHK#$stateCustRef)" : "") . " $stateBankRef ( $stateDescription )", $i, $thisrow['BankStatementID'], $i, $thisrow['BankTransMatches']
                );
                $last_rows_BankTransMatches = $thisrow['BankTransMatches'];
            }

            $j ++;
            If ( $j == 12 ) {
                $j = 1;
                echo $TableHeader;
            }
            //end of page full new headings if
            $i ++;
        }
    //end of while loop

    echo "</TABLE><CENTER><INPUT TYPE=HIDDEN NAME='RowCounter' VALUE=$i><INPUT TYPE=SUBMIT NAME='Update' VALUE='" . _('Update Matching') . "'></CENTER>";
}

echo '</form>';
include('includes/footer.inc');
?>
