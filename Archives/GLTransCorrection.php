<?php

/* $Revision: 1.5 $ */

use Rialto\AccountingBundle\Entity\Period;$PageSecurity = 8;

include ("includes/session.inc");
$title = _('General Ledger Transaction Inquiry');
include("includes/header.inc");
include("includes/DateFunctions.inc");
include('includes/WO_ui_input.inc');
include ('includes/DateFunctions.inc');
include ('includes/CommonGumstix.inc');

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";

if ( ( ! isset($_GET['TypeID']) OR ! isset($_GET['TransNo'])) && ( ! isset($_POST['TypeID']) OR ! isset($_POST['TransNo'])) ) {
    prnMsg(_('The script must be called with a valid transaction type and transaction number to review the general ledger postings for'), 'warn');
    echo "<P><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
    exit;
}

if ( (isset($_POST['TypeID']) && isset($_POST['TransNo']) ) ) {
    $TypeID = $_POST['TypeID'];
    $TransNo = $_POST['TransNo'];
    $CheckNum = $_POST['CheckNum'];
}
elseif ( isset($_GET['TypeID']) && isset($_GET['TransNo']) ) {
    $TypeID = $_GET['TypeID'];
    $TransNo = $_GET['TransNo'];
    $CheckNum = $_GET['CheckNum'];
}

Input_Hidden("TypeID", $TypeID);
Input_Hidden("TransNo", $TransNo);
Input_Hidden("CheckNum", $CheckNum);

$SQL = "SELECT TypeName, TypeNo FROM SysTypes WHERE TypeID=$TypeID";
$ErrMsg = _('The transaction type') . ' ' . $TypeID . ' ' . _('could not be retrieved');
$TypeResult = DB_query($SQL, $db, $ErrMsg);
if ( DB_num_rows($TypeResult) == 0 ) {
    prnMsg(_('No transaction type is defined for type ') . $TypeID, 'error');
    include('includes/footer.inc');
    exit;
}

$myrow = DB_fetch_row($TypeResult);
$TransName = $myrow[0];
if ( $myrow[1] < $TransNo ) {
    prnMsg(_('The transaction number the script was called with is requesting a') . ' ' . $TransName . ' ' . _('beyond the last one entered'), 'error');
    include('includes/footer.inc');
    exit;
}


/* 	first let's see if this is the last pass; if so-- insert	 */
if ( isset($_POST['Post']) ) {
    $SQL = "BEGIN";
    $ErrMsg = _('The credit trans failed...');
    $UPChequesResult = DB_query($SQL, $db, $ErrMsg);

    $Amount = $_POST['y'];
    $Account = $_POST['CR_Account'];
    $Ref = $_POST['CR_Ref'];
    $Prd = $_POST['Prd'];
    $TranDate = $_POST['TranDate'];
    $SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, Narrative, Amount, Account, PeriodNo ) VALUES
		($TypeID, $TransNo, '$TranDate', '$Ref', $Amount, $Account, $Prd )";
    echo $SQL . " <BR> ";
    $ErrMsg = _('The credit trans failed...');
    $UPChequesResult = DB_query($SQL, $db, $ErrMsg);
    $Amount = -$Amount;
    $Account = $_POST['DR_Account'];
    $Ref = $_POST['DR_Ref'];
    $SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, Narrative, Amount, Account, PeriodNo ) VALUES
                ($TypeID, $TransNo, '$TranDate', '$Ref', $Amount, $Account, $Prd )";
    $ErrMsg = _('The debit trans failed...');
    $UPChequesResult = DB_query($SQL, $db, $ErrMsg);
    echo $SQL . " <BR> ";

    if ( $CheckNum != '' ) {
        $SQL = "UPDATE BankTrans SET Amount=Amount-($Amount)
            WHERE Type=$TypeID AND ChequeNo=$CheckNum";
    }
    else {
        $SQL = "UPDATE BankTrans SET Amount=Amount-($Amount)
            WHERE Type=$TypeID AND TransNo=$TransNo";
    }
    $ErrMsg = _('The debit trans failed...');
    $UPChequesResult = DB_query($SQL, $db, $ErrMsg);
    echo $SQL . " <BR> ";

    $SQL = "SELECT * FROM SuppTrans WHERE Type=$TypeID AND TransNo=$TransNo";
    $ErrMsg = _('The debit trans failed...');
    $UPChequesResult = DB_query($SQL, $db, $ErrMsg);
    echo $SQL . " <BR> ";

    if ( DB_num_rows($UPChequesResult) == 1 ) {
        $SQL = "UPDATE SuppTrans SET OvAmount=OvAmount-($Amount) WHERE Type=$TypeID AND TransNo=$TransNo";
        $ErrMsg = _('The debit trans failed...');
        $UPChequesResult = DB_query($SQL, $db, $ErrMsg);
        echo $SQL . " <BR> ";
    }
    else {
        echo 'Not a supplier transaction<br>';
    }

    $SQL = "COMMIT";
    $ErrMsg = _('The credit trans failed...');
    $UPChequesResult = DB_query($SQL, $db, $ErrMsg);

    include('includes/footer.inc');
    exit;
}


