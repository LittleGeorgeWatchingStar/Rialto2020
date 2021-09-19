<?php

/* $Revision: 1.4 $ */
$PageSecurity = 7;
include ('includes/session.inc');
$title = _('Reconciliation From Bank Statement');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CommonGumstix.inc');
include('includes/WO_ui_input.inc');

// aqua, black, blue, fuchsia, gray, green, lime, maroon, navy, olive, purple, red, silver, teal, white, and yellow.

function PrintTableRow($a, $b, $c, $d, $e, $f, $g='')
{
    switch ( $f ) {
        case 'WRP' : $color = '#CFECEC'; break;
        case 'Bank': $color = '#6CC3FF'; break;
        case 'Bank2': $color = '#B0DCFF'; break;
        case 'Balanced': $color = '#FAF8CC'; break;
        case 'Difference': $color = '#FAF8CC'; break;
        case 'Invoice': $color = '#FDD017'; break;
        default: $color = '#FFFF00'; break;
    }
    if ( $e != '' ) {
        $e = number_format($e, 2);
    }
    if ( $d != '' ) {
        $d = number_format($d, 2);
    }
    printf("<tr bgcolor='$color'> <td>%s</td>   <td>%s</td>  <td>%s</td>  <td ALIGN=RIGHT>%s</td>  <td ALIGN=RIGHT>%s</td> ", $a/* .$f */, $b, $c, $d, $e);
    echo '<td>';
    if ( $g != '' ) {
        list ( $this_ref, $this_inv ) = split('-', $g);
        $to_be_checked = $_POST[$g];
        Input_Check($ttt . '', $g, $to_be_checked, true);
    }
    echo '</td></tr>';
    return $color;
}

DB_query('BEGIN', $db);

function AddPaymentForInvoice($db, $bank_statement_id, $bankref, $date, $amt,
    $account, $suppno, $check_no=0)
{
    //	Assumes $amt > 0 => increase amount due
    if ( $amt != 0 ) {
        $prd = GetPeriodSafely(($date), $db);
        if ( $prd < 0 ) {
            echo "D'OH!";
            exit;
        }
        $type = 22;
        $typeno = GetNextTransNo($type, $db);

        $sql = "INSERT INTO BankTrans (Type, TransNo, BankAct, Ref, ExRate, TransDate, BankTransType, Amount, CurrCode, Printed, ChequeNo) VALUES
	                ($type, $typeno,'10200', $suppno, 1, '$date', 'Direct credit', $amt, 'USD', 1, $check_no)";
        $err = "CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The bank transaction could not be added to the database because')";
        $dbg = "The following SQL to insert the supplier invoice was used:  ";
        $res = DB_query($sql, $db, $err, $dbg);
        $bank_trans_id = DB_last_insert_id($db);

        $sql = 'UPDATE BankStatements SET BankTransID=' . $bank_trans_id . ' WHERE BankStatementID=' . $bank_statement_id . ' AND BankTransID=0';
        $res = DB_query($sql, $db, $err, $dbg);

        $sql = 'INSERT INTO SuppTrans (TransNo, Type, SupplierNo, SuppReference, TranDate, DueDate, OvAmount, OvGST, Rate, TransText) VALUES ' .
            " ( $typeno, $type,  $suppno, '$suppref', '$date', '$date', $amt, '0', '1', '$text' )";
        $err = "CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The supplier invoice transaction could not be added to the database because')";
        $dbg = "The following SQL to insert the supplier invoice was used:  ";
        $res = DB_query($sql, $db, $err, $dbg);

        $suppref = $suppno . ' - Payroll - ' . $date;

        $sql = " INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount, ChequeNo ) VALUES  " .
            " ($type, $typeno, '$date',$prd, '10200','$suppref'," . $amt . ", $check_no )";
        $rc = DB_query($sql, $db);

        $sql = " INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount, ChequeNo ) VALUES  " .
            " ($type, $typeno, '$date',$prd, '20000','$suppref'," . -$amt . ", $check_no )";
        $rc = DB_query($sql, $db);
    }
}

