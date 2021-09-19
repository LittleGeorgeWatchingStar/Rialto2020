<?php
/* $Revision: 1.4 $ */

$PageSecurity = 7;

include ('includes/session.inc');

$title = _('Match Statement');

include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/CommonGumstix.inc');
include('includes/WO_ui_input.inc');

echo '<FORM METHOD="POST" ACTION="' . $_SERVER["PHP_SELF"] . '?' . SID . '">';
echo '<CENTER><TABLE>';
$SQL = 'SELECT BankAccountName, AccountCode FROM BankAccounts WHERE BankAccountName LIKE "%Valley%"';
$ErrMsg = _('The bank accounts could not be retrieved by the SQL because');
$DbgMsg = _('The SQL used to retrieve the bank acconts was');
$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

echo '<tr><td>Amex Charges</td><td><INPUT type="radio" name="specials" value="AmEx"      ' . ( ($_POST['specials']=='AmEx'       ) ? 'checked':'') . "></td></tr>";
echo '<tr><td>Amex Sweeps</td><td><INPUT type="radio" name="specials" value="AmExSweeps" ' . ( ($_POST['specials']=='AmExSweeps' ) ? 'checked':'') . "></td></tr>";
echo '<tr><td>VISA/MC</td><td><INPUT type="radio" name="specials" value="VIMC"           ' . ( ($_POST['specials']=='VIMC'       ) ? 'checked':'') . "></td></tr>";

echo '<TR><TD>' . _('Bank Account') . ':</TD><TD><SELECT name="BankAccount">';
if (DB_num_rows($AccountsResults)==0){
	 echo '</SELECT></TD></TR><P>' . _('Bank Accounts have not yet been defined') . '. ' . _('You must first') . "<A HREF='" . $rootpath . "/BankAccounts.php'>" . _('define the bank accounts') . '</A>' . ' ' . _('and general ledger accounts to be affected') . '.';
	include('includes/footer.inc');
	exit;
} else {
	while ($myrow=DB_fetch_array($AccountsResults)){
		/*list the bank account names */
	        if ($_POST["BankAccount"]=='') $_POST["BankAccount"]=10200;
		if ($_POST["BankAccount"]==$myrow["AccountCode"]){
			echo '<OPTION SELECTED VALUE="' . $myrow["AccountCode"] . '">' . $myrow["BankAccountName"];
		} else {
			echo '<OPTION VALUE="' . $myrow["AccountCode"] . '">' . $myrow["BankAccountName"];
		}
	}
	echo '</SELECT></TD></TR>';
}

/*Show a form to allow input of criteria for TB to show */

	if ( !isset($_POST['TransDate']) ) {
		$_POST['TransDate'] = LastDateInThisPeriod( $db );
	}
        echo '<CENTER><TR><TD>'._('Select the balance date').":</TD><TD><SELECT Name='TransDate'>";
        $sql = 'SELECT PeriodNo, LastDate_In_Period FROM Periods ORDER BY LastDate_In_Period DESC';
        $Periods = DB_query($sql,$db);
        while ($myrow=DB_fetch_array($Periods,$db)){
                if( $_POST['TransDate'] == $myrow['LastDate_In_Period'])      {
                        echo '<OPTION SELECTED VALUE=' . $myrow['LastDate_In_Period'] . '>' . ConvertSQLDate($myrow['LastDate_In_Period']);
                } else {
                        echo '<OPTION VALUE=' . $myrow['LastDate_In_Period'] . '>' . ConvertSQLDate($myrow['LastDate_In_Period']);
                }
        }
        echo '</SELECT></TD></TR>';

/*Now do the posting while the user is thinking about the bank account to select */

include ('includes/GLPostings.inc');

DisallowDoubleChecks();

echo '</TABLE>';
echo '<INPUT TYPE=SUBMIT Name="ShowRec" Value="' . _('Show') . '">';
echo '<INPUT TYPE=SUBMIT Name="PostBalancedTransactions" Value="Post Balanced Transactions">';

if ( isset($_POST['PostBalancedTransactions']) AND $_POST['PostBalancedTransactions']!=''){
	DB_query( 'BEGIN' , $db );
	foreach ( $_POST['SQL_LIST'] as $this_sql) {
		list ( $this_bank_statement, $these_bank_trans ) = split ( '-' , $this_sql );
		echo '<br>' . $sql = 'UPDATE BankStatements SET BankTransID="' . $these_bank_trans . '" WHERE BankStatementID=' . $this_bank_statement;
		$ret = DB_query( $sql, $db );
		$this_bank_trans = split ( ',' , $these_bank_trans );
		foreach ( $this_bank_trans as $this_id ) {
			echo '<br>' . $sql = 'UPDATE BankTrans SET AmountCleared=Amount WHERE BankTransID=' . $this_id;
			$ret = DB_query( $sql, $db );
		}
	}
}


//	if (isset($_POST['ShowRec']) AND $_POST['ShowRec']!=''){

/*Get the balance of the bank account concerned */

	$sql = "SELECT Max(Period) FROM ChartDetails WHERE AccountCode=" . $_POST["BankAccount"];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$LastPeriod = $myrow[0];

	$SQL = "SELECT BFwd+Actual AS Balance FROM ChartDetails WHERE Period=$LastPeriod AND AccountCode=" . $_POST["BankAccount"];
	$ErrMsg = _('The bank account balance could not be returned by the SQL because');
	$myrow = DB_fetch_row(DB_query($SQL,$db,$ErrMsg));
	$Balance = $myrow[0];

	echo '<CENTER><TABLE WIDTH=60%>';
