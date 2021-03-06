<?php

/* $Revision: 1.5 $ */

$PageSecurity = 7;

include("includes/session.inc");

if ( ($_GET["Type"] == 'Receipts') OR ($_POST["Type"] == 'Receipts') ) {
    $Type = 'Receipts';
    $TypeName = _('Receipts');
    $title = _('Bank Account Deposits Matching');
}
elseif ( ($_GET["Type"] == 'Payments') OR ($_POST["Type"] == 'Payments') ) {
    $Type = 'Payments';
    $TypeName = _('Payments');
    $title = _('Bank Account Payments Matching');
}
else {
    prnMsg(_('This page must be called with a bank transaction type') . '. ' . _('It should not be called directly'), 'error');
    include ('includes/footer.inc');
    exit;
}

include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/WO_ui_input.inc');

if ( isset($_POST['Update']) AND $_POST['RowCounter'] > 1 ) {
    for ( $Counter = 1; $Counter <= $_POST['RowCounter']; $Counter ++  ) {
        if ( $_POST["Clear_" . $Counter] == True ) {
            if ( is_numeric((float) $_POST["AmtClear_" . $Counter]) AND (($_POST["AmtClear_" . $Counter] > 0) ) ) {
                /* Update the banktrans recoord to match it off */
                $sql = "UPDATE BankTrans SET AmountCleared=(Amount/ExRate)
                    WHERE BankTransID=" . $_POST["BankTrans_" . $Counter];
                $ErrMsg = _('Could not match off this payment beacause');
                $result = DB_query($sql, $db, $ErrMsg);
                if ( isset($_POST["BankStatementID_" . $Counter]) && ($_POST["BankStatementID_" . $Counter] != 0) ) {
                    $msql = " UPDATE BankStatements
                        SET BankTransID = IF ( BankTransID >'0',
                        CONCAT( BankTransID, ',', '" . $_POST["BankTrans_" . $Counter] . "'), '" .
                        $_POST["BankTrans_" . $Counter] . "')" .
                        " WHERE  BankStatementID='" . $_POST["BankStatementID_" . $Counter] . "'";
                    $ErrMsg = _('Could not update the amount matched off this bank transaction because');
                    $result = DB_query($msql, $db, $ErrMsg);
                }
            }
            else {
                /* Update the banktrans recoord to match it off */
                $sql = "UPDATE BankTrans SET AmountCleared=(Amount/ExRate)
					WHERE BankTransID=" . $_POST["BankTrans_" . $Counter];
                $ErrMsg = _('Could not match off this payment beacause');
                $result = DB_query($sql, $db, $ErrMsg);

                if ( isset($_POST["BankStatementID_" . $Counter]) && ($_POST["BankStatementID_" . $Counter] != 0) ) {
                    $msql = " UPDATE BankStatements " .
                        " SET    BankTransID = '" . $_POST["BankTrans_" . $Counter] . "'" .
                        " WHERE  BankStatementID='" . $_POST["BankStatementID_" . $Counter] . "'";
                    $ErrMsg = _('Could not update the amount matched off this bank transaction because');
                    $result = DB_query($msql, $db, $ErrMsg);
                }
            }
        }
        elseif ( is_numeric((float) $_POST["AmtClear_" . $Counter]) AND (($_POST["AmtClear_" . $Counter] < 0 AND $Type == 'Payments') OR ($Type == 'Receipts' AND ($_POST["AmtClear_" . $Counter] > 0))) ) {
            /* if the amount entered was numeric and negative for a payment or positive for a receipt */
            $sql = "UPDATE BankTrans SET AmountCleared=" .
                $_POST["AmtClear_" . $Counter] . "
                WHERE BankTransID=" . $_POST["BankTrans_" . $Counter];

            $ErrMsg = _('Could not update the amount matched off this bank transaction because');
            $result = DB_query($sql, $db, $ErrMsg);
        }
        elseif ( $_POST["Unclear_" . $Counter] == True ) {
            $sql = "UPDATE BankTrans SET AmountCleared = 0
					WHERE BankTransID=" . $_POST["BankTrans_" . $Counter];
            $ErrMsg = _('Could not unclear this bank transaction because');
            $result = DB_query($sql, $db, $ErrMsg);
        }
    }
    /* Show the updated position with the same criteria as previously entered */
    $_POST["ShowTransactions"] = True;
}


echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo "<INPUT TYPE=HIDDEN Name=Type Value=$Type>";

echo '<TABLE><TR>';
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
    $_POST['AfterDate'] = Date($DefaultDateFormat, Mktime(0, 0, 0, Date("m") - 1, Date("d"), Date("y")));
}