/* 	LET'S SHOW THE BANKTRANS LINE FIRST 	 */


if ( $CheckNum != '' ) {
    $SQL = "SELECT * FROM BankTrans
        WHERE BankTrans.Type = $TypeID AND BankTrans.ChequeNo = $CheckNum";
}
else {
    $SQL = "SELECT * FROM BankTrans
        WHERE BankTrans.Type = $TypeID AND BankTrans.TransNo = $TransNo";
}
$ErrMsg = _('The unpresented cheques could not be retrieved by the SQL because');
$UPChequesResult = DB_query($SQL, $db, $ErrMsg);

echo '<CENTER><TABLE CELLPADDING=2 width=80%>';
$TableHeader = '<TR><TD class="tableheader">' . _('Date') . '</TD>
                        <TD class="tableheader">' . _('Type') . '</TD>
                        <TD class="tableheader">' . _('Account') . '</TD>
                        <TD class="tableheader">' . _('Amount') . '</TD>
                        <TD class="tableheader">' . _('Correct to:') . '</TD>
                        <TD class="tableheader">' . _('Reference') . '</TD></TR>';

echo $TableHeader;
while ( $myrow = DB_fetch_array($UPChequesResult) ) {
    $FormatedTranDate = ConvertSQLDate($myrow["TransDate"]);
    printf('<tr bgcolor="#CCCCCC">
		<td>%s</td>
                <td ALIGN=RIGHT>%s</td>
                <td>%s</td>
                <td ALIGN=RIGHT>%s</td>
                <td><INPUT TYPE=TEXT NAME="NewAmount_%s_%s" SIZE=12 MAXLENGTH=12 Value="%s"></td>
                <td>%s</td>
                </tr>', $FormatedTranDate, $myrow['BankTransType'], $myrow['BankAct'], number_format($myrow['Amount'], 2), $TypeID, $TransNo, $_POST["NewAmount_" . $TypeID . "_" . $TransNo], $myrow['Ref']
    );
    if ( $_POST["NewAmount_" . $TypeID . "_" . $TransNo] != 0 ) {
        $sum += $_POST["NewAmount_" . $TypeID . "_" . $TransNo]
            - $myrow['Amount'];
    }
    $j ++;
    If ( $j == 18 ) {
        $j = 1;
        echo $TableHeader;
    }
}
echo '</TABLE>';

echo "<BR> The amount will be changed by: " . number_format($sum, 2) . "<BR><BR>";


$SQL = "SELECT * FROM SuppTrans WHERE Type=$TypeID AND TransNo=$TransNo";
$ErrMsg = _('The debit trans failed...');
$SuppResult = DB_query($SQL, $db, $ErrMsg);
echo $SQL . " <BR> ";

echo '<CENTER><TABLE CELLPADDING=2 width=80%>';

$TableHeader = '<TR><TD class="tableheader">' . _('Date') . '</TD>
			<TD class="tableheader">' . _('Period') . '</TD>
			<TD class="tableheader">' . _('Account') . '</TD>
			<TD class="tableheader">' . _('Amount') . '</TD>
			<TD class="tableheader">' . _('Narrative') . '</TD>
			<TD class="tableheader">' . _('Posted') . '</TD></TR>';

