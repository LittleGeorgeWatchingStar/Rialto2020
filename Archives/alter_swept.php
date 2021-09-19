<?php
/* $Revision: 1.4 $ */

$PageSecurity = 7;
include ('includes/session.inc');
$title = _('Alter CardTrans Amounts');

include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/CommonGumstix.inc');
include('includes/WO_ui_input.inc');

echo '<FORM METHOD="POST" ACTION="' . $_SERVER["PHP_SELF"] . '?' . SID . '">';

//	CHOOSE THE DATE AT ISSUE
echo '<CENTER>';
Input_Text( 'From Date', 'TransDate', isset($_POST['TransDate'] ) ? $_POST['TransDate'] : Date('Y-m-d') );
Input_Submit( 'Go', 'Go' );
echo '</CENTER>';

echo '<CENTER><TABLE border=1>';
echo '<tr><th>ID</th><th>Date</th><th>Amount</th><th>CardType</th><th>Select</th><th>Total</th><th>OvAmount</th><th>OvFreight</th><th>OvGST</th></tr>';

$sql = 'SELECT * FROM CardTrans WHERE PostDate ="' . $_POST['TransDate'] . '"';
$res = DB_query( $sql, $db );
$running_total = 0;
while ( $this_card_tran = DB_fetch_array( $res ) ) {
	switch ($this_card_tran['CardID']) {
		case 'VISA': 
		case 'MCRD': $card_def_sql = ' AND Ref LIKE "Sweep VIMC%" '; break;
		case 'AMEX': $card_def_sql = ' AND Ref LIKE "Sweep AmEx%" '; break;
	}
	$findSQL =  'SELECT * FROM BankTrans WHERE Type=102 AND TransDate="' . $this_card_tran['PostDate'] . '" ' . $card_def_sql . ';';
	$bank_from_res = DB_fetch_array ( DB_query ( $findSQL, $db ) );
	//      GET THE INFORMATION ABOUT THE GLTRANS FOR THAT SWEEP
	$findSQL= 'SELECT * FROM GLTrans WHERE ACCOUNT=10600 AND Type=  ' . $bank_from_res['Type'] . ' AND TypeNo=   ' . $bank_from_res['TransNo'] . ';';
	$gl_from_resDR = DB_fetch_array ( DB_query ( $findSQL, $db ) );
	$findSQL='SELECT * FROM GLTrans WHERE ACCOUNT=10200 AND Type=  ' . $bank_from_res['Type'] . ' AND TypeNo=   ' . $bank_from_res['TransNo'] . ';';
	$gl_from_resCR = DB_fetch_array ( DB_query ( $findSQL, $db ) );
	$findSQL='SELECT * FROM DebtorTrans WHERE Type=12 AND TransNo = ' .  $this_card_tran['TransNo'];
	if ( $debtortrans= DB_fetch_array ( DB_query ( $findSQL, $db ) )) {
		$findSQL_A= 'SELECT DebtorTrans.* FROM CustAllocns 
			   LEFT JOIN DebtorTrans ON DebtorTrans.ID=CustAllocns.TransID_AllocTo WHERE TransID_AllocFrom  = '.$debtortrans['ID'];
		$invoicetrans= DB_fetch_array ( DB_query ( $findSQL_A, $db ) );
	}
	${'running_total_' . $this_card_tran['CardID']} += $this_card_tran['Amount'];
	echo '<tr>';
	echo '<td>' . $this_card_tran['CardTransID'] .  '</td>';
	echo '<td>' . $this_card_tran['PostDate'] .  '</td>';
	echo '<td>' . $this_card_tran['Amount'] .  '</td>';
	echo '<td>' . $this_card_tran['CardID'] .  '</td>';
	echo '<td>';
	Input_Check ( '', 'SELECT_' . $this_card_tran['CardTransID'], false );
	echo '</td>';
	
	//	IF THIS TRANSACTION IS SELECTED 
	//	SAVE THE ID
	//	GET A TO_DATE
	if (!check_to_bool( $_POST[ 'SELECT_' . $this_card_tran['CardTransID'] ] ) ) {
		echo '<td>' . ( $invoicetrans['OvAmount'] + $invoicetrans['OvFreight'] + $invoicetrans['OvGST']) . '</td>';
		echo '<td>' . $invoicetrans['OvAmount'] . '</td>';
		echo '<td>' . $invoicetrans['OvFreight'] . '</td>';
		if ( $invoicetrans['OvGST'] / $invoicetrans['OvAmount'] > 0.09) {
			$taxrate = '<I>' . number_format( 100* $invoicetrans['OvGST'] / $invoicetrans['OvAmount'],1). '%</i>';
		} else {
			$taxrate = '';
		}
		echo '<td>' . $invoicetrans['OvGST'] . '&nbsp;' .$taxrate .  '</td>';
	} else {
		if ( !isset( $_POST['NewOvAmount'] )) {
			$_POST['NewOvAmount'] = $invoicetrans['OvAmount'];
			$_POST['NewOvFreight'] = $invoicetrans['OvFreight'];
			$_POST['NewOvGST'] = $invoicetrans['OvGST'];
		}
		$_POST['SelectedCardTransID'] =  $this_card_tran['CardTransID'];
		echo '<td>' . ( $_POST['NewAmount'] = $_POST['NewOvAmount'] + $_POST['NewOvFreight'] + $_POST['NewOvGST'] ) . '</td>';
		TextInput_TableCells ( NULL, 'NewOvAmount', $_POST['NewOvAmount'] );
		TextInput_TableCells ( NULL, 'NewOvFreight', $_POST['NewOvFreight'] );
		TextInput_TableCells ( NULL, 'NewOvGST', $_POST['NewOvGST'] );
		$cr = '<BR>';
		$failed = false;
		if (  (  ($_POST['NewOvAmount']!=$invoicetrans['OvAmount']) OR
			 ($_POST['NewOvGST']!=$invoicetrans['OvGST']) OR 
			 ($_POST['NewOvFreight']!=$invoicetrans['OvFreight']) )  AND
		      ( $gl_from_resCR['CounterIndex'] != 0 )
		    )  {
			$ret = DB_query( 'BEGIN', $db);
	
			$FixSQL ='UPDATE CardTrans   SET Amount = "' . $_POST['NewAmount']   . '" WHERE CardTransID= ' .  $_POST['SelectedCardTransID'] . ';';
			$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  '*' . mysql_error($db); $failed=true;}

			$FixSQL ='UPDATE DebtorTrans SET OvAmount = "' . $_POST['NewAmount'] . '" WHERE Type=12 AND TransNo = ' .  $this_card_tran['TransNo'] . ';';
			$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  mysql_error($db); $failed=true;} 

			$FixSQL ='UPDATE GLTrans    SET Amount=' . round(  $_POST['NewAmount'] - $this_card_tran['Amount'],2 ) .
					' + Amount WHERE Account=10600 AND  Type = 12 AND TypeNo = ' .  $this_card_tran['TransNo'] . ';';
			$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  mysql_error($db); $failed=true;}

			$FixSQL ='UPDATE GLTrans    SET Amount=' . round( -$_POST['NewAmount'] + $this_card_tran['Amount'],2 ) . 
					' + Amount WHERE Account=22000 AND  Type = 12 AND TypeNo = ' .  $this_card_tran['TransNo'] . ';';
			$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  mysql_error($db); $failed=true;}

			$FixSQL='UPDATE BankTrans SET Amount='.round($_POST['NewAmount']-$this_card_tran['Amount'],2).'+Amount WHERE BankTransID='.$bank_from_res['BankTransID'].';' ;
			$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  mysql_error($db); $failed=true;}
			$FixSQL='UPDATE GLTrans   SET Amount='.round($_POST['NewAmount']-$this_card_tran['Amount'],2).'+Amount WHERE CounterIndex='.$gl_from_resCR['CounterIndex'].';' ;
			$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  mysql_error($db); $failed=true;}
			$FixSQL='UPDATE GLTrans SET Amount='.round(-$_POST['NewAmount']+$this_card_tran['Amount'],2).'+Amount WHERE CounterIndex='.$gl_from_resDR['CounterIndex'].';' ;
			$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  mysql_error($db); $failed=true;}
			
			if ( $invoicetrans['ID'] != '') {
				$FixSQL='UPDATE DebtorTrans SET OvAmount = "' . $_POST['NewOvAmount'] . '" WHERE ID=' . $invoicetrans['ID'] . ';';
				$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  mysql_error($db); $failed=true;}
				$FixSQL='UPDATE DebtorTrans SET OvFreight = "' . $_POST['NewOvFreight'] . '" WHERE ID=' . $invoicetrans['ID']. ';';
				$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  mysql_error($db); $failed=true;}
				$FixSQL='UPDATE DebtorTrans SET OvGST  = "' . $_POST['NewOvGST'] . '" WHERE ID=' . $invoicetrans['ID']. ';';
				$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  mysql_error($db); $failed=true;}
				$FixSQL='UPDATE GLTrans SET Amount='.   $_POST['NewAmount'] .
	                                        ' WHERE Account=22000 AND Type=10 AND TypeNo=' . $invoicetrans['TransNo']. ';';
				$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  mysql_error($db); $failed=true;} 
				$FixSQL='UPDATE GLTrans SET Amount='. ( -$_POST['NewOvFreight'] ) .
						' WHERE Account=40700 AND Type=10 AND TypeNo=' . $invoicetrans['TransNo']. ';';
				$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  mysql_error($db); $failed=true;}
				$FixSQL='UPDATE GLTrans SET Amount='. ( -$_POST['NewOvGST'] ).
						' WHERE Account=23100 AND Type=10 AND TypeNo=' . $invoicetrans['TransNo']. ';';
				$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  mysql_error($db); $failed=true;}
				$FixSQL='INSERT INTO GLTrans ' . // (CounterIndex, Type, TypeNo, ChequeNo, TranDate, PeriodNo, Account, Narrative, Amount, Posted, JobRef)
						' VALUES (' . 
						'NULL,10, "' . $invoicetrans['TransNo']. '","","' . $this_card_tran['TransDate'] . '",'.$gl_from_resDR['PeriodNo'].','.
						'40001, "Offset an error","' . round($_POST['NewAmount'] - $this_card_tran['Amount'],2 ) . '",0,"' . $gl_from_resDR['JobRef'] . '");';
				$ret = DB_query( $FixSQL, $db ); echo '<BR>' . $FixSQL; if ( mysql_errno($db)!=0 ) { echo  mysql_error($db); $failed=true;}
			}
			//$failed = true;
			if ($failed) {
				echo '<BR>Rolling back.' .  ' <BR>';
				$ret = DB_query( 'ROLLBACK', $db);
			} else {
				$ret = DB_query( 'COMMIT', $db);
			}
		}
	}
	echo '</tr>';
}
echo '</TABLE></CENTER>';
echo '<BR><HR>';
echo $running_total_AMEX  . ', ' . ( $running_total_VISA + $running_total_MCRD ) ;
echo '<HR>';

echo  $FixSQL;
echo '<BR><HR><BR>';
include('includes/footer.inc');
?>