echo '<TR><TD>' . _('Show') . ' ' . $TypeName . ' ' . _('before') . ':</TD>
	<TD><INPUT TYPE=TEXT NAM="BeforeDate" SIZE=12 MAXLENGTH=12 Value="' . $_POST['BeforeDate'] . '"></TD>';
echo '<TD>' . _('but after') . ':</TD>
	<TD><INPUT TYPE=TEXT NAME="AfterDate" SIZE=12 MAXLENGTH=12 Value="' . $_POST['AfterDate'] . '"></TD></TR>';
echo '<TR><TD COLSPAN=3>' . _('Choose outstanding') . ' ' . $TypeName . ' ' . _('only or all') . ' ' . $TypeName . ' ' . _('in the date range') . ':</TD>
	<TD><SELECT NAME="Ostg_or_All">';

if ( $_POST["Ostg_or_All"] == 'All' ) {
    echo '<OPTION SELECTED Value="All">' . _('Show all') . ' ' . $TypeName . ' ' . _('in the date range');
    echo '<OPTION Value="Ostdg">' . _('Show unmatched') . ' ' . $TypeName . ' ' . _('only');
}
else {
    echo '<OPTION Value="All">' . _('Show all') . ' ' . $TypeName . ' ' . _('in the date range');
    echo '<OPTION SELECTED Value="Ostdg">' . _('Show unmatched') . ' ' . $TypeName . ' ' . _('only');
}
echo '</SELECT></TD></TR>';

