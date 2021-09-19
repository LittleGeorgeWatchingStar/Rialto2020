<?php

/* $Revision: 1.3 $ */

/*Through deviousness and cunning, this system allows shows the balance sheets as at the end of any period selected - so first off need to show the input of criteria screen while the user is selecting the period end of the balance date meanwhile the system is posting any unposted transactions */

use Rialto\AccountingBundle\Entity\Period;$PageSecurity = 8;

if (isset($_POST['PrintPDF'])) {
	include('includes/session.inc');
	include('includes/SQL_CommonFunctions.inc');
	include('includes/DateFunctions.inc');
	include('includes/PDFStarter_ros.inc');
	$PageNumber = 0;
	$FontSize = 9;
	$pdf->addinfo('Title', _('Balance Sheet') );
	$pdf->addinfo('Subject', _('Balance Sheet') );
	$line_height = 11;
        $CompanyRecord = ReadInCompanyRecord($db);
        $RetainedEarningsAct = $CompanyRecord['RetainedEarnings'];
	$sql = 'SELECT lastdate_in_period FROM Periods WHERE periodno=' . $_POST['BalancePeriodEnd'];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$BalanceDate = ConvertSQLDate($myrow[0]);
	$theDate = $myrow[0];
	/*Calculate B/Fwd retained earnings */

	$SQL = 'SELECT Sum(CASE WHEN ChartDetails.period=' . $_POST['BalancePeriodEnd'] . ' THEN ChartDetails.bfwd + ChartDetails.actual ELSE 0 END) AS accumprofitbfwd,
			Sum(CASE WHEN ChartDetails.period=' . ($_POST['BalancePeriodEnd'] - 12) . " THEN ChartDetails.bfwd + ChartDetails.actual ELSE 0 END) AS lyaccumprofitbfwd
		FROM ChartMaster INNER JOIN AccountGroups
		ON ChartMaster.group_ = AccountGroups.groupname INNER JOIN ChartDetails
		ON ChartMaster.accountcode= ChartDetails.accountcode
		WHERE AccountGroups.pandl=1";

	$AccumProfitResult = DB_query($SQL,$db);
	if (DB_error_no($db) !=0) {
		$title = _('Balance Sheet') . ' - ' . _('Problem Report') . '....';
		include('includes/header.inc');
		prnMsg( _('The accumulated profits brought forward could not be calculated by the SQL because') . ' - ' . DB_error_msg($db) );
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the menu'). '</A>';
		if ($debug==1){
			echo '<BR>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$AccumProfitRow = DB_fetch_array($AccumProfitResult); /*should only be one row returned */

	$SQL = 'SELECT AccountGroups.sectioninaccounts, 
			AccountGroups.groupname,
			ChartDetails.accountcode ,
			ChartMaster.accountname,
			Sum(CASE WHEN ChartDetails.period=' . $_POST['BalancePeriodEnd'] . ' THEN ChartDetails.bfwd + ChartDetails.actual ELSE 0 END) AS balancecfwd,
			Sum(CASE WHEN ChartDetails.period=' . ($_POST['BalancePeriodEnd'] - 12) . ' THEN ChartDetails.bfwd + ChartDetails.actual ELSE 0 END) AS lybalancecfwd
		FROM ChartMaster INNER JOIN AccountGroups
		ON ChartMaster.group_ = AccountGroups.groupname INNER JOIN ChartDetails
		ON ChartMaster.accountcode= ChartDetails.accountcode
		WHERE AccountGroups.pandl=0
		GROUP BY AccountGroups.groupname,
			ChartDetails.accountcode,
			ChartMaster.accountname,
			AccountGroups.sequenceintb,
			AccountGroups.sectioninaccounts
		ORDER BY AccountGroups.sectioninaccounts, 
			AccountGroups.sequenceintb, 
			ChartDetails.accountcode';

	$AccountsResult = DB_query($SQL,$db);
	if (DB_error_no($db) !=0) {
		$title = _('Balance Sheet') . ' - ' . _('Problem Report') . '....';
		include('includes/header.inc');
		prnMsg( _('No general ledger accounts were returned by the SQL because') . ' - ' . DB_error_msg($db) );
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the menu'). '</A>';
		if ($debug==1){
			echo '<BR>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	include('includes/PDFBalanceSheetPageHeader.inc');
	
	$k=0; //row colour counter
	$Section='';
	$SectionBalance = 0;
	$SectionBalanceLY = 0;

	$LYCheckTotal = 0;
	$CheckTotal = 0;

	$ActGrp ='';

	$GroupTotal = 0;
	$LYGroupTotal = 0;

	while ($myrow=DB_fetch_array($AccountsResult)) {

		$AccountBalance = $myrow['balancecfwd'];
		$LYAccountBalance = $myrow['lybalancecfwd'];

		if ($myrow['accountcode'] == $RetainedEarningsAct){
			$AccountBalance += $AccumProfitRow['accumprofitbfwd'];
			$LYAccountBalance += $AccumProfitRow['lyaccumprofitbfwd'];
		}

		if ($myrow['groupname']!= $ActGrp AND $_POST['Detail']=='Summary' AND $ActGrp != '') {
			$YPos -= (1 * $line_height);
			$FontSize = 8;
			$pdf->selectFont(Fonts::find('Helvetica'));
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$ActGrp);
			$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format(($Section=='10' ? 1:-1 ) * $GroupTotal),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format(($Section=='10' ? 1:-1 ) * $LYGroupTotal),'right');
			$YPos -= $line_height;
		}
		if ($myrow['sectioninaccounts']!= $Section){

			if ($SectionBalanceLY+$SectionBalance !=0){
				$FontSize = 8;
				$pdf->selectFont(Fonts::find('Helvetica-Bold'));
				$LeftOvers = $pdf->addTextWrap($Left_Margin+10,$YPos,200,$FontSize,$Sections[$Section]);
				$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format( ($Section=='10' ? 1:-1 ) * $SectionBalance),'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format( ($Section=='10' ? 1:-1 ) * $SectionBalanceLY),'right');
				$YPos -= (1 * $line_height);
			}
			$SectionBalanceLY = 0;
			$SectionBalance = 0;

			$Section = $myrow['sectioninaccounts'];
			if ($_POST['Detail']=='Detailed'){
				$FontSize = 9;
				$pdf->selectFont(Fonts::find('Helvetica'));
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$Sections[$myrow['sectioninaccounts']]);
				$YPos -= (1 * $line_height);
			}
		}

		if ($myrow['groupname']!= $ActGrp){

			if ($_POST['Detail']=='Detailed'){
				$ActGrp = $myrow['groupname'];
				$FontSize = 8;
				$pdf->selectFont(Fonts::find('Helvetica'));
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$myrow['groupname']);
				$YPos -= $line_height;
			}
			$GroupTotal=0;
			$LYGroupTotal=0;
			$ActGrp = $myrow['groupname'];
			$YPos -= $line_height;				// Added an extra line after Group Names.
		}

		$SectionBalanceLY +=	$LYAccountBalance;
		$SectionBalance	  +=	$AccountBalance;

		$LYGroupTotal	  +=	$LYAccountBalance;
		$GroupTotal	  +=	$AccountBalance;

		$LYCheckTotal 	  +=	$LYAccountBalance;
		$CheckTotal  	  +=	$AccountBalance;


		if ($_POST['Detail']=='Detailed'){
			$FontSize = 8;
			$pdf->selectFont(Fonts::find('Helvetica'));
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,$myrow['accountcode']);
			$LeftOvers = $pdf->addTextWrap($Left_Margin+55,$YPos,200,$FontSize,$myrow['accountname']);
			$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format( ($myrow['sectioninaccounts']=='10' ? 1:-1 ) *  $AccountBalance),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format( ($myrow['sectioninaccounts']=='10' ? 1:-1 ) *  $LYAccountBalance),'right');
			$YPos -= $line_height;
		}
		if ($YPos < ($Bottom_Margin)){
			include('includes/PDFBalanceSheetPageHeader.inc');
		}
	}//end of loop

	if ($SectionBalanceLY+$SectionBalance !=0){
		if ($_POST['Detail']=='Summary'){
			$YPos -= (1 * $line_height);
			$FontSize = 8;
			$pdf->selectFont(Fonts::find('Helvetica'));
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$ActGrp);
			$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format(($Section=='10' ? 1:-1 ) * $GroupTotal),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format(($Section=='10' ? 1:-1 ) * $LYGroupTotal),'right');
			$YPos -= $line_height;
		}

		$FontSize = 8;
		$pdf->selectFont(Fonts::find('Helvetica-Bold'));
		$LeftOvers = $pdf->addTextWrap($Left_Margin+10,$YPos,200,$FontSize,$Sections[$Section]);
		$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format(($Section=='10' ? 1:-1 ) * $SectionBalance),'right');
		$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format(($Section=='10' ? 1:-1 ) * $SectionBalanceLY),'right');
		$YPos -= $line_height;
	}
	
	$YPos -= $line_height;
	$FontSize = 8;
	$pdf->selectFont(Fonts::find('Helvetica'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,'Check Total');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format($CheckTotal),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format($LYCheckTotal),'right');

	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);
	
	if ($len<=20){
		$title = _('Print Balance Sheet Error');
		include('includes/header.inc');
		echo '<p>';
		prnMsg( _('There were no entries to print out for the selections specified') );
		echo '<BR><A HREF="'. $rootpath.'/index.php?' . SID . '">'. _('Back to the menu'). '</A>';
		include('includes/footer.inc');
		exit;
	} else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=CustomerList.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$pdf->Stream();

	}
	exit;
}

