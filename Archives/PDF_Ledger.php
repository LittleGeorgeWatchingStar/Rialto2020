<?php
/* $Revision: 1.5 $ */
$PageSecurity = 3;
include('includes/SQL_CommonFunctions.inc');
include('includes/DateFunctions.inc');
include('config.php');

$InputError=0;
if (isset($_POST['FromDate']) AND !Is_Date($_POST['FromDate'])){
	$msg = _('The date from must be specified in the format') . ' ' . $DefaultDateFormat;
	$InputError=1;
	unset($_POST['FromDate']);
}
if (!Is_Date($_POST['ToDate']) AND isset($_POST['ToDate'])){
	$msg = _('The date to must be specified in the format') . ' ' . $DefaultDateFormat;
	$InputError=1;
	unset($_POST['ToDate']);
}

if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate'])){

     include ('includes/session.inc');
     $title = _('Ledger Listing');
     include ('includes/header.inc');

     if ($InputError==1){
	prnMsg($msg,'error');
     }

     echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '>';
     echo '<CENTER><TABLE>
     			<TR>
				<TD>' . _('Enter the date from which cheques are to be listed') . ":</TD>
				<TD><INPUT TYPE=text NAME='FromDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat) . "'></TD>
			</TR>";
     echo '<TR><TD>' . _('Enter the date to which transactions are to be listed') . ":</TD>
     		<TD><INPUT TYPE=text NAME='ToDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat) . "'></TD>
	</TR>";
     echo '<TR><TD>Ledger</TD><TD>';


     echo "<SELECT NAME='LedgerName'>";
     echo '<OPTION VALUE="SuppTrans">SuppTrans';
     echo '<OPTION VALUE="DebtorTrans">DebtorTrans';
     echo '<OPTION VALUE="GLTrans">GLTrans';
     echo '</SELECT></TD></TR>';
     echo "</TABLE><INPUT TYPE=SUBMIT NAME='Go' VALUE='" . _('Create PDF') . "'></CENTER>";


     include('includes/footer.inc');
     exit;
} else {

	include('includes/ConnectDB.inc');
}

$type_list = array();
$res = DB_query( "SELECT TypeID,TypeName FROM SysTypes", $db);
while ($row = DB_fetch_array( $res) ) {
	$type_name[$row['TypeID']] = $row['TypeName'];
}


switch ( $_POST['LedgerName'] ) {
	case 'SuppTrans':	$SQL= "SELECT * FROM SuppTrans ";
				$columns = array ( 'Type' => 'Type', 'TransNo' => 'TransNo', 'Date' => 'TranDate', 'Amount' => 'OvAmount', 'ID' => 'SupplierNo', 'Reference' => 'SuppReference', 'Comments' => 'TransText' );
				break;
	case 'DebtorTrans':	$SQL= "SELECT * FROM DebtorTrans";
                                $columns = array ( 'Type' => 'Type', 'TransNo' => 'TransNo', 'Date' => 'TranDate', 'Amount' => 'OvAmount', 'ID' => 'DebtorNo', 'Order' => 'Order_',  'Reference' => 'Reference' );
                                break;
	case 'GLTrans' :	$SQL= "SELECT * FROM GLTrans  ";
                                $columns = array ( 'Account' => 'Account' , 'Type' => 'Type', 'TransNo' => 'TypeNo', 'Date' => 'TranDate', 'Amount' => 'Amount',  'CHK#'=>'ChequeNo', 'Job ID' => 'JobRef', 'Comments' => 'Narrative' );
                                break;
	default: 		$SQL= "SELECT * FROM GLTrans  ";
                                $columns = array ( 'Type' => 'Type', 'TransNo' => 'TransNo', 'Date' => 'TranDate' , 'Reference' => 'SuppReference', 'Comments' => 'TransText', 'Amount' => 'OvAmount' );
                                break;
}

$SQL .= " WHERE TranDate >='" . FormatDateForSQL($_POST['FromDate']) . "'
	    AND TranDate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

