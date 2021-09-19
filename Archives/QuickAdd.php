<?php
/* $Revision: 1.5 $ */

use Rialto\PurchasingBundle\Entity\Supplier;
$PageSecurity = 8;

include ("includes/session.inc");
$title = _('General Ledger Transaction QuickAdd Bank Payment');
include("includes/header.inc");
include("includes/DateFunctions.inc");
include('includes/WO_ui_input.inc');
include('includes/SQL_CommonFunctions.inc');
include_once('includes/CommonGumstix.inc');

if ( $_SESSION['UserID'] != 'gordon') {
        echo '<BR>Not for the faint of heart.<BR>';
	include ('includes/footer.inc');
	exit;
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";


/*	first let's see if this is the last pass; if so-- insert	*/
if (isset($_POST['GoAgain'])) {
	unset($_POST['Post']); 
}

if (isset($_POST['Post'])) {

	$ErrorCoded = false;

	echo "Well, when we get around to it, we will add an invoice to " . $_POST['SupplierNo'] . "<br>";

	$SQL = "BEGIN";
        $ErrMsg = _('The credit trans failed...');
        $UPChequesResult = DB_query($SQL, $db, $ErrMsg);

	$TypeID=1;
	$TransNo = GetNextTransNo($TypeID, $db);
	Input_Hidden("TypeID",$TypeID);
	Input_Hidden("TransNo",$TransNo);
	$TranDate = FormatDateForSQL($_POST['TranDate']);
	$Prd	= GetPeriod( $_POST['TranDate'], $db);
	
	if ( ($Prd<10) || ($Prd>50)) $ErrorCoded = true;

	$Amount	= -$_POST['TranAmount'];
	$Account= '10200';
	$Ref	= $_POST['DR_Ref'];

	if ($ErrorCoded) {
		echo 'You tripped an error somewhere.';
	        echo "<BR>";
	        Input_Submit("Go Again",'Go Again');
		include ('includes/footer.inc');
	        exit;
	}

	$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, Narrative, Amount, Account, PeriodNo ) VALUES
		($TypeID, $TransNo, '$TranDate', '$Ref', $Amount, $Account, $Prd )";
	echo $SQL . " <BR> ";
	$ErrMsg = _('The credit trans failed...');
	$UPChequesResult = DB_query($SQL, $db, $ErrMsg);
        $Amount= -$Amount;
        $Account =  substr($_POST['DR_Account'],0,5);
        $Ref = $_POST['DR_Ref'];
 	if ($ErrorCoded) {
		echo 'You tripped an error somewhere.';
	        echo "<BR>";
	        Input_Submit("Go Again",'Go Again');
		include ('includes/footer.inc');
	        exit;
	}
        $SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, Narrative, Amount, Account, PeriodNo ) VALUES
                ($TypeID, $TransNo, '$TranDate', '$Ref', $Amount, $Account, $Prd )";
        $ErrMsg = _('The debit trans failed...');
	$UPChequesResult = DB_query($SQL, $db, $ErrMsg);
	echo $SQL . " <BR> ";
	if ($ErrorCoded) {
		echo 'You tripped an error somewhere.';
	        echo "<BR>";
	        Input_Submit("Go Again",'Go Again');
		include ('includes/footer.inc');
	        exit;
	}

	if ($_POST['SupplierNo'] !='N/A') {
	        $Amount= -$Amount;
		$SQL = "INSERT INTO SuppTrans (Type, TransNo, SuppReference, Rate, TranDate, OvAmount ) VALUES 
			($TypeID, $TransNo,'$Ref', 1, '$TranDate', $Amount )";
	        $ErrMsg = _('The debit trans failed...');
	        $UPChequesResult = DB_query($SQL, $db, $ErrMsg);
	        echo $SQL . " <BR> ";
	}

        $Amount= -$Amount;
	$SQL = "INSERT INTO BankTrans (Type, TransNo, BankAct, Ref, ExRate, TransDate, BankTransType, Amount, CurrCode, Printed) VALUES 
		($TypeID, $TransNo,'10200', '$Ref', 1, '$TranDate', 'Direct credit', $Amount, 'USD', 0)";
        $ErrMsg = _('The debit trans failed...');
        $UPChequesResult = DB_query($SQL, $db, $ErrMsg);
        echo $SQL . " <BR> ";


        $SQL = "COMMIT";
        $ErrMsg = _('The credit trans failed...');
        $UPChequesResult = DB_query($SQL, $db, $ErrMsg);
	echo "<BR>";
	Input_Submit("Go Again",'Go Again');

        include('includes/footer.inc');
        exit;
}

echo '<TABLE>';
echo '<TR><TD>Select the account to charge:</td><TD>';
//	select the account to charge
//	Input_Option($label, $name, $choices, $selected='')
Input_Option('Account', 'DR_Account',GetAccountList($db) ,$_POST['DR_Account']);
echo '</td></tr>';

echo '<TR><TD>Select the vendor who invoied this:</td><TD>';
//      select the account to charge
//      Input_Option($label, $name, $choices, $selected='')
Input_Option('Supplier', 'SupplierNo',GetSupplierList($db) ,$_POST['SupplierNo']);
echo '</td></tr>';

//	name the date
//	DateInput_TableRow($label, $controlName, $initVal=null, $incDays=0, $incMonths=0, $incYears=0)
DateInput_TableRow('Date of transaction', 'TranDate', $_POST['TranDate']);


//	name the amount
//	TextInput_TableRow($label, $controlName, $value, $size, $maxLength, $params="", $postLabel="")
TextInput_TableRow('Amount', 'TranAmount', $_POST['TranAmount'],10, 10 );

//	give the reference
//      TextInput_TableRow($label, $controlName, $value, $size, $maxLength, $params="", $postLabel="")
TextInput_TableRow('Narrative', 'DR_Ref',$_POST['DR_Ref'], 40, 40);

echo '</TABLE>';

Input_Submit("Recheck",'Just check');
Input_Submit("Post",'Post');


echo '</FORM>';
include('includes/footer.inc');

?>