function AmendInvoice($db, $typeno, $added_amt, $account)
{
    //	Assumes $added_amt > 0 => increase the amount due
    $type = 20;
    $sql = "UPDATE SuppTrans SET OvAmount = OvAmount + $added_amt WHERE Type=$type AND TransNo=$typeno";
    $err = "CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The supplier invoice transaction could not be added to the database because')";
    $dbg = "The following SQL to insert the supplier invoice was used:  ";
    $res = DB_query($sql, $db, $err, $dbg);

    $sql = "SELECT TranDate, SuppReference FROM SuppTrans WHERE Type=$type AND TransNo=$typeno";
    $err = "CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The supplier invoice transaction could not be added to the database because')";
    $dbg = "The following SQL to insert the supplier invoice was used:  ";
    $res = DB_query($sql, $db, $err, $dbg);
    $row = DB_fetch_array($res);

    $suppref = $row['SuppReference'];
    $date = $row['TranDate'];
    $prd = GetPeriodSafely(($date), $db);
    if ( $prd < 0 ) {
        echo "D'OH!"; exit;
    }

    $sql = " INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount ) VALUES  " .
        " ( $type, $typeno, '$date',$prd, $account,'$suppref'," . $added_amt . " )";
    $rc = DB_query($sql, $db);

    $sql = " INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount ) VALUES  " .
        " ( $type, $typeno, '$date',$prd, '20000','$suppref'," . -$added_amt . " )";
    $rc = DB_query($sql, $db);
}

function AddInvoice($db, $suppno, $suppref, $date, $due, $amt, $tax, $text,
    $account)
{
    $prd = GetPeriodSafely(($date), $db);
    if ( $prd < 0 ) {
        echo "D'OH!"; exit;
    }
    $type = 20;
    $typeno = GetNextTransNo($type, $db);
    $sql = 'INSERT INTO SuppTrans (TransNo, Type, SupplierNo, SuppReference, TranDate, DueDate, OvAmount, OvGST, Rate, TransText) VALUES ' .
        " ( $typeno, $type,  $suppno, '$suppref', '$date', '$due', $amt, $tax, '1', '$text' )";
    $err = "CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The supplier invoice transaction could not be added to the database because')";
    $dbg = "The following SQL to insert the supplier invoice was used:  ";
    $res = DB_query($sql, $db, $err, $dbg);

    $sql = " INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount ) VALUES  " .
        " ( $type, $typeno, '$date',$prd, $account,'$suppref'," . $amt . " )";
    $rc = DB_query($sql, $db);

    $sql = " INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount ) VALUES  " .
        " ( $type, $typeno, '$date',$prd, '20000','$suppref'," . -$amt . " )";
    $rc = DB_query($sql, $db);
}

//	SET UP THE FORM

echo '<FORM METHOD="POST" ACTION="' . $_SERVER["PHP_SELF"] . '?' . SID . '">';
DB_query('Begin', $db);
echo '<CENTER><TABLE>';
$TableHeader = '<TR>
		<TD class="tableheader">' . _('Bank Date') . '</TD>
                <TD class="tableheader">' . _('BankRef') . '</TD>
		<TD class="tableheader">' . _('Type') . '</TD>
		<TD class="tableheader">' . _('Bank Amount') . '</TD>
                <TD class="tableheader">' . _('webERP Amount') . '</TD>
		<TD class="tableheader">' . _('OK') . '</TD>
		</TR>';

$_POST["BankAccount"] = 10200;

