<?php

/* $Revision: 1.12 $ */

use Rialto\PurchasingBundle\Entity\Supplier;
use Rialto\CoreBundle\Database\ErpDbManager;

$PageSecurity = 11;
/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include_once("includes/inventory_db.inc");   //include('manufacturing/includes/inventory_db.inc');
include_once('includes/WO_ui_input.inc');

$title = _('Approve Invoice');
include('includes/header.inc');

function PostApprovedInvoice($SuppInvoiceID, $db)
{
    // Fetch full invoice for selected item
    $sql = " SELECT DISTINCT SupplierID, SuppReference, PONumber, InvoiceDate
		 FROM SuppInvoiceDetails WHERE SIDetailID = '" . $SuppInvoiceID . "'";
    $result = DB_query($sql, $db);
    $invoicerow = DB_fetch_array($result);

    // Make sure there isn't already a matching supplier transaction
    $sql = "SELECT Count(*) FROM SuppTrans WHERE SupplierNo='" . $invoicerow['SupplierID'] .
        "' AND SuppReference='" . $invoicerow['SuppReference'] . "'";
    $errorRow = DB_fetch_row(DB_query($sql, $db));
    if ( $errorRow[0] == 1 ) { /* Transaction reference already entered */
        echo " This transaction ($SuppInvoiceID) already exists.<BR>";
        return -1;
    }

    echo "About to start on $SuppInvoiceID<BR>";

    $Result = DB_query("BEGIN", $db);
    $InvoiceNo = GetNextTransNo(20, $db); // Purchase Invoice
    $PeriodNo = GetPeriod(ConvertSQLDate($invoicerow['InvoiceDate']), $db);
    $SQLInvoiceDate = $invoicerow['InvoiceDate'];
    $LocalTotal = 0;
    $ExRate = 1;
    $CreditorsAct = '20000';
    $CurrCode = 'USD';
    $TaxGLCode = '71500';
    $GRNAct = '20100';
    $PurchPriceVarAct = '59000';
    $SupplierID = $invoicerow['SupplierID'];
    $dbm = ErpDbManager::getInstance();
    $supplier = $dbm->find('purchasing\Supplier', $SupplierID);
    if ( ! $supplier ) {
        $Result = DB_query("ROLLBACK", $db);
        echo "Unable to process invoice $SuppInvoiceID: no such supplier $SupplierID.";
        return;
    }
    $terms = $supplier->getPaymentTerms();
    $DueDate = FormatDateForSQL(CalcDueDate(
        ConvertSQLDate($invoicerow['InvoiceDate']),
        $terms->getDayInFollowingMonth(),
        $terms->getDaysBeforeDue()
    ));
    if ( $PeriodNo < 0 ) {
        echo "can't do this one; bad dates...";
        $InputError = true;
    }

    $SuppReference = $invoicerow['SuppReference'];
    if ( $InputError == False ) {

        // Fetch all items in this invoice
        $sql = " SELECT SuppInvoiceDetails.*,
                GRNs.ItemCode AS GRN_ItemCode,
                PurchOrderDetails.PODetailItem,
				PurchOrderDetails.JobRef,
                PurchOrderDetails.StdCostUnit,
                PurchOrderDetails.GLCode AS POD_GLCode
			 FROM SuppInvoiceDetails
			 LEFT JOIN GRNs ON SuppInvoiceDetails.GRNNo=GRNs.GRNNo
			 LEFT JOIN PurchOrderDetails ON PurchOrderDetails.PODetailItem=GRNs.PODetailItem
			 WHERE SuppInvoiceDetails.SupplierID='" . $SupplierID . "' AND SuppInvoiceDetails.SuppReference='" . $SuppReference . "'";
        $result = DB_query($sql, $db);
        while ( $invoiceitem = DB_fetch_array($result) ) {

            /* 	We have been posting to A/P and to the stock's GL Code.
              We should in fact post the variance to $PurchPriceVarAct

              Changed Line 103
             */

            // Validate that something is selected
            if ( ( $invoiceitem['GLCode'] == 0) && ($invoiceitem['GRNNo'] == 0) && ($invoiceitem['Description'] != 'CHECKSUM') ) {
                $Result = DB_query("ROLLBACK", $db);
                return -1;
            }

            // If a GL account is selected, create corresponding GL entry
            if ( $invoiceitem['GLCode'] != 0 ) {
                $SQL = 'INSERT INTO GLTrans (Type,
                    TypeNo,
                    TranDate,
                    PeriodNo,
                    Account,
                    Narrative,
                    Amount,
                    JobRef)
					VALUES (20, ' .
                    $InvoiceNo . ", '" .
                    $SQLInvoiceDate . "', " .
                    $PeriodNo . ', ' .
                    $invoiceitem['GLCode'] . ", '" .
                    $invoiceitem['SupplierID'] . ' ' . $invoiceitem['Description'] . "', " .
                    round($invoiceitem['Total'] / $ExRate, 2) . ", '" .
                    $invoiceitem['JobRef'] . "')";
                $Result = DB_query($SQL, $db);
                $LocalTotal += round($invoiceitem['Total'] / $ExRate, 2);
            }

            // Otherwise a GRN item was selected
            else {
                /* enter the GL entry to reverse GRN suspense entry created on delivery at standard cost used on delivery */
                if ( $invoiceitem['StdCostUnit'] * $invoiceitem['Invoicing'] != 0 ) {
                    $SQL = 'INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount)
						 VALUES (20, ' .
                        $InvoiceNo . ", '" . $SQLInvoiceDate . "', " . $PeriodNo . " , '" . $GRNAct .
                        "', '" . $invoiceitem['SupplierID'] . ' - ' . _('GRN') . ' ' . $invoiceitem['GRNNo'] .
                        ' - ' . $invoiceitem['ItemCode'] . ' x ' . $invoiceitem['Invoicing'] . ' @  ' .
                        _('std cost of') . ' ' . $invoiceitem['StdCostUnit'] . "', " .
                        $invoiceitem['StdCostUnit'] * $invoiceitem['Invoicing'] . ')';
                    $Result = DB_query($SQL, $db, $ErrMsg, $Dbg, True);
                }
                $PurchPriceVar = round($invoiceitem['Invoicing'] * ( ($invoiceitem['Price'] / $ExRate) - $invoiceitem['StdCostUnit']), 2);
                if ( $PurchPriceVar != 0 ) { /* don't bother with this lot if there is no difference ! */
                    if ( $invoiceitem['ItemCode'] != '' ) { /* so it is a stock item */
                        /* need to get the stock category record for this stock item - this is function in SQL_CommonFunctions.inc */
                        $StockGLCode = GetStockGLCode($invoiceitem['ItemCode'], $db);
                        $SQL = ' INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount)
							 VALUES (20, ' . $InvoiceNo . ", '" . $SQLInvoiceDate . "', " . $PeriodNo . ', ' .
                            $PurchPriceVarAct . ", '" .
                            $invoiceitem['SupplierID'] . ' - ' . _('GRN') . ' ' . $invoiceitem['GRNNo'] .
                            ' - ' . $invoiceitem['ItemCode'] . ' x ' .
                            $invoiceitem['Invoicing'] . ' x  ' . _('price var of') . ' ' .
                            number_format(($invoiceitem['Price'] / $ExRate) - $invoiceitem['StdCostUnit'], 2) . "', " . $PurchPriceVar . ')';
                        $Result = DB_query($SQL, $db);
                    }
                    else {
                        $SQL = 'INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount)
							VALUES (20, ' . $InvoiceNo . ", '" . $SQLInvoiceDate . "', " . $PeriodNo . ", '" .
                            $PurchPriceVarAct . "', '" .
                            $invoiceitem['SupplierID'] . ' - ' . _('GRN') . ' ' . $invoiceitem['GRNNo'] .
                            ' - ' . $invoiceitem['Description'] . ' x ' .
                            $invoiceitem['Invoicing'] . ' x  ' . _('price var') . ' ' .
                            number_format(($invoiceitem['Price'] / $ExRate) - $invoiceitem['StdCostUnit'], 2) . "', " . $PurchPriceVar . ')';
                        $Result = DB_query($SQL, $db);
                    }
                }
                $LocalTotal += round(($invoiceitem['Price'] * $invoiceitem['Invoicing']) / $ExRate, 2);
            } /* end of GRN postings */

            /* Now the TAX account */
            $invoiceitem['OvGST'] = 0;
            if ( $invoiceitem['OvGST'] != 0 ) {
                $SQL = 'INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (20, ' .
                    $InvoiceNo . ", '" . $SQLInvoiceDate . "', " . $PeriodNo . ", '" . $TaxGLCode .
                    "', '" . $invoiceitem['SupplierID'] . ' - ' . _('Inv') . ' ' .
                    $invoiceitem['SuppReference'] . ' ' . $CurrCode .
                    $invoiceitem['OvGST'] . ' @ ' . _('a rate of') . ' ' . $ExRate .
                    "', " . round($invoiceitem['OvGST'] / $ExRate, 2) . ')';
                $Result = DB_query($SQL, $db);
            }

            /* Now update the GRN and PurchOrderDetails records for amounts invoiced */
            if ( $invoiceitem['PODetailItem'] != "" ) {
                $SQL = 'UPDATE PurchOrderDetails SET QtyInvoiced = QtyInvoiced + ' . $invoiceitem['Invoicing'] .
                    ', ActPrice = ' . $invoiceitem['Price'] . ' WHERE PODetailItem = ' . $invoiceitem['PODetailItem'];
                $Result = DB_query($SQL, $db);
                $SQL = 'UPDATE GRNs SET QuantityInv = QuantityInv + ' . $invoiceitem['Invoicing'] .
                    ' WHERE GRNNo = ' . $invoiceitem['GRNNo'];
                $Result = DB_query($SQL, $db);
            }
            $SISQL = "UPDATE SuppInvoiceDetails SET Posted=1 WHERE SIDetailID='" . $invoiceitem['SIDetailID'] . "'";
            echo $SISQL . "<BR>";
            $SIResult = DB_query($SISQL, $db);
        }
        /* 	END OF LOOPING OVER ITEMS */

        /* 	Now the control account */
        $SQL = 'INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (20, ' .
            $InvoiceNo . ", '" .
            $SQLInvoiceDate . "', " .
            $PeriodNo . ', ' .
            $CreditorsAct . ", '" .
            $SupplierID . ' - ' . _('Inv') . ' ' .
            $SuppReference . ' ' . $CurrCode .
            number_format($LocalTotal + $invoiceitem['OvGST'], 2) .
            ' @ ' . _('a rate of') . ' ' . $ExRate . "', " .
            -round(($LocalTotal + ( $invoiceitem['OvGST'] / $ExRate)), 2) . ')';
        $Result = DB_query($SQL, $db);
        echo $SQL . "<BR>";
        $SQL = 'INSERT INTO SuppTrans (TransNo, Type,
            SupplierNo,
            SuppReference,
            TranDate,
            DueDate,
            OvAmount,
            OvGST,
            Rate,
            TransText)
			VALUES (' . $InvoiceNo . ",20 , '" .
            $SupplierID . "', '" .
            $SuppReference . "',
			'" .
            $SQLInvoiceDate . "', '" .
            $DueDate . "', " .
            round(($LocalTotal + ( $invoiceitem['OvGST'] / $ExRate)), 2) .
            ', ' . round($invoiceitem['OvGST'], 2) . ', ' .
            $ExRate . ", '" .
            $invoiceitem['Comments'] . "')";
        $Result = DB_query($SQL, $db);
        echo $SQL . "<BR>";
    }
    $Result = DB_query("COMMIT", $db);
    prnMsg(_('Supplier invoice number') . ' ' . $InvoiceNo . ' ' . _('has been processed'), 'success');
}

if ( isset($_POST['Update']) ) {
    echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

    // Get unapproved invoice items
    $invoiceSQL = "SELECT DISTINCT SIDetailID FROM SuppInvoiceDetails WHERE Approved = 0";
    $invoiceRES = DB_query($invoiceSQL, $db);
    echo "<CENTER>";
    while ( $myInvoice = DB_fetch_array($invoiceRES) ) {

        $commitment = DB_query("BEGIN", $db);
        $matchedTransaction = "so far";
        $INVUID = $myInvoice['SIDetailID'];

        // If this item is selected
        if ( $_POST['APP_' . $INVUID] == "on" ) {
            $SupplierID = $_POST["SID_" . $INVUID];
            $SuppReference = $_POST["INV_" . $INVUID];

            // Get all items in this invoice
            $invoiceitemSQL = " SELECT * FROM SuppInvoiceDetails
					    WHERE SupplierID='" . $SupplierID . "' AND SuppReference='" . $SuppReference . "'";
            $invoiceitemRES = DB_query($invoiceitemSQL, $db);
            while ( ($myrow = DB_fetch_array($invoiceitemRES)) && ($matchedTransaction == "so far") ) {
                $UID = $myrow['SIDetailID'];
                echo $_POST['GRNNo_' . $UID] . "  " . $_POST['GLCode_' . $UID] . "<BR>";

                // ???
                if ( ( $_POST['GRNNo_' . $UID] == 0 ) && ($_POST['GLCode_' . $UID] == 0 ) && ($myrow['Description'] != 'CHECKSUM' ) ) {
                    echo "Unmatched transaction ($SuppReference) will not be processed.";
                    $matchedTransaction = "no longer";
                    $commitment = DB_query("ROLLBACK", $db);
                }

                // If there is a GRN item selected, update the invoice item accordingly
                if ( $_POST['GRNNo_' . $UID] != 0 ) {
                    $matchSQL = "	UPDATE	SuppInvoiceDetails
							SET	Approved=1, GRNNo= '" . $_POST['GRNNo_' . $UID] . "'" .
                        "	WHERE	SIDetailID = '" . $UID . "'";
                    echo $matchSQL . "<BR>";
                    $rescode = DB_query($matchSQL, $db);
                    Input_Hidden('APP_' . $myrow['SIDetailID'], $_POST['APP_' . $myrow['SIDetailID']]);
                }

                // If a GL account is selected, update the invoice item accordingly
                else {
                    if ( $_POST['GLCode_' . $UID] != 0 ) {
                        $matchSQL = "   UPDATE  SuppInvoiceDetails
        	                                                SET     Approved=1, GLCode= '" . $_POST['GLCode_' . $UID] . "'" .
                            "   WHERE   SIDetailID = '" . $UID . "'";
                        echo $matchSQL . "<BR>";
                        $rescode = DB_query($matchSQL, $db);
                        Input_Hidden('APP_' . $myrow['SIDetailID'], $_POST['APP_' . $myrow['SIDetailID']]);
                    }
                }
            }
        }
        $commitment = DB_query("COMMIT", $db);
    }
    unset($_POST['Update']);
    Input_Submit('ConfirmPosting', "OK");
    Input_Submit('Cancel', "Cancel");
    echo '</FORM>';
    include('includes/footer.inc');
    exit;
}


if ( isset($_POST['ConfirmPosting']) ) {
    echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';
    $SQL = "SELECT SIDetailID FROM SuppInvoiceDetails WHERE Approved =1 AND Posted=0";
    $Result = DB_query($SQL, $db);
    echo "<CENTER>";
    while ( $myInvoice = DB_fetch_array($Result) ) {
        $INVUID = $myInvoice['SIDetailID'];
        if ( $_POST['APP_' . $INVUID] == "on" ) {
            PostApprovedInvoice($INVUID, $db);
        }
    }
    Input_Submit('Restart', "Restart");
    echo '</FORM>';
    include('includes/footer.inc');
    exit;
}


echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';
echo '<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>
<TR class="tableheader" align=center>
	<TD></TD>
	<TD>' . _('Line') . '</TD>
	<TD>' . _('Description') . '</TD>
	<TD>' . _('Invoicing') . '</TD>
	<TD>' . _('Price') . '</TD>
	<TD>' . _('Total') . '</TD>
        <TD>' . _('Inventory') . '</TD>
	<TD>' . _('Non-inventory') . '</TD>
	</TR>';

$SQL = "SELECT SuppInvoiceDetails.LineNo, SuppInvoiceDetails.Description, SuppInvoiceDetails.Invoicing, SuppInvoiceDetails.PONumber, SuppInvoiceDetails.StockID,
		SuppInvoiceDetails.Price, SuppInvoiceDetails.Approved, SuppInvoiceDetails.SuppReference, Suppliers.SuppName,
		SuppInvoiceDetails.Total, SuppInvoiceDetails.InvoiceDate, SuppInvoiceDetails.SIDetailID, SuppInvoiceDetails.SupplierID
	 FROM SuppInvoiceDetails
	 LEFT JOIN Suppliers ON Suppliers.SupplierID=SuppInvoiceDetails.SupplierID
	 WHERE SuppInvoiceDetails.Approved= 0
	 ORDER BY SuppName, SuppReference, LineNo ASC ";
$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
$thePreviousInvoiceID = "";
$index = -1;
$InvoiceList = array();
$UniqueStockID = "";
while ( $myrow = DB_fetch_array($Result) ) {
    if ( $myrow['Description'] == 'CHECKSUM' ) {
        ${"Checksum" . $myrow['SuppReference']} = $myrow['Total'];
    }
    else {
        $accountChoices = array('' => '0', 'Shipping' => '57500', 'Development' => '68000', 'Sales Tax' => '71500', 'Labour' => '57000', 'Discount' => '59500');
        $GRNChoices = array(array('description' => '', 'qty' => '', 'grnno' => 0));
        $UID = $myrow['SIDetailID'];
        if ( ! isset($_POST['APP_' . $UID]) ) {
            $_POST['APP_' . $UID] = "off";
        }
        Input_Hidden('SID_' . $UID, $myrow['SupplierID']);
        Input_Hidden('INV_' . $UID, $myrow['SuppReference']);
        $grnSQL = "	SELECT	GRNNo, PurchOrderDetails.GLCode, PurchOrderDetails.ItemCode, GRNs.ItemDescription, " .
//					CONCAT( ' (' , Floor(GRNs.QtyRecd-GRNs.QuantityInv),')' ) QtyRemaining  FROM GRNs
            "             Floor(GRNs.QtyRecd-GRNs.QuantityInv) AS QtyRemaining  FROM GRNs  " .
            "	LEFT JOIN PurchOrderDetails ON PurchOrderDetails.PODetailItem=GRNs.PODetailItem
				LEFT JOIN PurchOrders ON PurchOrderDetails.OrderNo = PurchOrders.OrderNo
				WHERE PurchOrders.OrderNo = '" . $myrow['PONumber'] . "'";
        $grnChoiceResults = DB_query($grnSQL, $db);
        while ( $myGRN = DB_fetch_array($grnChoiceResults) ) {
            if ( $myGRN['ItemCode'] != "" ) {
                $this_choice['description'] = $myGRN['ItemCode'];
            }
            else {
                $this_choice['description'] = $myGRN['ItemDescription'];
            }
            $this_choice['qty'] = $myGRN['QtyRemaining'];
            $this_choice['grnno'] = $myGRN['GRNNo'];
            $GRNChoices[] = $this_choice;
        }

        if ( $myrow['SuppReference'] != $thePreviousInvoiceID ) {
            if ( $index != -1 ) {
                echo "<TR $BGCOLOUR>" .
                "<TD COLSPAN=9 ALIGN='RIGHT'>" .
                ( ( Abs(${"Checksum" . $thePreviousInvoiceID} - $TheSum) < 0.02 ) ?
                    ("<I>Checksum matches: $" . number_format($TheSum, 2) . " </I>") :
                    ("<B>Checksum $" . number_format(${"Checksum" . $thePreviousInvoiceID}, 2) . " != $" . number_format($TheSum, 2) . "</B>") ) .
                "</TD>" .
                "</TR>";
                $BGCOLOUR = ( $BGCOLOUR == '' ? 'BGCOLOR=#999999' : '' );
            }
            $index ++;
            $InvoiceList['$index'] = $myrow['SuppReference'];
            echo "<TR $BGCOLOUR>" . "<TD ALIGN=CENTER>";
            Input_Check(null, 'APP_' . $UID, $_POST['APP_' . $UID]);
            echo "</TD>" .
            "<TD COLSPAN=5>";

            echo '<A target="_blank" HREF="EditSuppInvoice.php?SuppReference=' . $myrow['SuppReference'] . '&&SupplierID=' . $myrow['SupplierID'] . '&&PONumber=' . $myrow['PONumber'] . '">';
            echo $myrow['SuppName'] . " Invoice " .
            $myrow['SuppReference'] . " (" .
            substr($myrow['InvoiceDate'], 0, 10) .
            ")</A></TD>" .
            "<TD COLSPAN=2 $BGCOLOUR align=center>" .
            "Received for PO: <A target='_blank' HREF='GoodsReceived.php?PONumber=" . $myrow['PONumber'] . "'>" . $myrow['PONumber'] . "</A>" .
            "</TD>" .
            "</TR>";
            $thePreviousInvoiceID = $myrow['SuppReference'];
            $TheSum = 0;
        }
        echo "<TR $BGCOLOUR>" . "<TD></TD>" .
        "<TD>" . $myrow['LineNo'] . "</TD>" .
        "<TD>" . $myrow['Description'];
        if ( $myrow['StockID'] != '' ) {
            echo ' <I>(' . $myrow['StockID'] . ')</I)';
        }
        echo "</TD>" .
        "<TD ALIGN=RIGHT>" . $myrow['Invoicing'] . "</TD>" .
        "<TD ALIGN=RIGHT>" . $myrow['Price'] . "</TD>" .
        "<TD ALIGN=RIGHT>" . $myrow['Total'] . "</TD>" .
        "<TD ALIGN=CENTER>";
        $TheSum += $myrow['Total'];
        $theInvSelection = array();
        $theInvSelection['description'] = $myrow['StockID'];
        $theInvSelection['qty'] = $myrow['Invoicing'];
        $theAccountSelection = $myrow['Description'];
        if ( $myrow['SupplierID'] == 44 ) {
            if ( stripos($myrow['Description'], "BRD", 0) !== false ) {
                $CurrentBoard = substr($myrow['Description'], stripos($myrow['Description'], "BRD", 0));
                if ( stripos($myrow['Description'], "-R") > 0 ) {
                    $CurrentBoard = trim(substr($CurrentBoard, 0, stripos($CurrentBoard, "-R")));
                }
                $theInvSelection = "Labour: " . $CurrentBoard;
            }
        }


        if ( $myrow['SupplierID'] == 61 ) {
            if ( stripos($myrow['Description'], "BRD", 0) !== false ) {
                $CurrentBoard = substr($myrow['Description'], stripos($myrow['Description'], "BRD", 0));
                if ( stripos($myrow['Description'], "-R") > 0 ) {
                    $CurrentBoard = trim(substr($CurrentBoard, 0, stripos($CurrentBoard, "-R")));
                }
                $theInvSelection = "Labour: " . $CurrentBoard;
            }
            else {
                if ( stripos($myrow['Description'], "Handling") !== false )
                    $theAccountSelection = "Shipping";
                if ( stripos($myrow['Description'], "NRE") !== false )
                    $theAccountSelection = "Development";
                if ( floatval($myrow['Total']) < 0 )
                    $theAccountSelection = "Discount";
            }
        }

        Input_GRN_Option('GRNNo_' . $UID, $GRNChoices, $theInvSelection);
        echo "</TD>" . "<TD>";
        Input_Option(null, 'GLCode_' . $UID, $accountChoices, $theAccountSelection);
        echo "</TD>";
        echo "</TR>";
    }
}
echo "<TR $BGCOLOUR>" .
 "<TD COLSPAN=9 ALIGN='RIGHT'>" .
 ( (${"Checksum" . $thePreviousInvoiceID} == $TheSum ) ?
    ("<I>Checksum and Total match: $" . number_format($TheSum, 2) . " </I>") :
    ("<B>Checksum (" . ${"Checksum" . $thePreviousInvoiceID} . ") and Total (" . $TheSum . ") Don't match</B>") ) .
 "</TD>" .
 "</TR>";
echo '</TABLE><CENTER><INPUT TYPE=SUBMIT NAME=Update Value=' . _('Update') . '><P>';
echo "</FORM>";
include('includes/footer.inc');
?>