include ('includes/session.inc');
$title = _('Balance Sheet');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');


echo "<FORM METHOD='POST' ACTION=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';


if (! isset($_POST['BalancePeriodEnd']) OR isset($_POST['SelectADifferentPeriod'])){
	if (! isset($_POST['BalancePeriodEnd'])) {
		$_POST['BalancePeriodEnd'] = GetPeriod( Date($DefaultDateFormat),$db);
	echo $_POST['BalancePeriodEnd'];
	}

/*Show a form to allow input of criteria for TB to show */
	echo '<CENTER><TABLE><TR><TD>'._('Select the balance date').":</TD><TD><SELECT Name='BalancePeriodEnd'>";

	$sql = 'SELECT PeriodNo, LastDate_In_Period FROM Periods';
	$Periods = DB_query($sql,$db);


	while ($myrow=DB_fetch_array($Periods,$db)){
		if( $_POST['BalancePeriodEnd']== $myrow['PeriodNo']){
			echo '<OPTION SELECTED VALUE=' . $myrow['PeriodNo'] . '>' . ConvertSQLDate($myrow['LastDate_In_Period']);
		} else {
			echo '<OPTION VALUE=' . $myrow['PeriodNo'] . '>' . ConvertSQLDate($myrow['LastDate_In_Period']);
		}
	}

	echo '</SELECT></TD></TR>';

	echo '<TR><TD>'._('Detail Or Summary').":</TD><TD><SELECT Name='Detail'>";
		echo "<OPTION SELECTED VALUE='Summary'>"._('Summary');
		echo "<OPTION SELECTED VALUE='Detailed'>"._('All Accounts');
	echo '</SELECT></TD></TR>';

	echo '</TABLE>';

	echo "<INPUT TYPE=SUBMIT Name='ShowBalanceSheet' Value='"._('Show Balance Sheet')."'></CENTER>";

/*Now do the posting while the user is thinking about the period to select */

	include ('includes/GLPostings.inc');

} else {

	echo "<INPUT TYPE=HIDDEN NAME='BalancePeriodEnd' VALUE=" . $_POST['BalancePeriodEnd'] . '>';

	$CompanyRecord = ReadInCompanyRecord($db);
	$RetainedEarningsAct = $CompanyRecord['RetainedEarnings'];

	$sql = 'SELECT LastDate_in_Period FROM Periods WHERE PeriodNo=' . $_POST['BalancePeriodEnd'];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$BalanceDate = ConvertSQLDate($myrow[0]);
        $theDate = $myrow[0];

	/*Calculate B/Fwd retained earnings */

	$SQL = 'SELECT Sum(CASE WHEN ChartDetails.Period=' . $_POST['BalancePeriodEnd'] . ' THEN ChartDetails.BFwd + ChartDetails.Actual ELSE 0 END) AS AccumProfitBFwd,
			Sum(CASE WHEN ChartDetails.Period=' . ($_POST['BalancePeriodEnd'] - 12) . " THEN ChartDetails.BFwd + ChartDetails.Actual ELSE 0 END) AS LYAccumProfitBFwd
		FROM ChartMaster INNER JOIN AccountGroups
		ON ChartMaster.Group_ = AccountGroups.GroupName INNER JOIN ChartDetails
		ON ChartMaster.AccountCode= ChartDetails.AccountCode
		WHERE AccountGroups.PandL=1";

	$AccumProfitResult = DB_query($SQL,$db,_('The accumulated profits brought forward could not be calculated by the SQL because'));

	$AccumProfitRow = DB_fetch_array($AccumProfitResult); /*should only be one row returned */

	$SQL = 'SELECT AccountGroups.SectionInAccounts, AccountGroups.GroupName,
			ChartDetails.AccountCode ,
			ChartMaster.AccountName,
			Sum(CASE WHEN ChartDetails.Period=' . $_POST['BalancePeriodEnd'] . ' THEN ChartDetails.BFwd + ChartDetails.Actual ELSE 0 END) AS BalanceCFwd,
			Sum(CASE WHEN ChartDetails.Period=' . ($_POST['BalancePeriodEnd'] - 12) . ' THEN ChartDetails.BFwd + ChartDetails.Actual ELSE 0 END) AS LYBalanceCFwd
		FROM ChartMaster INNER JOIN AccountGroups
		ON ChartMaster.Group_ = AccountGroups.GroupName INNER JOIN ChartDetails
		ON ChartMaster.AccountCode= ChartDetails.AccountCode
		WHERE AccountGroups.PandL=0
		GROUP BY AccountGroups.GroupName,
			ChartDetails.AccountCode,
			ChartMaster.AccountName
		ORDER BY AccountGroups.SectionInAccounts, AccountGroups.SequenceInTB, ChartDetails.AccountCode';

	$AccountsResult = DB_query($SQL,$db,_('No general ledger accounts were returned by the SQL because'));

	echo '<CENTER><FONT SIZE=4 COLOR=BLUE><B>'._('Balance Sheet as at')." $BalanceDate</B></FONT><BR>";


	$postingSql = 'SELECT SUM(BankStatements.Amount) FROM BankStatements
			LEFT JOIN BankTrans ON (
                                ( BankTrans.BankTransID = BankStatements.BankTransID ) OR
                                ( BankStatements.BankTransID LIKE CONCAT( "%,", BankTrans.BankTransID)  ) OR
				( BankStatements.BankTransID LIKE CONCAT( BankTrans.BankTransID, ",%")  ) )
			WHERE 	BankTrans.TransDate <= "' . $theDate . '" 
				AND BankStatements.BankPostDate > "' . $theDate . '"';
	$postingRes = DB_fetch_row( DB_query( $postingSql, $db ));
	/* echo $postingSql .*/ ( $thePost = $postingRes[0]);	


	echo '<TABLE CELLPADDING=2>';

	if ($_POST['Detail']=='Detailed'){
		$TableHeader = "<TR>
				<TD class='tableheader'>"._('Account')."</TD>
				<TD class='tableheader'>"._('Account Name')."</TD>
				<TD COLSPAN=2 class='tableheader' ALIGN=CENTER>$BalanceDate</TD>
				<TD COLSPAN=2 class='tableheader' ALIGN=CENTER>"._('Last Year').'</TD>
				</TR>';
	} else { /*summary */
		$TableHeader = "<TR>
				<TD COLSPAN=2 class='tableheader'></TD>
				<TD COLSPAN=2 class='tableheader' ALIGN=CENTER>$BalanceDate</TD>
				<TD COLSPAN=2 class='tableheader' ALIGN=CENTER>"._('Last Year').'</TD>
				</TR>';
	}


	$k=0; //row colour counter
	$Section='';
	$SectionBalance = 0;
	$SectionBalanceLY = 0;

	$LYCheckTotal = 0;
	$CheckTotal = 0;

	$ActGrp ='';

	$GroupTotal = 0;
	$LYGroupTotal = 0;

	while ($myrow=DB_fetch_array($AccountsResult)) {

		$AccountBalance = $myrow['BalanceCFwd'];
		$LYAccountBalance = $myrow['LYBalanceCFwd'];

		if ($myrow['AccountCode'] == $RetainedEarningsAct){
			$AccountBalance += $AccumProfitRow['AccumProfitBFwd'];
			$LYAccountBalance += $AccumProfitRow['LYAccumProfitBFwd'];
		}

		if ($myrow['GroupName']!= $ActGrp AND $_POST['Detail']=='Summary' AND $ActGrp != '') {

			printf('<td COLSPAN=3>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<TD></TD>
			<td ALIGN=RIGHT>%s</td>
			</tr>',
			$ActGrp,
			number_format( ($Section=='10' ? 1:-1 ) * $GroupTotal),
			number_format( ($Section=='10' ? 1:-1 ) * $LYGroupTotal)
			);

		}
		if ($myrow['SectionInAccounts']!= $Section){

			if ($SectionBalanceLY+$SectionBalance !=0){
				if ($_POST['Detail']=='Detailed'){
					echo '<TR>
					<TD COLSPAN=2></TD>
      					<TD><HR></TD>
					<TD></TD>
					<TD><HR></TD>
					<TD></TD>
					</TR>';
				} else {
					echo '<TR>
					<TD COLSPAN=3></TD>
      					<TD><HR></TD>
					<TD></TD>
					<TD><HR></TD>
					</TR>';
				}

				printf('<TR>
					<TD COLSPAN=3><FONT SIZE=4>%s</FONT></td>
					<TD ALIGN=RIGHT>%s</TD>
					<TD></TD>
					<TD ALIGN=RIGHT>%s</TD>
				</TR>',
				$Sections[$Section],
				number_format( ($Section==10 ? 1 : -1) *  $SectionBalance),
				number_format( ($Section==10 ? 1 : -1) * $SectionBalanceLY));
			}
			$SectionBalanceLY = 0;
			$SectionBalance = 0;

			$Section = $myrow['SectionInAccounts'];

			if ($_POST['Detail']=='Detailed'){
				printf('<TR>
					<TD COLSPAN=6><FONT SIZE=4 COLOR=BLUE><B>%s</B></FONT></TD>
					</TR>',
					$Sections[$myrow['SectionInAccounts']]);
			}
		}

		if ($myrow['GroupName']!= $ActGrp){

			if ($_POST['Detail']=='Detailed'){
				$ActGrp = $myrow['GroupName'];
				printf('<TR>
				</TR><TR>
				<td COLSPAN=6><FONT SIZE=2 COLOR=BLUE><B>%s</B></FONT></TD>
				</TR>',
				$myrow['GroupName']);
				echo $TableHeader;
			}
			$GroupTotal=0;
			$LYGroupTotal=0;
			$ActGrp = $myrow["GroupName"];
		}

		$SectionBalanceLY +=	$LYAccountBalance;
		$SectionBalance	  +=	$AccountBalance;

		$LYGroupTotal	  +=	$LYAccountBalance;
		$GroupTotal	  +=	$AccountBalance;

		$LYCheckTotal 	  +=	$LYAccountBalance;
		$CheckTotal  	  +=	$AccountBalance;


		if ($_POST['Detail']=='Detailed'){

			if ($k==1){
				echo "<tr bgcolor='#CCCCCC'>";
				$k=0;
			} else {
				echo "<tr bgcolor='#EEEEEE'>";
				$k++;
			}

			$ActEnquiryURL = "<A HREF='$rootpath/GLAccountInquiry.php?" . SID . "Period=" . $_POST['BalancePeriodEnd'] . '&Account=' . $myrow['AccountCode'] . "'>" . $myrow['AccountCode'] . '<A>';

			$PrintString = '<td>%s</td>
					<td>%s</td>
					<td ALIGN=RIGHT>%s</td>
					<TD></TD>
					<td ALIGN=RIGHT>%s</td>
					<td></td>
					</tr>';
			printf($PrintString, 
				$ActEnquiryURL,
				$myrow['AccountName'],
				number_format( ($myrow['SectionInAccounts']=='10' ? 1:-1 ) *   $AccountBalance),
				number_format( ($myrow['SectionInAccounts']=='10' ? 1:-1 ) *   $LYAccountBalance)
				);
		}
	}
	//end of loop


	if ($SectionBalanceLY+$SectionBalance !=0){
		if ($_POST['Detail']=='Summary'){
			printf('<td COLSPAN=3>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<TD></TD>
				<td ALIGN=RIGHT>%s</td>
				</tr>',
			$ActGrp,
			number_format(-$GroupTotal),
			number_format(-$LYGroupTotal)
			);
		}
		echo "<TR>
			<TD COLSPAN=3></TD>
      			<TD><HR></TD>
			<TD></TD>
			<TD><HR></TD>
			</TR>";

		printf('<TR>
			<TD COLSPAN=3><FONT SIZE=4>%s</FONT></td>
			<TD ALIGN=RIGHT>%s</TD>
			<TD></TD>
			<TD ALIGN=RIGHT>%s</TD>
			</TR>',
			$Sections[$Section],
			number_format(-$SectionBalance),
			number_format(-$SectionBalanceLY));
	}

	echo '<TR>
		<TD COLSPAN=3></TD>
      		<TD><HR></TD>
		<TD></TD>
		<TD><HR></TD>
		</TR>';

	printf('<TR>
		<TD COLSPAN=3>'._('Check Total').'</FONT></td>
		<TD ALIGN=RIGHT>%s</TD>
		<TD></TD>
		<TD ALIGN=RIGHT>%s</TD>
		</TR>',
		number_format($CheckTotal),
		number_format($LYCheckTotal));

	echo '<TR>
		<TD COLSPAN=3></TD>
      		<TD><HR></TD>
		<TD></TD>
		<TD><HR></TD>
		</TR>';

	echo '</TABLE>';
	echo "<INPUT TYPE=SUBMIT Name='SelectADifferentPeriod' Value='"._('Select A Different Balance Date')."'></CENTER>";
	echo "<INPUT TYPE=SUBMIT Name='PrintPDF'               Value='"._('PrintPDF')."'></CENTER>";
        echo "<INPUT TYPE=HIDDEN NAME='BalancePeriodEnd' VALUE=" . $_POST['BalancePeriodEnd'] . ">";
        echo "<INPUT TYPE=HIDDEN NAME='Detail' VALUE=" . $_POST['Detail'] . '>';
}
echo '</form>';
include('includes/footer.inc');
?>
