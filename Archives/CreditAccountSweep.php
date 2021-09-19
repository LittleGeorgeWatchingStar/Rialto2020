<?php
/* $Revision: 1.5 $ */
include('includes/DefineJournalClass.php');

use Rialto\SecurityBundle\Entity\User;
$PageSecurity = 10;
include('includes/session.inc');
$title = _('Credit Card Sweep Transactions');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

if ($_GET['NewJournal']=='Yes' AND isset($_SESSION['JournalDetail'])){
	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);
}

if (!isset($_SESSION['JournalDetail'])){
	$_SESSION['JournalDetail'] = new Journal;

	/* Make an array of the defined bank accounts - better to make it now than do it each time a line is added
	Journals cannot be entered against bank accounts GL postings involving bank accounts must be done using
	a receipt or a payment transaction to ensure a bank trans is available for matching off vs statements */

	$SQL = 'SELECT AccountCode FROM BankAccounts';
	$result = DB_query($SQL,$db);
	$i=0;
	while ($Act = DB_fetch_row($result)){
		$_SESSION['JournalDetail']->BankAccounts[$i]= $Act[0];
		$i++;
	}
}

if (isset($_POST['JournalProcessDate'])){
	$_SESSION['JournalDetail']->JnlDate=$_POST['JournalProcessDate'];

	if (!Is_Date($_POST['JournalProcessDate'])){
		prnMsg(_('The date entered was not valid please enter the date to process the journal in the format'). $DefaultDateFormat,'warn');
		$_POST['CommitBatch']='Do not do it the date is wrong';
	}
}

$_POST['JournalType']='Normal';
$_SESSION['JournalDetail']->JournalType = $_POST['JournalType'];
$msg='';

if ($_POST['CommitBatch']==_('Accept and Process Journal')){

 /* once the GL analysis of the journal is entered
  process all the data in the session cookie into the DB
  A GL entry is created for each GL entry
*/

	$PeriodNo = GetPeriod($_SESSION['JournalDetail']->JnlDate,$db);


     /*Start a transaction to do the whole lot inside */
	$result = DB_query('BEGIN',$db);

	$TransNo = GetNextTransNo( 0, $db);

	foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
		$SQL = 'INSERT INTO GLTrans (Type,
						TypeNo,
						TranDate,
						PeriodNo,
						Account,
						Narrative,
						Amount) ';
		$SQL= $SQL . 'VALUES (0,
					' . $TransNo . ",
					'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
					" . $PeriodNo . ",
					" . $JournalItem->GLCode . ",
					'" . $JournalItem->Narrative . "',
					" . $JournalItem->Amount . ")";
		$ErrMsg = _('Cannot insert a GL entry for the journal line because');
		$DbgMsg = _('The SQL that failed to insert the GL Trans record was');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	}

	$SQL =		" INSERT INTO BankTrans (Type, TransNo, BankAct,Ref,BankTransType,Amount,TransDate) "; 
	$SQL= $SQL .	" VALUES (12,'$TransNo','10200','Credit card sweep','ACH','" . $_POST['totalsweep'] . "','" . 
			FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "')  ";
	$ErrMsg = _('Cannot insert a GL entry for the journal line because');
	$DbgMsg = _('The SQL that failed to insert the GL Trans record was');
	$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	$ErrMsg = _('Cannot commit the changes');
	$result= DB_query('COMMIT',$db,$ErrMsg,_('The commit database transaction failed'),true);

	prnMsg(_('Journal').' ' . $TransNo . ' '._('has been sucessfully entered'),'success');

	unset($_POST['JournalProcessDate']);
	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);

	/*Set up a newy in case user wishes to enter another */
	echo "<BR><A HREF='" . $_SERVER['PHP_SELF'] . '?' . SID . "NewJournal=Yes'>"._('Enter another sweep transaction.').'</A>';
	/*And post the journal too */
	include('includes/GLPostings.inc');
        include('includes/footer.inc');
	exit;

} elseif (isset($_GET['Delete'])){

  /* User hit delete the line from the journal */
   $_SESSION['JournalDetail']->Remove_GLEntry($_GET['Delete']);

} elseif (isset($_POST['Restart'])) {
        unset($_POST['JournalProcessDate']);
        unset($_SESSION['JournalDetail']->GLEntries);
        unset($_SESSION['JournalDetail']);
        /*Set up a newy in case user wishes to enter another */
        echo "<BR><A HREF='" . $_SERVER['PHP_SELF'] . '?' . SID . "NewJournal=Yes'>"._('Try again').'</A>';
	include('includes/footer.inc');
       exit; 
} 