if ( isset($_POST['PostAll']) ) {
    foreach ( $_POST['BankRefs'] as $this_match ) {
        list ( $this_ref, $this_inv ) = split('-', $this_match);
        $invoice_match = $this_inv;
        $differences = $_POST['Post_' . $this_ref . '_differences'];
        $date = $_POST['Post_' . $this_ref . '_date'];
        $supplier_no = $_POST['Post_' . $this_ref . '_supplierno'];
        $salaries = $_POST['Post_' . $this_ref . '_salaries'];
        $suppreference = $_POST['Post_' . $this_ref . '_suppreference'];
        $trans_date = $_POST['Post_' . $this_ref . '_date'];
        $bank_statement_id = $_POST['Post_' . $this_ref . '_bank_statement_id'];

        $account = $_POST['Post_' . $this_ref . '_account'];
        $totalinvoice = $_POST['Post_' . $this_ref . '_totalinvoice'];

        if ( $suppreference == 'CHECK PAID' ) {
            AddPaymentForInvoice($db, $bank_statement_id, $suppreference, $date, $salaries, '75000', $supplier_no, $this_ref);
        }
        elseif ( $suppreference == 'ADP TX/FINCL SVC ADP - TAX GUMSTIX INC GUMSTIX' ) {
            AddPaymentForInvoice($db, $bank_statement_id, 'Direct credit', $date, $salaries, '75000', $supplier_no);
        }
        else {
            if ( $this_inv == 'ADD' ) {
                echo " Add Invoice And Payment  $" . number_format(-$differences, 2) . " on " . $date;
                AddInvoice($db, $supplier_no, $suppreference, $date, $date, -$differences, 0, '', $account);
                AddPaymentForInvoice($db, $bank_statement_id, 'Direct credit', $date, $differences, $account, $supplier_no);
            }
            else {
                echo " Amend&Pay Invoice: take $" . number_format($differences, 2) . " from Invoice " . $invoice_match . " on " . $date;
                AmendInvoice($db, $invoice_match, -$differences, $account);
                AddPaymentForInvoice($db, $bank_statement_id, 'Direct credit', $date, $totalinvoice, $account, $supplier_no);
            }
        }
        echo '<br>';
    }
}

$post_trans_id = 0;
if ( ! isset($_POST['TransMonth']) ) {
    $_POST['TransMonth'] = substr(LastDateInThisPeriod($db), 0, 7);
}
Input_Option_SQL_Date('Select the month to reconcile:  ', $db, 'TransMonth', $_POST['TransMonth']);

echo '<CENTER>';
echo '<TABLE WIDTH=80%>';

$list_of_matches = array();

$to_match['SupplierNo'] = 58;
$to_match['BankDescription'] = 'ADP PAYROLL FEES%'; //	This is the BankDescription from the BankStatement
$to_match['SuppReference'] = 'ADP PAYROLL FEES';  //	This is the SuppReference in SuppTrans and will be inserted into SuppTrans too
$to_match['CureAccount'] = '68500';
$list_of_matches[] = $to_match;

$to_match['SupplierNo'] = 58;
$to_match['BankDescription'] = 'ADP TX/FINCL%INC'; //	This is the BankDescription from the BankStatement
$to_match['SuppReference'] = 'ADP Taxes';  //	This is the SuppReference in SuppTrans and will be inserted into SuppTrans too
$to_match['CureAccount'] = '72000';
$list_of_matches[] = $to_match;

$to_match['SupplierNo'] = 110;
$to_match['BankDescription'] = 'WIRE OUT%ADP CANADA%';   //      This is the BankDescription from the BankStatement
$to_match['SuppReference'] = 'Salaries';          //      This is the SuppReference in SuppTrans and will be inserted into SuppTrans too
$to_match['CureAccount'] = '75100';
$list_of_matches[] = $to_match;

$to_match['SupplierNo'] = 30;
$to_match['BankDescription'] = 'AUTHNET GATEWAY%';      //      This is the BankDescription from the BankStatement
$to_match['SuppReference'] = 'AUTHNET GATEWAY';        //      This is the SuppReference in SuppTrans and will be inserted into SuppTrans too
$to_match['CureAccount'] = '62000';
$list_of_matches[] = $to_match;

$to_match['SupplierNo'] = 30;
$to_match['BankDescription'] = '%POS%CARDSYSTEMS%CHARGEBACK%';      //      This is the BankDescription from the BankStatement
$to_match['SuppReference'] = 'POS CARDSYSTEMS CHARGEBACK';        //      This is the SuppReference in SuppTrans and will be inserted into SuppTrans too
$to_match['CureAccount'] = '48000';
$list_of_matches[] = $to_match;

