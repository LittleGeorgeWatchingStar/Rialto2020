<?php
/* Format a check or checks for printing
   List of checks passed in as $_POST['BankAccount'] and $_POST['CheckNumbers'] as a string of comma-separated check numbers */
use Rialto\AccountingBundle\Entity\BankAccount;$PageSecurity = 3;
include('includes/SQL_CommonFunctions.inc');
include('config.php');
include ('includes/session.inc');
$title = _('List customer refund checks to print');
include ('includes/header.inc');

if (!isset($_POST['CheckNumberData'])) {
   echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '>';
   echo '<CENTER><TABLE>';
   echo '<TR><TD>' . _('Enter the check numbers to print, comma separated') . ":</TD>
     		<TD><INPUT TYPE=text NAME='CheckNumbers' MAXLENGTH=100 SIZE=100 VALUE='XXXXX,YYYYY,ZZZZZ,....'></TD>
	</TR>";
   echo '<TR><TD>' . _('Bank Account') . '</TD><TD>';

   $sql = "SELECT BankAccountName, AccountCode FROM BankAccounts WHERE AccountCode='10200' order by 2";
   $result = DB_query($sql,$db);

   echo "<SELECT NAME='AccountCode'>";
   $first_time = 0;
   while ($myrow=DB_fetch_array($result)){
	echo '<OPTION ' . ($first_time++ ? '' : 'SELECTED ') . 'VALUE=' . $myrow['AccountCode'] . '>' . $myrow['BankAccountName'] . '</OPTION>';
   }

   echo '</SELECT></TD></TR>';
   echo "</TABLE><BR><INPUT TYPE=SUBMIT NAME='Go' VALUE='" . _('The list') . "'>          ";
   echo '<INPUT TYPE=Submit Name="CheckNumberData" Value="' . _('All unprinted checks') . '"></CENTER>';
   exit;
}
echo "Printing: ". $_POST['CheckNumberData'] . "<BR>";
if (isset($_POST['CheckNumberData'])) {
	$SQL= " SELECT TransNo FROM BankTrans
		WHERE Printed = 0
		AND BankTrans.BankAct=" . $_POST['AccountCode'] . "
	        AND BankTrans.Type=101 ";
	$Result=DB_query($SQL,$db,'','',false,false);
	if (DB_error_no($db)!=0){
		echo "ERROR!";
		exit;
	}
	$myrow=DB_fetch_array($Result);
        $_POST['CheckNumbers'] = $myrow['TransNo'];
	while ($myrow=DB_fetch_array($Result)){
		$_POST['CheckNumbers'] .= ', '.$myrow['TransNo'];
	}
}

if ($_POST['CheckNumbers']!="") {

  $SQL= "SELECT Amount,
		BrName SuppName,
		BrAddr1 PaymentAddr1,
		BrAddr2 PaymentAddr2,
		concat(concat(concat(concat(BrCity, ', '), BrState), ' '), BrZip) CityStateZip,
		TransDate,
		TransNo
	FROM BankTrans INNER JOIN CustBranch on BankTrans.Ref = Concat(CustBranch.DebtorNo,',',CustBranch.BranchCode)
	WHERE BankTrans.BankAct=" . $_POST['AccountCode'] . "
	AND BankTrans.Type=101
	AND TransNo IN (" . $_POST['CheckNumbers'] . ")";

  $Result=DB_query($SQL,$db,'','',false,false);
  while ($myrow=DB_fetch_array($Result)) {
	echo number_format($myrow['Amount'],2). "\n";
	echo $myrow['SuppName'] .    "<BR>";
	echo $myrow['TransDate'].    "<BR>";
	echo $myrow['SuppName'].     "<BR>";
	echo $myrow['PaymentAddr1']. "<BR>";
	echo $myrow['PaymentAddr2']. "<BR>";
	echo $myrow['CityStateZip']. "<BR>";
  }
  
}  else {
   echo "No cheques to print.";
}

include ('includes/footer.inc');

?>