if (isset($Cancel)){
   unset($_POST['GLAmount']);
   unset($_POST['GLCode']);
   unset($_POST['AccountName']);
}

echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . ' METHOD=POST>';
echo '<P><TABLE BORDER=1 WIDTH=100%>';


	echo '<TR><TD VALIGN=TOP WIDTH=30%><TABLE>'; // A new table in the first column of the main table

if (!Is_Date($_SESSION['JournalDetail']->JnlDate)){
	// Default the date to the last day of the previous month
	$_SESSION['JournalDetail']->JnlDate = Date($DefaultDateFormat,mktime(0,0,0,date('m'),0,date('Y')));
}

echo '<TR><TD>'._('Date to Process Journal').":</TD>
	<TD><INPUT TYPE='text' name='JournalProcessDate' maxlength=10 size=11 value='" . $_SESSION['JournalDetail']->JnlDate . "'></TD></TR>";


echo "<TR><TD>VISA</TD><TD><INPUT TYPE='TEXT' NAME='GLAmount_VISA' VALUE='" . $_POST['GLAmount_VISA'] . "'></TD></TR>";
echo "<TR><TD>MC  </TD><TD><INPUT TYPE='TEXT' NAME='GLAmount_MC' VALUE='" .   $_POST['GLAmount_MC'] . "'></TD></TR>";
echo "<TR><TD>AMEX</TD><TD><INPUT TYPE='TEXT' NAME='GLAmount_AMEX' VALUE='" . $_POST['GLAmount_AMEX'] . "'></TD></TR>";
echo "<TR><TD>COST</TD><TD><INPUT TYPE='TEXT' NAME='GLAmount_COST' VALUE='" . $_POST['GLAmount_COST'] . "'></TD></TR>";

$_SESSION['JournalDetail']->add_to_glanalysis($_POST['GLAmount_VISA'], 'Visa sweep', '10200',  'SVB Checking'); 
$_SESSION['JournalDetail']->add_to_glanalysis($_POST['GLAmount_MC'],   'MC sweep',   '10200',  'SVB Checking');
$_SESSION['JournalDetail']->add_to_glanalysis($_POST['GLAmount_AMEX'], 'AmEx sweep', '10200',  'SVB Checking');
$totalsweep  = $_POST['GLAmount_VISA'] + $_POST['GLAmount_MC'] + $_POST['GLAmount_AMEX'];
$_SESSION['JournalDetail']->add_to_glanalysis(  -$totalsweep,                         'CC Sweep',   '10600',  'Authorize.net');

$_SESSION['JournalDetail']->add_to_glanalysis( $_POST['GLAmount_COST'], 'CC Costs',   '10200',  'SVB Checking');
$_SESSION['JournalDetail']->add_to_glanalysis(-$_POST['GLAmount_COST'],                         'CC Sweep',   '10600',  'Authorize.net');
echo "<input type='hidden' name='totalsweep' value='$totalsweep'>";

echo '</TABLE>';
echo '</TD>';

echo "<TD>";
if ($totalsweep==0) {
	echo "<INPUT TYPE=SUBMIT NAME='Process' value='Process'>";
} else {
        echo "<INPUT TYPE=SUBMIT NAME='Restart' value='Restart'>";
}
echo "</TD>";


echo "<TABLE WIDTH=100% BORDER=1><TR>
	<TD class='tableheader'>"._('Amount')."</TD>
	<TD class='tableheader'>"._('GL Account')."</TD>
	<TD class='tableheader'>"._('Narrative').'</TD></TR>';

foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
	echo "<TR><TD ALIGN=RIGHT>" . number_format($JournalItem->Amount,2) . "</TD>
		<TD>" . $JournalItem->GLCode . ' - ' . $JournalItem->GLActName . '</TD>
		<TD>' . $JournalItem->Narrative  . "</TD>
		<TD><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $JournalItem->ID . "'>"._('Delete').'</a></TD>
	</TR>';
}

echo '<TR><TD ALIGN=RIGHT><B>' . number_format($_SESSION['JournalDetail']->JournalTotal,2) . '</B></TD></TR></TABLE>';
echo '</TABLE>';

if (ABS($_SESSION['JournalDetail']->JournalTotal)<0.001 AND $_SESSION['JournalDetail']->GLItemCounter > 0){
	echo "<BR><BR><INPUT TYPE=SUBMIT NAME='CommitBatch' VALUE='"._('Accept and Process Journal')."'>";
} elseif(count($_SESSION['JournalDetail']->GLEntries)>0) {
	echo '<BR><BR>';
	prnMsg(_('The journal must balance ie debits equal to credits before it can be processed'),'warn');
}
echo '</form>';
include('includes/footer.inc');
?>
