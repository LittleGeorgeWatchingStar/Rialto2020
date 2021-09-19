<?php
/* $Revision: 1.5 $ */

$PageSecurity = 7;

require_once 'config.php';
require_once 'includes/ConnectDB.inc';

$title = _('Card Transaction Posting');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include_once('includes/SQL_CommonFunctions.inc');

$_POST['BeforeDate'] = Date($DefaultDateFormat);
$_POST['AfterDate'] = Date($DefaultDateFormat, Mktime(0,0,0,Date("m")-3,Date("d"),Date("y")));
$todate = date("Y-m-d");
$typesToSweep = '12, 101'; // receipt, customer refund

if ( 1 ) {    //    $InputError !=1 AND isset($_POST["ShowTransactions"])){

    $SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
    $SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);
    $sql = "SELECT * FROM CardTrans
        WHERE PostDate >= '". $SQLAfterDate . "'
        AND PostDate <= '" . $SQLBeforeDate . "'
        AND Posted = 0
        AND Voided = 0
        AND Type in ($typesToSweep)
        ORDER BY PostDate";
    $ErrMsg = _('The payments with the selected criteria could not be retrieved because');
    $PaymentsResult = DB_query($sql, $db, $ErrMsg);

    $TableHeader = '<TR>
                    <TD class="tableheader">' .  _('PostDate'). '</TD>
                    <TD class="tableheader">' .  _('TransactionID'). '</TD>
                    <TD class="tableheader">' .  _('AmEX'). '</TD>
                    <TD class="tableheader">' . _('VIMC') . '</TD>
                    <TD class="tableheader">' . _('Amount') . '</TD>
                    </TR>';
    echo '<br><center><TABLE CELLPADDING=2 BORDER=2 WIDTH=50%>' . $TableHeader;

    $j = 1; //page length counter
    $k = 0; //row colour counter
    $i = 1; //no of rows counter
    $previousPostDate = -1;

    while ($myrow=DB_fetch_array($PaymentsResult)) {
        if ($previousPostDate == -1) {
            $previousPostDate= $myrow['PostDate'];
            $PostDateRunningPayTotal = 0;
            $PostDateRunningApprovedTotal = 0;
            $grandApprovedTotal = 0;
            $grandPayTotal = 0;
        }

        if ($myrow['PostDate']==$previousPostDate) {
            if ($myrow['Approved']==1) {
                $PostDateRunningApprovedTotal    += $myrow['Amount'];
                ${"PostDateRunningApprovedTotal".$myrow['CardID']} +=$myrow['Amount'];
                $grandApprovedTotal        += $myrow['Amount'];
                ${"grandApprovedTotal".$myrow['CardID'] } +=$myrow['Amount'];
            } else {
                $PostDateRunningPayTotal += $myrow['Amount'];
                ${"PostDateRunningPayTotal".$myrow['CardID']} +=$myrow['Amount'];
                $grandPayTotal += $myrow['Amount'];
                ${"grandPayTotal".$myrow['CardID'] } +=$myrow['Amount'];
            }
        } else {
            if ( ${"PostDateRunningPayTotalVISA"} +
                 ${"PostDateRunningPayTotalMCRD"} +
                 ${"PostDateRunningPayTotalDISC"} != 0 )
            {
                $btamount = ${"PostDateRunningPayTotalVISA"} +
                    ${"PostDateRunningPayTotalMCRD"} +
                    ${"PostDateRunningPayTotalDISC"};
                printf("<td>%s</td>
                        <td ALIGN=RIGHT>%s</td>
                        <td ALIGN=RIGHT>%s</td>
                        <td ALIGN=RIGHT>%s</td>
                        <td ALIGN=CENTER>%s</td>
                        </tr>",
                    $previousPostDate,
                    'Visa, Mastercard, & Discover',
                    '',
                    number_format($btamount, 2),
                    '');
                $_POST["Approved_".$i]='checked';
                $_POST['PostDate_' . $i ] = $previousPostDate;
                $_POST['CardType_' .$i ] = 'VIMC';
                $_POST['Amount_' . $i ] = $btamount;
                $i++;
            }
            if (${"PostDateRunningPayTotalAMEX"} != 0 ) {
                $btamount = ${"PostDateRunningPayTotalAMEX"};
                printf("<td>%s</td>
                        <td ALIGN=RIGHT>%s</td>
                        <td ALIGN=RIGHT>%s</td>
                        <td ALIGN=RIGHT>%s</td>
                        <td ALIGN=CENTER>%s</td>
                        </tr>",
                    $previousPostDate,
                    'American express',
                    number_format($btamount, 2),
                    '',
                    '' );
                $_POST["Approved_".$i]='checked';
                $_POST['PostDate_' . $i ] = $previousPostDate;
                $_POST['CardType_' .$i ] = 'AmEx';
                $_POST['Amount_' . $i ] = $btamount;
                $i++;
            }
            $previousPostDate = $myrow['PostDate'];
            if ($myrow['Approved']==1) {
                $PostDateRunningApprovedTotal = $myrow['Amount'];
                $grandApprovedTotal         += $myrow['Amount'];
                $PostDateRunningPayTotal  = 0;
                ${"PostDateRunningPayTotalAMEX"} = 0;
                ${"PostDateRunningPayTotalVISA"} = 0;
                ${"PostDateRunningPayTotalMCRD"} = 0;
                ${"PostDateRunningPayTotalDISC"} = 0;
                ${"PostDateRunningApprovedTotalAMEX"} = 0;
                ${"PostDateRunningApprovedTotalVISA"} = 0;
                ${"PostDateRunningApprovedTotalMCRD"} = 0;
                ${"PostDateRunningApprovedTotalDISC"} = 0;
                ${"PostDateRunningApprovedTotal".$myrow['CardID']} +=$myrow['Amount'];
                ${"grandPayTotal".$myrow['CardID'] } +=$myrow['Amount'];
            } else {
                $PostDateRunningPayTotal  = $myrow['Amount'];
                $grandPayTotal         += $myrow['Amount'];
                $PostDateRunningApprovedTotal = 0;
                ${"PostDateRunningPayTotalAMEX"} = 0;
                ${"PostDateRunningPayTotalVISA"} = 0;
                ${"PostDateRunningPayTotalMCRD"} = 0;
                ${"PostDateRunningPayTotalDISC"} = 0;
                ${"PostDateRunningApprovedTotalAMEX"} = 0;
                ${"PostDateRunningApprovedTotalVISA"} = 0;
                ${"PostDateRunningApprovedTotalMCRD"} = 0;
                ${"PostDateRunningApprovedTotalDISC"} = 0;
                ${"PostDateRunningPayTotal".$myrow['CardID']} =$myrow['Amount'];
                ${"grandPayTotal".$myrow['CardID'] } +=$myrow['Amount'];
            }
        }

        $DisplayTransDate = ConvertSQLDate($myrow["TransDate"]);
        if (  $myrow["DueDate"] < $todate ){
            echo "<tr bgcolor='#CCCCCC'>";
        } else {
            echo "<tr bgcolor='#EEEEEE'>";
        }
        printf("<td>%s</td>
                <td>%s</td>
                <td ALIGN=RIGHT>%s</td>
                <td ALIGN=RIGHT>%s</td>
                <td ALIGN=RIGHT>%s</td>
                </tr>",
            $myrow['PostDate'],
            $myrow['TransactionID'],
            $myrow['CardID'] == 'AMEX' ? number_format($myrow['Amount'],2) : '',
            $myrow['CardID'] != 'AMEX' ? number_format($myrow['Amount'],2) : '',
            number_format($myrow['Amount'],2)
        );

        $i++;
    }
    //////    //end of while loop

    if ( ${"PostDateRunningPayTotalVISA"} +
         ${"PostDateRunningPayTotalMCRD"} +
         ${"PostDateRunningPayTotalDISC"} != 0 )
    {
        $btamount = ${"PostDateRunningPayTotalVISA"} +
            ${"PostDateRunningPayTotalMCRD"} +
            ${"PostDateRunningPayTotalDISC"};
        printf("<tr>
                <td>%s</td>
                <td ALIGN=RIGHT>%s</td>
                <td ALIGN=RIGHT>%s</td>
                <td ALIGN=RIGHT>%s</td>
                <td ALIGN=CENTER>%s</td>
                </tr>",
            $previousPostDate,
            'Visa, Mastercard, & Discover',
            '',
            number_format($btamount, 2),
            '' );
        $_POST["Approved_".$i]='checked';
        $_POST['PostDate_' . $i ] = $previousPostDate;
        $_POST['CardType_' .$i ] = 'VIMC';
        $_POST['Amount_' . $i ] = $btamount;
        $i++;
    }
    if (${"PostDateRunningPayTotalAMEX"} != 0 ) {
        $btamount =  ${"PostDateRunningPayTotalAMEX"};
        printf("<tr>
                <td>%s</td>
                <td ALIGN=RIGHT>%s</td>
                <td ALIGN=RIGHT>%s</td>
                <td ALIGN=RIGHT>%s</td>
                <td ALIGN=CENTER>%s</td>
                </tr>",
            $previousPostDate,
            'American express',
            number_format($btamount, 2),
            '',
            '');
        $_POST["Approved_".$i]='checked';
        $_POST['PostDate_' . $i ] = $previousPostDate;
        $_POST['CardType_' .$i ] = 'AmEx';
        $_POST['Amount_' . $i ] = $btamount;
        $i++;
    }
    ////
    echo "<tr bgcolor='#EE6666'>";
    printf("<td>%s</td>
        <td>%s</td>
        <td ALIGN=RIGHT>%s</td>
        <td ALIGN=RIGHT>%s</td>
        <td ALIGN=RIGHT>%s</td>
        </tr>",
        '',
        'Grand Totals',
    number_format(${"grandPayTotalAMEX"},2),
    number_format(${"grandPayTotalVISA"} + ${"grandPayTotalMCRD"} + ${"grandPayTotalDISC"}, 2),
    number_format($grandPayTotal,2),
    number_format($grandApprovedTotal,2)
    );
    echo "</TABLE>";
}
$_POST['RowCounter']=$i;