echo '<TR><TD COLSPAN=3>' . _('Choose to display only the first 20 matching') . ' ' . $TypeName . ' ' . _('or all') . ' ' . $TypeName . ' ' . _('meeting the criteria') . ':</TD><TD><SELECT NAME="First20_or_All">';
if ( $_POST["First20_or_All"] == 'All' ) {
    echo '<OPTION SELECTED Value="All">' . _('Show all ') . ' ' . $TypeName . ' ' . _(' in the date range');
    echo '<OPTION Value="First20">' . _('Show only the first 20 ') . ' ' . $TypeName;
}
else {
    echo '<OPTION SELECTED Value="All">' . _('Show all ') . ' ' . $TypeName . _(' in the date range');
    echo '<OPTION Value="First20">' . _('Show only the first 20 ') . ' ' . $TypeName;
}
echo '</SELECT></TD></TR>';


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

    if ( $_POST["Ostg_or_All"] == 'All' ) {
        if ( $Type == 'Payments' ) {
            $sql = "SELECT BankTransID,
					Ref,
					TransNo,
                    ChequeNo,
					AmountCleared,
					TransDate,
					Amount/ExRate AS Amt,
					BankTransType
				FROM BankTrans
				WHERE Amount <0
				AND TransDate >= '" . $SQLAfterDate . "'
				AND TransDate <= '" . $SQLBeforeDate . "'
				AND BankAct=" . $_POST["BankAccount"] . "
				ORDER BY Type, TransNo";
        }
        else { /* Type must == Receipts */
            $sql = "SELECT BankTransID,
					Ref,
                    TransNo,
                    ChequeNo,
					AmountCleared,
					TransDate,
					Amount/ExRate AS Amt,
					BankTransType
				FROM BankTrans
				WHERE Amount >0
				AND TransDate >= '" . $SQLAfterDate . "'
				AND TransDate <= '" . $SQLBeforeDate . "'
				AND BankAct=" . $_POST["BankAccount"] . "
				ORDER BY Type, TransDate";
        }
    }
    else { /* it must be only the outstanding bank trans required */
        if ( $Type == 'Payments' ) {
            $sql = "SELECT BankTransID,
					Ref,
                    TransNo,
                    ChequeNo,
					AmountCleared,
					TransDate,
					Amount/ExRate AS Amt,
					BankTransType
				FROM BankTrans
				WHERE Amount <0
				AND TransDate >= '" . $SQLAfterDate . "'
				AND TransDate <= '" . $SQLBeforeDate . "'
				AND BankAct=" . $_POST["BankAccount"] . "
				AND  ABS(AmountCleared - (Amount / ExRate)) > 0.009
				ORDER BY Type, TransNo";
        }
        else { /* Type must == Receipts */
            $sql = "SELECT BankTransID,
                    TransNo,
                    ChequeNo,
					Ref,
					AmountCleared,
					TransDate,
					Amount/ExRate AS Amt,
					BankTransType
				FROM BankTrans
				WHERE Amount >0
				AND TransDate >= '" . $SQLAfterDate . "'
				AND TransDate <= '" . $SQLBeforeDate . "'
				AND BankAct=" . $_POST["BankAccount"] . "
				AND  ABS(AmountCleared - (Amount / ExRate)) > 0.009
				ORDER BY Type, TransDate";
        }
    }
    if ( $_POST["First20_or_All"] != 'All' ) {
        $sql = $sql . " LIMIT 20";
    }

    $ErrMsg = _('The payments with the selected criteria could not be retrieved because');
    $PaymentsResult = DB_query($sql, $db, $ErrMsg);

    $TableHeader = '<TR><th></th>
			 <th>' . $TypeName . '</th>
             <th>Trans No</th>
             <th>' . _('Ref') . '</th>
             <th>Cheque No</th>
			 <th>' . _('Date') . '</th>
			 <th>' . _('Amount') . '</th>
			 <th>' . _('Outstanding') . '</th>
			 <th COLSPAN=3 ALIGN=CENTER>' . _('Clear') . ' / ' . _('Unclear') . '</th>
             <th>' . _('Potential match') . '</th>
		</TR>';
    echo '<TABLE CELLPADDING=2 BORDER=2>' . $TableHeader;

    $j = 1;  //page length counter
    $k = 0; //row colour counter
    $i = 1; //no of rows counter

    while ( $myrow = DB_fetch_array($PaymentsResult) ) {

        $DisplayTranDate = ConvertSQLDate($myrow["TransDate"]);
        $Outstanding = $myrow["Amt"] - $myrow["AmountCleared"];
        if ( ABS($Outstanding) < 0.009 ) { /* the payment is cleared dont show the check box */

            printf("<tr bgcolor='#CCCEEE'>
                <td> </td>
                <td>%s</td>
                <td>%s</td>
				<td>%s</td>
				<td>%s</td>
                <td>%s</td>
				<td class='numeric'>%s</td>
				<td class='numeric'>%s</td>
				<td COLSPAN=2 ALIGN=CENTER>%s</td>
				<td ALIGN=CENTER>
                    <INPUT TYPE='checkbox' NAME='Unclear_%s'>
                    <INPUT TYPE=HIDDEN NAME='BankTrans_%s' VALUE=%s>
                </td>
                <td>%s</td>
				</tr>",
                $myrow['BankTransType'],
                $myrow['TransNo'],
                $myrow['Ref'],
                $myrow['ChequeNo'],
                $DisplayTranDate,
                number_format($myrow['Amt'], 2),
                number_format($Outstanding, 2),
                _('Unclear'),
                $i,
                $i,
                $myrow['BankTransID'],
                ''
            );
        }
        else {
            $statementSQL =
                " SELECT * FROM BankStatements " .
                " WHERE " .
                " DATEDIFF(BankPostDate,'" . $myrow['TransDate'] . "') >= 0 " .
                " AND ( " .
                " Amount= " . $myrow['Amt'];
            if ( ! check_to_bool($_POST['Ignore_' . $i]) ) {
                $statementSQL .= " AND ( " .
                    " ( DATEDIFF(BankPostDate,'" . $myrow['TransDate'] . "')<60
                    AND CustRef=0 )
                    OR CustRef=" . $myrow['ChequeNo'] . ")";
            }
            $statementSQL .= ")";
            if ( strpos($myrow['Ref'], "FEES: Sweep AmEx") !== false ) {
                $statementSQL .=
                    " OR ( DATEDIFF(BankPostDate,'" . $myrow['TransDate'] . "') > 1 AND
                        DATEDIFF(BankPostDate,'" . $myrow['TransDate'] . "') < 6 AND
                        CustRef=0 AND  " .
                    "	BankDescription LIKE '%EXPR%DISC%' " .
                    "	AND ABS(1-ABS(Amount)/ABS(" . $myrow['Amt'] . ")) <0.05 )";
            }
            if ( $statement = DB_fetch_array(DB_query($statementSQL, $db)) ) {
                $stateAmount = $statement['Amount'];
                $stateCustRef = $statement['CustRef'];
                $stateBankRef = $statement['BankRef'];
                $stateDescription = $statement['BankDescription'];
                $stateDate = $statement['BankPostDate'];
            }
            else {
                $stateAmount = "";
                $stateCustRef = "";
                $stateBankRef = "";
                $stateDescription = "";
                $stateDate = "";
                $totalUnmatched += $myrow['Amt'];
            }
            if ( ( $stateDate == "" ) AND ( $myrow['BankTransType'] == 'Direct credit') ) {
                $statementSQL = " SELECT * FROM BankStatements
                    WHERE BankPostDate = '" . $myrow['TransDate'] . "'
                    AND BankDescription LIKE '%" . $myrow['Ref'] . "%'";
                echo $statementSQL . '<HR>';
                if ( $statement = DB_fetch_array(DB_query($statementSQL, $db)) ) {
                    $stateAmount = $statement['Amount'];
                    $stateCustRef = $statement['CustRef'];
                    $stateBankRef = $statement['BankRef'];
                    $stateDescription = $statement['BankDescription'];
                    $stateDate = $statement['BankPostDate'];
                }
            }
            if ( $k == 1 ) {
                echo "<tr bgcolor='#CCCCCC'>";
                $k = 0;
            }
            else {
                echo "<tr bgcolor='#EEEEEE'>";
                $k = 1;
            }
            if ( $stateBankRef == "" ) {
                printf("
                    <td ALIGN=CENTER><INPUT TYPE='checkbox' NAME='Ignore_%s'></td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td class='numeric'>%s</td>
                    <td class='numeric'>%s</td>
                    <td COLSPAN=4></td>
                    </tr>",
                    $i,
                    $myrow['BankTransType'],
                    $myrow['TransNo'],
                    $myrow['Ref'],
                    $myrow['ChequeNo'],
                    $DisplayTranDate,
                    number_format($myrow['Amt'], 2),
                    number_format($Outstanding, 2)
                );
            }
            else {
                printf("
                    <td ALIGN=CENTER><INPUT TYPE='checkbox' NAME='Ignore_%s'></td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td class='numeric'>%s</td>
                    <td class='numeric'>%s</td>
                    <td ALIGN=CENTER><INPUT TYPE='checkbox' NAME='Clear_%s'>
                        <INPUT TYPE=HIDDEN NAME='BankTrans_%s' VALUE=%s>
                    </td>
                    <td COLSPAN=2>
                        <INPUT TYPE='text' MAXLENGTH=15 SIZE=15 NAME='AmtClear_%s'>
                    </td>
                    <td>%s<INPUT TYPE=HIDDEN NAME='BankStatementID_%s' VALUE=%s></td>
                    </tr>",
                    $i,
                    $myrow['BankTransType'],
                    $myrow['TransNo'],
                    $myrow['Ref'],
                    $myrow['ChequeNo'],
                    $DisplayTranDate,
                    number_format($myrow['Amt'], 2),
                    number_format($Outstanding, 2),
                    $i,
                    $i,
                    $myrow['BankTransID'],
                    $i,
                    "\$$stateAmount<I> $stateDate</I> " .
                        ( ($myrow['Amt'] < 0) ? "(CHK#$stateCustRef)" : "") .
                        " $stateBankRef ( $stateDescription )",
                    $i,
                    $statement['BankStatementID']
                );
            }
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
    echo 'Total outstanding on this list: $' . number_format(-$totalUnmatched, 2);
}

echo '</form>';
include('includes/footer.inc');
?>
