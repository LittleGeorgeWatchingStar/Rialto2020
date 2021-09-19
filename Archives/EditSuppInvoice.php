<?php
/* $Revision: 1.12 $ */
$PageSecurity = 11;
/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include_once("includes/inventory_db.inc");   //include('manufacturing/includes/inventory_db.inc');
include('includes/WO_ui_input.inc');

$title = _('Edit Invoice');
include('includes/header.inc');

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';
echo '<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>';

if (!isset($_POST['SupplierID'] ) && ( isset($_GET['SupplierID'])))  {
	$_POST['SupplierID'] = $_GET['SupplierID'];
}

if (!isset($_POST['SuppReference'] ) && ( isset ( $_GET['SuppReference']))) {
        $_POST['SuppReference'] = $_GET['SuppReference'];
}

if (!isset($_POST['PONumber'] ) && ( isset ( $_GET['PONumber']))) {
        $_POST['PONumber'] = $_GET['PONumber'];
}

Input_Hidden( 'SupplierID', $_POST['SupplierID'] );
Input_Hidden( 'SuppReference', $_POST['SuppReference'] );
Input_Hidden( 'PONumber',$_POST['PONumber'] );

if ( check_to_bool( $_POST['APP_'.$UID])) {
	$SQL =  'UPDATE SuppInvoiceDetails SET SuppInvoiceDetails.PONumber="' . $_POST['PONumber'] . '" ' .
		' WHERE SuppInvoiceDetails.Approved=0 AND SuppInvoiceDetails.SuppReference="' . $_POST['SuppReference'] . '" AND SuppInvoiceDetails.SupplierID=' . $_POST['SupplierID'];
	echo $SQL;
} else {
	$SQL =	"SELECT SuppInvoiceDetails.LineNo, SuppInvoiceDetails.Description, SuppInvoiceDetails.Invoicing, SuppInvoiceDetails.PONumber, SuppInvoiceDetails.StockID,
			SuppInvoiceDetails.Price, SuppInvoiceDetails.Approved, SuppInvoiceDetails.SuppReference, Suppliers.SuppName,
			SuppInvoiceDetails.Total, SuppInvoiceDetails.InvoiceDate, SuppInvoiceDetails.SIDetailID, SuppInvoiceDetails.SupplierID
		 FROM SuppInvoiceDetails " .
		" LEFT JOIN Suppliers ON Suppliers.SupplierID=SuppInvoiceDetails.SupplierID " .
		' WHERE SuppInvoiceDetails.Approved=0 AND SuppInvoiceDetails.SuppReference="' . $_POST['SuppReference'] . '" AND SuppInvoiceDetails.SupplierID=' . $_POST['SupplierID']  .
		" ORDER BY SuppName, SuppReference, LineNo ASC ";
	$Result=DB_query($SQL,$db, $ErrMsg, $DbgMsg);
	$UniqueStockID = "";

	$BGCOLOUR = ( $BGCOLOUR=='' ? 'BGCOLOR=#999999' : '' );

	echo    "<TR $BGCOLOUR>" . "<TD ALIGN=CENTER>";
	Input_Check(null, 'APP_'.$UID, $_POST['APP_' . $UID] );
	echo    "</TD>" .
		"<TD COLSPAN=5>" .
		" Invoice " .
		$_POST['SuppReference'] . " " .
		"</TD>" .
		"<TD COLSPAN=2 $BGCOLOUR align=center>" .
		"Received for PO" . $_POST['PONumber'];
	Input_Text( '','PONumber', $_POST['PONumber'] );
	echo  "</A>" .
		"</TD>" .
		"</TR>";
	
	$TheSum = 0;
	echo   '<TR class="tableheader" align=center>
	        <TD></TD>
	        <TD>' . _('Line')                . '</TD>
	        <TD>' . _('Description') . '</TD>
	        <TD>' . _('Invoicing')   . '</TD>
	        <TD>' . _('Price')               . '</TD>
	        <TD>' . _('Total')               . '</TD>
	        <TD>' . _('Inventory')     . '</TD>
	        <TD>' . _('Non-inventory')       . '</TD>
	        </TR>';
	
	$BGCOLOUR = ( $BGCOLOUR=='BGCOLOR=#999999' ? '' : 'BGCOLOR=#999999'  );
	while ($myrow = DB_fetch_array($Result)) {
		if ($myrow['Description']=='CHECKSUM') {
			${"Checksum".$myrow['SuppReference']} = $myrow['Total'];
		} else {
			$accountChoices = array ( '' => '0', 'Shipping'=> '57500', 'Development' => '68000', 'Sales Tax' => '71500', 'Labour' => '57000', 'Discount' => '59500' );
			$GRNChoices = array ( array ('description' => '', 'qty' =>'', 'grnno'=>0  ));
			$UID = $myrow['SIDetailID'];
			if (!isset($_POST['APP_' . $UID])) {
				$_POST['APP_' . $UID] = "off";
			}
		        Input_Hidden( 'SID_' . $UID, $myrow['SupplierID']) ;
			Input_Hidden( 'INV_' . $UID, $myrow['SuppReference']) ;
			$grnSQL = "	SELECT	GRNNo, PurchOrderDetails.GLCode, PurchOrderDetails.ItemCode, GRNs.ItemDescription, " .
	//					CONCAT( ' (' , Floor(GRNs.QtyRecd-GRNs.QuantityInv),')' ) QtyRemaining  FROM GRNs
	  			  "             Floor(GRNs.QtyRecd-GRNs.QuantityInv) AS QtyRemaining  FROM GRNs  " .

				  "	LEFT JOIN PurchOrderDetails ON PurchOrderDetails.PODetailItem=GRNs.PODetailItem
					LEFT JOIN PurchOrders ON PurchOrderDetails.OrderNo = PurchOrders.OrderNo
					WHERE PurchOrders.OrderNo = '" . $myrow['PONumber'] ."'";
			$grnChoiceResults = DB_query($grnSQL, $db);
			while ($myGRN = DB_fetch_array($grnChoiceResults)) {
				if ( $myGRN['ItemCode'] != "") {
					$this_choice['description'] = $myGRN['ItemCode'];
				} else {
	                                $this_choice['description'] = $myGRN['ItemDescription'];
				}
				$this_choice['qty'] = $myGRN['QtyRemaining'];
				$this_choice['grnno'] = $myGRN['GRNNo'];
				$GRNChoices[] = $this_choice;
			}

			echo	"<TR $BGCOLOUR>" . "<TD></TD>" .
				"<TD>" . $myrow['LineNo']	. "</TD>" .
				"<TD>" . $myrow['Description'];
			if ( $myrow['StockID'] != '' ) {
					echo ' <I>(' .  $myrow['StockID'] . ')</I)';
			}
			echo	"</TD>" .
				"<TD ALIGN=RIGHT>" . $myrow['Invoicing']  . "</TD>" .
				"<TD ALIGN=RIGHT>" . $myrow['Price']  . "</TD>" .
				"<TD ALIGN=RIGHT>" . $myrow['Total']  . "</TD>" .
				"<TD ALIGN=CENTER>" ;
			$TheSum += $myrow['Total'];
			$theInvSelection = array();
			$theInvSelection['description'] = $myrow['StockID'];
			$theInvSelection['qty'] = $myrow['Invoicing'];
			$theAccountSelection = $myrow['Description'];
			if ( $myrow['SupplierID']==44) {
				if (stripos( $myrow['Description'], "BRD", 0) !== false) {
					$CurrentBoard = substr($myrow['Description'], stripos( $myrow['Description'], "BRD", 0));
					if (stripos( $myrow['Description'],"-R")>0) {
						$CurrentBoard = trim(substr($CurrentBoard,0,stripos( $CurrentBoard, "-R")));
					}
					$theInvSelection = "Labour: " . $CurrentBoard;
				}	
			}
	
			if ( $myrow['SupplierID']==61) {
				if (stripos( $myrow['Description'], "BRD", 0) !== false) {
					$CurrentBoard = substr($myrow['Description'], stripos( $myrow['Description'], "BRD", 0));
					if (stripos( $myrow['Description'],"-R")>0) {
						$CurrentBoard = trim(substr($CurrentBoard,0,stripos( $CurrentBoard, "-R")));
					}
					$theInvSelection = "Labour: " . $CurrentBoard;
		        	} else {
		                        if (stripos( $myrow['Description'], "Handling") !== false) $theAccountSelection = "Shipping";
					if (stripos( $myrow['Description'], "NRE") !== false) $theAccountSelection = "Development";
		                        if ( floatval($myrow['Total']) < 0  ) $theAccountSelection = "Discount";
				}	
			}
		
		        Input_GRN_Option( 'GRNNo_' . $UID, $GRNChoices , $theInvSelection ) ;
			echo	"</TD>" .   "<TD>";
			Input_Option( null, 'GLCode_' . $UID, $accountChoices, $theAccountSelection );
			echo    "</TD>" ;
			echo    "</TR>";
		}
	}
	echo "<TR $BGCOLOUR>" . 
		"<TD COLSPAN=9 ALIGN='RIGHT'>" .
		( (${"Checksum".$thePreviousInvoiceID} == $TheSum ) ? 
			("<I>Checksum and Total match: $" . number_format($TheSum,2) . " </I>") : 
			("<B>Checksum (" .${"Checksum".$thePreviousInvoiceID} . ") and Total (" .$TheSum . ") Don't match</B>") ) .
		"</TD>" .
		"</TR>" ;
	echo '</TABLE><CENTER><INPUT TYPE=SUBMIT NAME=Update Value=' . _('Update') . '><P>';
	echo "</FORM>";
	include('includes/footer.inc');
}
?>