$Result=DB_query($SQL,$db,'','',false,false);
if (DB_error_no($db)!=0){
	$title = _('Payment Listing');
	include('includes/header.inc');
	prnMsg(_('An error occurred getting the payments'),'error');
	if ($Debug==1){
        	prnMsg(_('The SQL used to get the receipt header information that failed was') . ':<BR>' . $SQL,'error');
	}
	include('includes/footer.inc');
  	exit;
} elseif (DB_num_rows($Result)==0){
	$title = _('Payment Listing');
	include('includes/header.inc');
  	prnMsg (_('There were no bank transactions found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'] . '. ' ._('Please try again selecting a different date range or account'), 'error');
	include('includes/footer.inc');
  	exit;
}

$CompanyRecord = ReadInCompanyRecord($db);

include('includes/PDFStarter_ros.inc');

/*PDFStarter_ros.inc has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addinfo('Title',_('Cheque Listing'));
$pdf->addinfo('Subject',_('Cheque listing from') . '  ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);

$line_height=10;
$PageNumber = 1;
$TotalCheques = 0;
$FontSize -=1;

include ('includes/PDF_Ledger_PageHeader.inc');
while ($myrow=DB_fetch_array($Result)){
	$i = 0;
	foreach ( $columns as $col_name => $field ) {
		switch ( $col_name ) {
			case 'Date' :			$field_width =  70;       break;
			case 'Reference':		$field_width = 250;       break;
			case 'Comments':		$field_width = 100;       break;
			case 'Type':			$field_width =  80;       break;
			case 'ID':			$field_width =  30;       break;
			default:			$field_width =  35;       break;
		}
		switch ( $col_name ) {
			case 'Type':	$LeftOvers = $pdf->addTextWrap($Left_Margin+ $i,  $YPos, $field_width - 4,$FontSize,  $type_name[$myrow[$field]], 'right'); break;
			case 'Amount':  $LeftOvers = $pdf->addTextWrap($Left_Margin+ $i,  $YPos, $field_width - 4,$FontSize,  number_format( $myrow[$field], 2) , 'right'); break;
			case 'ID':
			case 'Order':	$LeftOvers = $pdf->addTextWrap($Left_Margin+ $i,  $YPos, $field_width - 4,$FontSize,  $myrow[$field], 'right'); break;
			default: 	$LeftOvers = $pdf->addTextWrap($Left_Margin+ $i,  $YPos, $field_width,    $FontSize,  $myrow[$field], 'left'); break;
		}
		$i += $field_width;
		if ($YPos - (0 *$line_height) < $Bottom_Margin){
          		/*Then set up a new page */
              		$PageNumber++;
	      		include ('includes/PDF_Ledger_PageHeader.inc');
      		} /*end of new page header  */
	}

      $YPos -= ($line_height);
      $TotalCheques = $TotalCheques - $myrow['Amount'];

      if ($YPos - (0 *$line_height) < $Bottom_Margin){
          /*Then set up a new page */
              $PageNumber++;
	      include ('includes/PDF_Ledger_PageHeader.inc');
      } /*end of new page header  */
} /* end of while there are customer receipts in the batch to print */


$pdfcode = $pdf->output();
$len = strlen($pdfcode);
header('Content-type: application/pdf');
header('Content-Length: ' . $len);
header('Content-Disposition: inline; filename=ChequeListing.pdf');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

$pdf->stream();

if ($_POST['Email']=='Yes'){
	if (file_exists($reports_dir . '/PaymentListing.pdf')){
		unlink($reports_dir . '/PaymentListing.pdf');
	}
    	$fp = fopen( $reports_dir . '/PaymentListing.pdf','wb');
	fwrite ($fp, $pdfcode);
	fclose ($fp);

	include('includes/htmlMimeMail.php');

	$mail = new htmlMimeMail();
	$attachment = $mail->getFile($reports_dir . '/PaymentListing.pdf');
	$mail->setText(_('Please find herewith payments listing from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);
	$mail->addAttachment($attachment, 'PaymentListing.pdf', 'application/pdf');
	$mail->setFrom(array("$CompanyName <" . $CompanyRecord['Email'] . '>'));

	/* $ChkListingRecipients defined in config.php */
	$result = $mail->send($ChkListingRecipients);
}

?>