$to_match['SupplierNo'] = 30;
$to_match['BankDescription'] = '%AMERICAN EXPRESS COLLECTION%';      //      This is the BankDescription from the BankStatement
$to_match['SuppReference'] = 'AMERICAN EXPRESS COLLECTION';        //      This is the SuppReference in SuppTrans and will be inserted into SuppTrans too
$to_match['CureAccount'] = '62000';
$list_of_matches[] = $to_match;

$to_match['SupplierNo'] = 30;
$to_match['BankDescription'] = 'POS %CCDISCOUNT%';      //      This is the BankDescription from the BankStatement
$to_match['SuppReference'] = 'POS CCDISCOUNT';        //      This is the SuppReference in SuppTrans and will be inserted into SuppTrans too
$to_match['CureAccount'] = '21000';
$list_of_matches[] = $to_match;

$to_match['SupplierNo'] = 30;
$to_match['BankDescription'] = 'BANKCA%CCDISCOUNT%';  //      This is the BankDescription from the BankStatement
$to_match['SuppReference'] = 'POS CCDISCOUNT';        //      This is the SuppReference in SuppTrans and will be inserted into SuppTrans too
$to_match['CureAccount'] = '21000';
$list_of_matches[] = $to_match;

$to_match['SupplierNo'] = 36;
$to_match['BankDescription'] = 'ANALYSIS SERVICE%';      //      This is the BankDescription from the BankStatement
$to_match['SuppReference'] = 'ANALYSIS SERVICE';        //      This is the SuppReference in SuppTrans and will be inserted into SuppTrans too
$to_match['CureAccount'] = '62000';
$list_of_matches[] = $to_match;

$to_match['SupplierNo'] = 207;
$to_match['BankDescription'] = 'GOOGLE%GUMSTIX%';      //      This is the BankDescription from the BankStatement
$to_match['SuppReference'] = 'GOOGLE ADWORDS';        //      This is the SuppReference in SuppTrans and will be inserted into SuppTrans too
$to_match['CureAccount'] = '60000';
$list_of_matches[] = $to_match;

$to_match['SupplierNo'] = 208;
$to_match['BankDescription'] = 'AMAZON%';      //      This is the BankDescription from the BankStatement
$to_match['SuppReference'] = 'AMAZON CLOUD';        //      This is the SuppReference in SuppTrans and will be inserted into SuppTrans too
$to_match['CureAccount'] = '68000';
$list_of_matches[] = $to_match;

function GetUnmatchedChecks($date, $db, $bank_description, $by_month=false)
{
    if ( $by_month ) {
        $SQL = " SELECT * FROM BankStatements WHERE Left(BankPostDate,7) LIKE '$date' ";
    }
    else {
        $SQL = " SELECT * FROM BankStatements WHERE BankPostDate LIKE '$date' ";
    }
    $SQL .= " AND BankTransID IN ('0','') " .
        " AND BankDescription LIKE '$bank_description'" .
        " ORDER BY LEFT(BankDescription,4), BankPostDate ASC";
    $ErrMsg = _('The deposits could not be retrieved by the SQL because');
    return DB_query($SQL, $db, $ErrMsg);
}

function GetSalaryChecks($date, $db, $bank_description)
{
    $to_return = array();
    $SQL = " SELECT * FROM BankStatements WHERE BankPostDate LIKE '$date' " .
        " AND BankDescription LIKE '$bank_description'" .
        " AND BankTransID IN ('0','') " .
        " ORDER BY LEFT(BankDescription,4), BankPostDate ASC";
    $ErrMsg = _('The deposits could not be retrieved by the SQL because');
    $res = DB_query($SQL, $db, $ErrMsg);
    while ( $row = DB_fetch_array($res) ) {
        $to_return[] = $row;
    }
    $SQL = " SELECT * FROM BankStatements WHERE BankPostDate > '$date' " .
        " AND BankDescription LIKE 'CHECK PAID'" .
        " AND CustRef > 10100 AND BankTransID = 0 " .
        " ORDER BY LEFT(BankDescription,4), BankPostDate ASC";
    $ErrMsg = _('The deposits could not be retrieved by the SQL because');
    $res = DB_query($SQL, $db, $ErrMsg);
    while ( $row = DB_fetch_array($res) ) {
        $to_return[] = $row;
    }
    return $to_return;
}