echo '<BR>';

DB_query( "BEGIN", $db );
if ( 1 ) {
    echo '<CENTER><TABLE WIDTH=50% BORDER=1>';
    echo "<TR  bgcolor='#EE4400'><TH>Date</TH><TH COLSPAN=2>Card</TH><TH>Amount Posted</TH></TR>";
    echo "<COLGROUP><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='1*'>";
    for ($Counter=1;$Counter <= $_POST['RowCounter']; $Counter++){
        /*Update the cardTrans record to match it off */
        $thisApproval = ($_POST["Approve_" . $Counter] == True) ? 1:0;
        /*    THREE TRANSACTION ENTRIES:
         DR: GLTrans -- Bank Account
         CR: GLTrans -- Authorize.net Account
         Journal BankTrans
         */
        if ($_POST["Approved_" . $Counter] != '') {
            $TransNo = GetNextTransNo(102, $db);
            $TransDate = $_POST['PostDate_' . $Counter];
            $PeriodNo  = GetPeriod( ConvertSQLDate($TransDate), $db);
            $Amount     = $_POST['Amount_' . $Counter];
            $Ref     = "Sweep " . $_POST['CardType_' . $Counter] . " - " . $_POST['PostDate_' . $Counter];

            $sql = "INSERT INTO GLTrans (Type,TypeNo,TranDate,PeriodNo,Amount,Account,Narrative) VALUES
                (102, '$TransNo','$TransDate','$PeriodNo', '" . (-$Amount) . "', '10600', '$Ref'  )";
            $ErrMsg =  _('Could not match off this payment beacause');
            $result = DB_query($sql,$db,$ErrMsg);

            $sql = "INSERT INTO GLTrans (Type,TypeNo,TranDate,PeriodNo,Amount,Account,Narrative) VALUES
                (102, '$TransNo','$TransDate','$PeriodNo', '$Amount', '10200', '$Ref'  )";
            $ErrMsg =  _('Could not match off this payment beacause');
            $result = DB_query($sql,$db,$ErrMsg);

            $sql = "INSERT INTO BankTrans ( Type, TransNo, Amount, BankAct, Ref, TransDate ) VALUES( 102, '$TransNo', '$Amount', '10200' , '$Ref' , '$TransDate' )";
            $ErrMsg =  _('Could not match off this payment beacause');
            $result = DB_query($sql,$db,$ErrMsg);

            if ($_POST['CardType_'.$Counter] == 'AmEx') {
                $sql = "UPDATE CardTrans SET Posted=1 WHERE CardID='AMEX' AND Type in ($typesToSweep) AND PostDate='".$_POST['PostDate_'.$Counter]."'";
                $ErrMsg =  _('Could not match off this payment beacause');
                $result = DB_query($sql,$db,$ErrMsg);

                $fees = $Amount * 0.03500;
                $sql = "INSERT INTO GLTrans (Type,TypeNo,TranDate,PeriodNo,Amount,Account,Narrative) VALUES
                                    (102, '$TransNo','$TransDate','$PeriodNo', '-$fees', '10200', 'FEES: $Ref'  )";
                $ErrMsg =  _('Could not match off this payment beacause');
                $result = DB_query($sql,$db,$ErrMsg);

                $sql = "INSERT INTO GLTrans (Type,TypeNo,TranDate,PeriodNo,Amount,Account,Narrative) VALUES
                                    (102, '$TransNo','$TransDate','$PeriodNo', '$fees', '21000', 'FEES: $Ref'  )";
                $ErrMsg =  _('Could not match off this payment beacause');
                $result = DB_query($sql,$db,$ErrMsg);

                $sql = "INSERT INTO BankTrans ( Type, TransNo, Amount, BankAct, Ref, TransDate ) VALUES
                    ( 102, '$TransNo', '-$fees', '10200' , 'FEES: $Ref' , '$TransDate' )";
                $ErrMsg =  _('Could not match off this payment beacause');
                $result = DB_query($sql,$db,$ErrMsg);

            } else {
                $sql = "UPDATE CardTrans SET Posted=1 WHERE CardID='VISA' AND Type in ($typesToSweep) AND PostDate='".$_POST['PostDate_'.$Counter]."'";
                $ErrMsg =  _('Could not match off this payment beacause');
                $result = DB_query($sql,$db,$ErrMsg);

                $sql = "UPDATE CardTrans SET Posted=1 WHERE CardID='MCRD' AND Type in ($typesToSweep) AND PostDate='".$_POST['PostDate_'.$Counter]."'";
                $ErrMsg =  _('Could not match off this payment beacause');
                $result = DB_query($sql,$db,$ErrMsg);

                $sql = "UPDATE CardTrans SET Posted=1 WHERE CardID='DISC' AND Type in ($typesToSweep) AND PostDate='".$_POST['PostDate_'.$Counter]."'";
                $ErrMsg =  _('Could not match off this payment beacause');
                $result = DB_query($sql,$db,$ErrMsg);
            }
            echo "<TR>";
            echo "<TD><CENTER>" . $_POST["PostDate_" . $Counter] . "</TD>";
            echo  "<TD COLSPAN=2><CENTER>" . $_POST["CardType_" . $Counter] . "</TD>";
            echo  "<TD><CENTER>" . $_POST["Amount_" . $Counter] . "</TD>";
            echo "</TR>";
        }
    }
    DB_query('COMMIT',  $db );
    //  For debugging -- DB_query('ROLLBACK',  $db );
    echo '</TABLE>';
    include('includes/footer.inc');
}
?>
