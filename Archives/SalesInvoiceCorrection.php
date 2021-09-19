<?php
/* $Revision: 1.5 $ */

use Rialto\AccountingBundle\Entity\Period;$PageSecurity = 8;

include ("includes/session.inc");
$title = _('General Ledger Transaction Inquiry');
include("includes/header.inc");
include("includes/DateFunctions.inc");
include('includes/WO_ui_input.inc');
include ('includes/DateFunctions.inc');

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";

if (  (!isset($_GET['TypeID']) OR !isset($_GET['TransNo']))  && (!isset($_POST['TypeID']) OR !isset($_POST['TransNo']))  ) {
	prnMsg(_('The script must be called with a valid transaction type and transaction number to review the general ledger postings for'),'warn');
	echo "<P><A HREF='$rootpath/index.php?". SID ."'>" . _('Back to the menu') . '</A>';
	exit;
}

if ( (isset($_POST['TypeID']) &&  isset($_POST['TransNo']))) {
	$TypeID = $_POST['TypeID'];
	$TransNo = $_POST['TransNo'];
} elseif (isset($_GET['TypeID']) &&  isset($_GET['TransNo'])) {
        $TypeID = $_GET['TypeID'];
        $TransNo = $_GET['TransNo'];
}

Input_Hidden("TypeID",$TypeID);
Input_Hidden("TransNo",$TransNo);

$SQL = "SELECT TypeName, TypeNo FROM SysTypes WHERE TypeID=$TypeID";
$ErrMsg =_('The transaction type') . ' ' . $TypeID . ' ' . _('could not be retrieved');
$TypeResult = DB_query($SQL,$db,$ErrMsg);
if (DB_num_rows($TypeResult)==0){
        prnMsg(_('No transaction type is defined for type ') . $TypeID,'error');
	include('includes/footer.inc');
	exit;
}

$myrow = DB_fetch_row($TypeResult);
$TransName = $myrow[0];
if ($myrow[1]<$TransNo){
	prnMsg(_('The transaction number the script was called with is requesting a') . ' ' . $TransName . ' ' . _('beyond the last one entered'),'error');
	include('includes/footer.inc');
	exit;
}


/*	first let's see if this is the last pass; if so-- insert	*/
if (isset($_POST['Post'])) {
	$SQL = "BEGIN";
        $UPChequesResult = DB_query($SQL, $db, $ErrMsg);

	$SQL = $_POST['sql2do1'];
        $ErrMsg = _('The debit trans failed...');
        $r1= DB_query($SQL, $db, $ErrMsg);
        echo $SQL . " <BR> ";

        $SQL = $_POST['sql2do2'];
        $ErrMsg = _('The debit trans failed...');
        $r2= DB_query($SQL, $db, $ErrMsg);
        echo $SQL . " <BR> ";

        if ( $r1 && $r2 ) {
		echo $SQL = "COMMIT";
	} else {
		echo $SQL = "ROLLBACK";
	}
        $UPChequesResult = DB_query($SQL, $db, $ErrMsg);

        include('includes/footer.inc');
        exit;
}


echo '<CENTER><TABLE CELLPADDING=2 width=80%>';

$TableHeader = '<TR><TD class="tableheader">' . _('Date') . '</TD>
			<TD class="tableheader">' . _('Period') .'</TD>
			<TD class="tableheader">'. _('Account') .'</TD>
			<TD class="tableheader">'. _('Amount') .'</TD>
			<TD class="tableheader">' . _('Change') .'</TD>
			<TD class="tableheader">' . _('Narrative') .'</TD>
			<TD class="tableheader">'. _('Posted') . '</TD></TR>';

echo $TableHeader;
$j = 1;

$sql = 'SELECT * FROM GLTrans WHERE Type=' . $TypeID . '  AND TypeNo=' . $TransNo . ' AND Account IN (11000,22000) ORDER BY Account';
echo '<br>' . $sql . '<br>';
$ret = DB_query(  $sql, $db  );
while ($myrow=DB_fetch_array($ret)) {
        echo '<tr bgcolor="#CCCCCC">';
	switch ($myrow['Account']) {
		case 11000: 	$change = - $myrow['Amount'];
                		Input_Hidden("Prd",$myrow['PeriodNo']);
                		Input_Hidden("TranDate",$myrow['TranDate']);
                		Input_Hidden("CR_Account",$myrow['Account']);
				Input_Hidden("CR_Ref",$myrow['Narrative']);
	       			$FormatedTranDate = ConvertSQLDate($myrow["TranDate"]);
	       			printf ('<td>%s</td>
       					<td ALIGN=RIGHT>%s</td>
					<td>%s</td>
					<td ALIGN=RIGHT>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>',	
					$FormatedTranDate,
					$myrow['PeriodNo'],
					$myrow['Account'],
	        		        number_format($myrow['Amount'],2),
					$change,
					$myrow['Narrative'],
					$Posted);
				$sql_11000 = 'UPDATE GLTrans SET Amount=Amount + (' . $change . ') WHERE CounterIndex = ' . $myrow['CounterIndex'];
				break;
		case 22000:	
                                Input_Hidden("Prd",$myrow['PeriodNo']);
                                Input_Hidden("TranDate",$myrow['TranDate']);
                                Input_Hidden("CR_Account",$myrow['Account']);
                                Input_Hidden("CR_Ref",$myrow['Narrative']);
                                $FormatedTranDate = ConvertSQLDate($myrow["TranDate"]);
                                printf ('<td>%s</td>
                                        <td ALIGN=RIGHT>%s</td>
                                        <td>%s</td>
                                        <td ALIGN=RIGHT>%s</td>
                                        <td>%s</td>
                                        <td>%s</td>
                                        <td>%s</td>
                                        </tr>',
                                        $FormatedTranDate,
                                        $myrow['PeriodNo'],
                                        $myrow['Account'],
                                        number_format($myrow['Amount'],2),
					-$change,
                                        $myrow['Narrative'],
                                        $Posted);
				$sql_22000 = 'UPDATE GLTrans SET Amount=Amount - (' . $change . ') WHERE CounterIndex = ' . $myrow['CounterIndex'];
                                break;

	}
}
//end of while loop

echo '</TABLE></CENTER>';
echo $sql_11000 . '<br>' . $sql_22000 . '<br>';
unset($_POST['y']);
Input_Submit("Recheck",'Just check');
Input_Submit("Post",'Post');
Input_Hidden('y',$sum);
Input_Hidden('sql2do1', $sql_11000 );
Input_Hidden('sql2do2', $sql_22000 );
echo '</FORM>';
include('includes/footer.inc');

?>