echo $TableHeader;
$j = 1;
$k = 0; //row colour counter
while ( $myrow = DB_fetch_array($SuppResult) ) {
    if ( $k == 1 ) {
        echo '<tr bgcolor="#CCCCCC">';
        $k = 0;
    }
    else {
        echo '<tr bgcolor="#EEEEEE">';
        $k ++;
    }
    if ( $myrow['Posted'] == 0 ) {
        $Posted = _('No');
    }
    else {
        $Posted = _('Yes');
    }
    if ( $myrow['Amount'] < 0 ) {
        Input_Hidden("Prd", $myrow['PeriodNo']);
        Input_Hidden("TranDate", $myrow['TranDate']);
        Input_Hidden("CR_Account", $myrow['Account']);
        Input_Hidden("CR_Ref", $myrow['Narrative']);
    }
    else {
        Input_Hidden("DR_Ref", $myrow['Narrative']);
        Input_Hidden("DR_Account", $myrow['Account']);
    }
    $FormatedTranDate = ConvertSQLDate($myrow["TranDate"]);
    printf('<td>%s</td>
       		<td ALIGN=RIGHT>%s</td>
		<td>%s</td>
		<td ALIGN=RIGHT>%s</td>
		<td>%s</td>
		<td>%s</td>
		</tr>', $FormatedTranDate, $myrow['PeriodNo'], $myrow['AccountName'], number_format($myrow['OvAmount'], 2), $myrow['SuppReference'], $Posted);
    $j ++;
    If ( $j == 18 ) {
        $j = 1;
        echo $TableHeader;
    }
}
//end of while loop

echo '</TABLE></CENTER>';




/* show a table of the transactions returned by the SQL */
$SQL = "SELECT * FROM GLTrans
	INNER JOIN ChartMaster ON GLTrans.Account = ChartMaster.AccountCode
	WHERE Type=$TypeID AND TypeNo=$TransNo ORDER BY CounterIndex";
$ErrMsg = _('The transactions for') . ' ' . $TransName . ' ' . _('number') . ' ' . $TransNo . ' ' . _('could not be retrieved');
$TransResult = DB_query($SQL, $db, $ErrMsg);
echo $SQL;

echo '<CENTER><TABLE CELLPADDING=2 width=80%>';

$TableHeader = '<TR><TD class="tableheader">' . _('Date') . '</TD>
			<TD class="tableheader">' . _('Period') . '</TD>
			<TD class="tableheader">' . _('Account') . '</TD>
			<TD class="tableheader">' . _('Amount') . '</TD>
			<TD class="tableheader">' . _('Narrative') . '</TD>
			<TD class="tableheader">' . _('Posted') . '</TD></TR>';

echo $TableHeader;
$j = 1;
$k = 0; //row colour counter
while ( $myrow = DB_fetch_array($TransResult) ) {
    if ( $k == 1 ) {
        echo '<tr bgcolor="#CCCCCC">';
        $k = 0;
    }
    else {
        echo '<tr bgcolor="#EEEEEE">';
        $k ++;
    }
    if ( $myrow['Posted'] == 0 ) {
        $Posted = _('No');
    }
    else {
        $Posted = _('Yes');
    }
    if ( $myrow['Amount'] < 0 ) {
        Input_Hidden("Prd", $myrow['PeriodNo']);
        Input_Hidden("TranDate", $myrow['TranDate']);
        Input_Hidden("CR_Account", $myrow['Account']);
        Input_Hidden("CR_Ref", $myrow['Narrative']);
    }
    else {
        Input_Hidden("DR_Ref", $myrow['Narrative']);
        Input_Hidden("DR_Account", $myrow['Account']);
    }
    $FormatedTranDate = ConvertSQLDate($myrow["TranDate"]);
    printf('<td>%s</td>
       		<td ALIGN=RIGHT>%s</td>
		<td>%s</td>
		<td ALIGN=RIGHT>%s</td>
		<td>%s</td>
		<td>%s</td>
		</tr>', $FormatedTranDate, $myrow['PeriodNo'], $myrow['AccountName'], number_format($myrow['Amount'], 2), $myrow['Narrative'], $Posted);
    $j ++;
    If ( $j == 18 ) {
        $j = 1;
        echo $TableHeader;
    }
}
//end of while loop

echo '</TABLE></CENTER>';
unset($_POST['y']);
Input_Submit("Recheck", 'Just check');
Input_Submit("Post", 'Post');
Input_Hidden('y', $sum);
echo '</FORM>';
include('includes/footer.inc');
?>