function GetUnmatchedInvoices($supplier_no, $db, $supp_reference, $date)
{
    $supp_sql = "	SELECT * FROM SuppTrans
			WHERE Type=20 AND SupplierNo=$supplier_no " .
        " AND SuppReference LIKE '$supp_reference'" .
        " AND ABS(DATEDIFF(TranDate,'$date')) < 5";
    return DB_query($supp_sql, $db);
}

DisallowDoubleChecks();

echo $TableHeader;
foreach ( $list_of_matches as $this_match ) {
    echo '<tr><td colspan=8>' . $this_match['SuppReference'] . '</td></tr>';
    $TotalUnclearedDeposits = 0;
    $checks_to_match = GetUnmatchedChecks($_POST['TransMonth'], $db, $this_match["BankDescription"], true);
    while ( $myrow = DB_fetch_array($checks_to_match) ) {
        $differences = $myrow["Amount"];
        $this_ref = $myrow["BankRef"];
        //
        //	We first print the bank statement item, then look for potential matches
        //
		PrintTableRow(ConvertSQLDate($myrow["BankPostDate"]), $this_ref, $myrow["BankDescription"], $myrow["Amount"], '', 'Bank'); //	,$this_ref);
        $invoices_to_correct = GetUnmatchedInvoices($this_match['SupplierNo'], $db, $this_match['SuppReference'], $myrow["BankPostDate"]);
        if ( ($num_invoices = DB_num_rows($invoices_to_correct)) == 0 ) {
            //
            //	if there are no matching items then we need to add an invoice
            //
			$invoice_match = 'ADD';
            $row_color = PrintTableRow(ConvertSQLDate($myrow["BankPostDate"]), '', 'DIFFERENCE IS', $differences, '', 'Invoice', $myrow['BankRef'] . '-' . $invoice_match);
            if ( check_to_bool($_POST[$myrow["BankRef"] . '-' . $invoice_match]) ) {
                //
                //	if we indicate that this we want to add this item then we record the transaction to add
                //
				echo "<tr bgcolor='$row_color'><td></td><td colspan=5>";
                echo " Add Invoice and Payment  $" . number_format(-$myrow["Amount"], 2) . " on " . $myrow["BankPostDate"];
                Input_Hidden('BankRefs[]', $this_ref . '-' . $invoice_match);
                Input_Hidden('Post_' . $this_ref . '_supplierno', $this_match['SupplierNo']);
                Input_Hidden('Post_' . $this_ref . '_transno', $invoice_match);
                Input_Hidden('Post_' . $this_ref . '_salaries', $salary_debit_amount);
                Input_Hidden('Post_' . $this_ref . '_suppreference', $this_match['SuppReference']);
                Input_Hidden('Post_' . $this_ref . '_account', $this_match['CureAccount']);
                Input_Hidden('Post_' . $this_ref . '_date', $myrow["BankPostDate"]);
                Input_Hidden('Post_' . $this_ref . '_bank_statement_id', $myrow["BankStatementID"]);
                Input_Hidden('Post_' . $this_ref . '_differences', $differences);
                Input_Hidden('Post_' . $this_ref . '_totalinvoice', $myrow["Amount"]);
                $post_trans_id ++;
            }
            else {
//				PrintTableRow( ConvertSQLDate($myrow["BankPostDate"]),'', 'Balanced', $differences, '', 'Balance1' );
            }
        }
        else {
            //
            //	there is at least one potential invoice to match, so we iterate through each one
            //
			while ( $supp_res = DB_fetch_array($invoices_to_correct) ) {
                $differences = $myrow["Amount"] + $supp_res["OvAmount"]; //	keep track of the difference between the actual bank transaction and the invoeice we're matching
                $invoice_match = $supp_res["TransNo"];    //	the invoices transaction ID
                $salary_checks_list = array();
                if ( ($this_match['SupplierNo'] == 58) && (strpos($this_match["BankDescription"], 'FEES') === false ) ) {
                    //	we calculate the exact payroll taxes charged to us and amend that invoice
                    //	for any pay period we must enter the SuppTrans records for payroll checks
                    //	all this is triggered by the tax withdrawal:
                    //		find the tax withdrawal
                    //		match the salary withdrawal and pachecks
                    //	the running total of differences only applies to the tax payment
                    //	all other checks need to match to the salary invoice
                    //
					//	start with listing all bank payments
                    //
					$salary_payments = GetSalaryChecks($myrow["BankPostDate"], $db, 'ADP TX/FINCL%GUMSTIX');
                    foreach ( $salary_payments as $salary_check ) {
                        PrintTableRow(ConvertSQLDate($salary_check["BankPostDate"]), $salary_check['BankRef'], $salary_check['BankDescription'], $salary_check['Amount'], '', 'Bank2', 'ADP' . $myrow['BankRef'] . '-' . $salary_check['BankRef']);
                        if ( check_to_bool($_POST['ADP' . $myrow["BankRef"] . '-' . $salary_check['BankRef']]) ) {
                            $salary_match['SupplierNo'] = $this_match['SupplierNo'];
                            $salary_match['Amount'] = $salary_check['Amount'];
                            if ( $salary_check['BankDescription'] == 'CHECK PAID' ) {
                                $salary_match['BankRef'] = $salary_check['CustRef'];
                            }
                            else {
                                $salary_match['BankRef'] = $salary_check['BankRef'];
                            }
                            $salary_match['BankStatementID'] = $salary_check['BankStatementID'];
                            $salary_match['BankPostDate'] = $salary_check['BankPostDate'];
                            $salary_match['SuppReference'] = $salary_check['BankDescription'];
                            $salary_checks_list[] = $salary_match;
                            $salary_debit_amount += $salary_check['Amount'];
                            $differences += $salary_check['Amount']; //	this is the running difference between the tax invoice and all salary payments
                        }
                    }
                    //
                    //	next list all invoices
                    //
					$salary_invoiced = GetUnmatchedInvoices(58, $db, 'Salaries', $supp_res["TranDate"]);
                    if ( $salary_invoice = DB_fetch_array($salary_invoiced) ) {
                        $differences += $salary_invoice['OvAmount'];
                        $adp_directcredit_ref = $salary_invoice['TransNo'];
                        PrintTableRow(ConvertSQLDate($myrow["BankPostDate"]), $salary_invoice['TransNo'], $salary_invoice['SuppReference'], '', $salary_invoice['OvAmount'], 'WRP');
                    }
                    PrintTableRow(ConvertSQLDate($supp_res["TranDate"]), $invoice_match, $supp_res["SuppReference"], '', $supp_res["OvAmount"], 'WRP');
                }
                else {
                    PrintTableRow(ConvertSQLDate($supp_res["TranDate"]), $invoice_match, $supp_res["SuppReference"], '', $supp_res["OvAmount"], 'WRP');
                }

                if ( abs($differences) > 0.1 ) {
                    Input_Hidden('BoxesList[]', $myrow['BankRef'] . '-' . $invoice_match);
                    if ( check_to_bool(($_POST[$myrow["BankRef"] . '-' . $invoice_match])) ) {
                        Input_Hidden('CheckedBoxesList[]', $myrow['BankRef'] . '-' . $invoice_match);
                    }
                    PrintTableRow(ConvertSQLDate($myrow["BankPostDate"]), '', 'DIFFERENCE IS', $differences, '', 'Difference', $myrow['BankRef'] . '-' . $invoice_match);
                    if ( check_to_bool($_POST[$myrow["BankRef"] . '-' . $invoice_match]) ) {
                        echo "<tr bgcolor='#FFEECC'><td></td><td colspan=5>";
                        echo " Amend and Pay Invoice: Subtract $" . number_format($differences, 2) . " from Invoice " . $invoice_match . " on " . $myrow["BankPostDate"];
                        if ( abs($salary_debit_amount) > 0.1 ) {
                            foreach ( $salary_checks_list as $this_salary_check ) {
                                echo " <br> Add Invoice Payment ";
                                print_r($this_salary_check);
                                Input_Hidden('BankRefs[]', $this_salary_check['BankRef'] . '-' . $adp_directcredit_ref);
                                Input_Hidden('Post_' . $this_salary_check['BankRef'] . '_supplierno', $this_match['SupplierNo']);
                                Input_Hidden('Post_' . $this_salary_check['BankRef'] . '_transno', $this_salary_check['BankRef']);
                                Input_Hidden('Post_' . $this_salary_check['BankRef'] . '_salaries', $this_salary_check['Amount']);
                                Input_Hidden('Post_' . $this_salary_check['BankRef'] . '_suppreference', $this_salary_check['SuppReference']);
                                Input_Hidden('Post_' . $this_salary_check['BankRef'] . '_account', $this_match['CureAccount']);
                                Input_Hidden('Post_' . $this_salary_check['BankRef'] . '_date', $this_salary_check["BankPostDate"]);
                                Input_Hidden('Post_' . $this_salary_check['BankRef'] . '_bank_statement_id', $this_salary_check["BankStatementID"]);
                                Input_Hidden('Post_' . $this_salary_check['BankRef'] . '_differences', 0);
                                Input_Hidden('Post_' . $this_salary_check['BankRef'] . '_totalinvoice', $this_salary_check["Amount"]);
                                $post_trans_id ++;
                            }
                            echo '</td>';
                        }
                        echo '</tr>';
                        Input_Hidden('BankRefs[]', $this_ref . '-' . $invoice_match);
                        Input_Hidden('Post_' . $this_ref . '_supplierno', $this_match['SupplierNo']);
                        Input_Hidden('Post_' . $this_ref . '_transno', $invoice_match);
                        Input_Hidden('Post_' . $this_ref . '_salaries', $salary_debit_amount);
                        Input_Hidden('Post_' . $this_ref . '_suppreference', $this_match['SuppReference']);
                        Input_Hidden('Post_' . $this_ref . '_account', $this_match['CureAccount']);
                        Input_Hidden('Post_' . $this_ref . '_date', $myrow["BankPostDate"]);
                        Input_Hidden('Post_' . $this_ref . '_bank_statement_id', $myrow["BankStatementID"]);
                        Input_Hidden('Post_' . $this_ref . '_differences', $differences);
                        Input_Hidden('Post_' . $this_ref . '_totalinvoice', $myrow["Amount"]);
                        $post_trans_id ++;
                    }
                }
                else {
                    if ( $num_invoices > 1 ) {
                        Input_Hidden('BoxesList[]', $myrow['BankRef'] . '-' . $invoice_match);
                        if ( check_to_bool(($_POST[$myrow["BankRef"] . '-' . $invoice_match])) ) {
                            Input_Hidden('CheckedBoxesList[]', $myrow['BankRef'] . '-' . $invoice_match);
                        }
                        PrintTableRow(ConvertSQLDate($myrow["BankPostDate"]), '', 'Balanced', $differences, '', 'Balanced', $myrow['BankRef'] . '-' . $invoice_match);
                    }
                    else {
                        PrintTableRow(ConvertSQLDate($myrow["BankPostDate"]), '', 'Balanced', $differences, '', 'Balanced', '');
                    }
                }
            }
        }
        echo '<tr><td colspan=8></td></tr>';
    }
    echo '<tr><td colspan=8></td></tr>';
}
DB_query('commit', $db);
// use for testing-- 	DB_query( 'ROLLBACK', $db );

echo '</TABLE>';

if ( ! isset($_POST['PostAll']) ) {
    Input_Submit('PostAll', 'Post These');
}
Input_Submit('Check', 'Check This Date');

echo '</form>';
include('includes/footer.inc');
?>