//	echo "<COLGROUP><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='2*'><COL WIDTH='1*'><COL WIDTH='1*'><COL WIDTH='1*'>";

	$SQL =	" SELECT * FROM BankStatements " .
		" WHERE BankTransID IN ('', '0') " .
		" AND ( ABS( 45+DateDiff(BankPostDate,'" . $_POST['TransDate'] . "')) <45 )";
	switch  ( $_POST['specials'] ) {
		case "AmEx":       $SQL .= " AND ( BankDescription LIKE '%SETTLEMENT%1040599060%' OR BankDescription LIKE '%COLLECTION%1040599060%' )";         break;
		case "AmExSweeps": $SQL .= " AND BankDescription LIKE '%AXP DISCN%1040599060%'";		break;
		case "VIMC":       $SQL .= " AND ( BankDescription LIKE '%BANKCARD DEPOSIT %' OR BankDescription LIKE '%POS CARDSYSTEMS CR CD DEP%') ";		break;
	}
	$SQL .=	" ORDER BY " .
		" SIGN(Amount), " .
		" BankPostDate DESC " ;
	$ErrMsg = _('The deposits could not be retrieved by the SQL because');
	$UPChequesResult = DB_query($SQL,$db,$ErrMsg);
        $TableHeader = '<TR>
                        <TD class="tableheader">' . _('Bank Date') . '</TD>
                        <TD class="tableheader">' . _('Type') . '</TD>
                        <TD class="tableheader">' . _('Bank Amount') . '</TD>
                        <TD class="tableheader">' . _('Link') . '</TD>
                        <TD class="tableheader">' . _('Linked Amount') . '</TD>
                        </TR>';
	echo $TableHeader;
	while ($myrow=DB_fetch_array($UPChequesResult)) {
		$sub_sql = ' SELECT * FROM BankTrans ' .
			' WHERE ( ABS( 5+DateDiff(TransDate,"' . $myrow['BankPostDate'] . '")) <5 ) ' .
			' AND  AmountCleared=0 ';
		switch  ( $_POST['specials'] ) {
			case "AmEx":	   $sub_sql .= " AND Ref LIKE 'Sweep%AmEx%' "; break;
            case "AmExSweeps": $sub_sql .= " AND Ref LIKE 'FEE%AmEx%' "; break;
            case "VIMC":	   $sub_sql .= " AND Ref LIKE '%VIMC%' "; break;
		}
		$sub_sql .= ' ORDER BY TransDate DESC ';
  		printf("<tr bgcolor='#CCCCCC'> <td>%s</td> <td>%s</td>  <td ALIGN=RIGHT>%01.2f</td></tr>",
				ConvertSQLDate($myrow["BankPostDate"]),
				$myrow["BankDescription"],
				$myrow["Amount"]
		);
		$continuation	= true;
		$this_checksum	= 0;
		$pot_sql	= array();
		$pot_matches	= array();
		$this_difference= 0;
		$sub_ret = DB_query( $sub_sql, $db);
		while ( $bank_trans = DB_fetch_array( $sub_ret ) ) {
			$this_id = $bank_trans['TransNo'] . '-' . $myrow['BankStatementID'];
			Input_Hidden('BoxesList[]', $this_id );
			printf( '<tr><td></td><td>%s</td><td ALIGN=RIGHT>%01.2f</td><td>%s</td></tr>',
				$bank_trans["Ref"] ,
				$bank_trans["Amount"],
				Input_Check_String ( null, $this_id, $_POST[$this_id]  , true )
			);
			if ( check_to_bool ( $_POST[$this_id] ) ) {
				$this_checksum += $bank_trans["Amount"];
				Input_Hidden('CheckedBoxesList[]', $this_id );
				$pot_matches[] = $bank_trans['BankTransID'];
			}
		}
		if ( count ( $pot_matches ) > 0) {
			$this_difference += $this_checksum -  $myrow["Amount"];
			$cumulative_differences += $this_checksum -  $myrow["Amount"];
			if ( abs($this_difference)> 0.024 ) {
				$pot_sql = 'NEED TO ADD SweepTrans OF ' . $cumulative_differences . ' AS CORRECTING TRANS';
			} else {
				$pot_sql  = 'UPDATE BankStatements SET BankTransID="' . join ( $pot_matches, ',' ) . '"';
				Input_Hidden( 'SQL_LIST[]',  $myrow['BankStatementID'] . '-' .  join ( $pot_matches, ',' )  );
			}
			printf( '</tr><tr bgcolor=#DDCCCC><td></td><td> %s </td><td ALIGN=RIGHT>%01.2f</td></tr>', $pot_sql, $this_checksum -  $myrow["Amount"] );
			printf( '<tr><td colspan=5></td></tr>');

		} else {
			$pot_sql = 'Differences';
			printf( '</tr><tr><td></td><td> %s </td><td ALIGN=RIGHT>%01.2f</td></tr>', $pot_sql, $this_checksum -  $myrow["Amount"] );
			printf( '<tr><td colspan=5></td></tr>');
		}
	}
	echo '</TABLE>';
echo 'Total differences were ' . $cumulative_differences;
//	}
echo '</form>';
DB_query( 'COMMIT', $db);

include('includes/footer.inc');
?>
