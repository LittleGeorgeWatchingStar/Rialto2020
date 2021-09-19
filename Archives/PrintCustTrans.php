<?php
/* $Revision: 1.12 $ */
ini_set("memory_limit","96M");
use Rialto\UtilBundle\Exception\PrinterException;
use Rialto\SalesBundle\Entity\Customer;
use Rialto\SalesBundle\Entity\Salesman;
use Rialto\ShippingBundle\Entity\Shipper;
use Rialto\AccountingBundle\Entity\PaymentTerms;

use Rialto\GeographyBundle\Model\Country;
$PageSecurity = 1;

include('includes/session.inc');

include("includes/WO_ui_input.inc");
require_once("gumstix/erp/tools/Printer.php");
require_once('gumstix/tools/I18n.php'); // 00044 - utf8ToAscii()


if (isset($_GET['FromTransNo'])){
    $FromTransNo = $_GET['FromTransNo'];
} elseif (isset($_POST['FromTransNo'])){
    $FromTransNo = $_POST['FromTransNo'];
}


if (isset($_GET['InvOrCredit'])){
    $InvOrCredit = $_GET['InvOrCredit'];
} elseif (isset($_POST['InvOrCredit'])){
    $InvOrCredit = $_POST['InvOrCredit'];
}
if (isset($_GET['PrintPDF'])){
    $PrintPDF = $_GET['PrintPDF'];
} elseif (isset($_POST['PrintPDF'])){
    $PrintPDF = $_POST['PrintPDF'];
}

include('includes/SQL_CommonFunctions.inc');
include('includes/DateFunctions.inc');
require_once 'includes/class.pdf.php';
include ('includes/htmlMimeMail.php');
include ('includes/CommonGumstix.inc');

$LineItems = array();

function esc_quotes($intxt) {
    $outtxt = str_replace("'","",$intxt);
    return str_replace('"',"",$outtxt);
}

if (isset($_GET['FromTransNo'])){
    $FromTransNo = $_GET['FromTransNo'];
} elseif (isset($_POST['FromTransNo'])){
    $FromTransNo = $_POST['FromTransNo'];
} else {
    $FromTransNo ='';
}

If (!isset($_POST['ToTransNo']) OR $_POST['ToTransNo']==''){
    $_POST['ToTransNo'] = $FromTransNo;
}
$FirstTrans = $FromTransNo; /*Need to start a new page only on subsequent transactions */

if (isset($PrintPDF) AND $PrintPDF!='' AND isset($FromTransNo) AND isset($InvOrCredit) AND $FromTransNo!='')
{

    if (isset($SessionSavePath)){
        session_save_path($SessionSavePath);
    }

    session_start();

/*check security - $PageSecurity set in files where this script is included from */
    if (! in_array($PageSecurity,$SecurityGroups[$_SESSION['AccessLevel']]) OR !isset($PageSecurity)){
        $title = _('Access Denied Error');
        include ('includes/header.inc');
        echo '<BR><BR><BR><BR><BR><BR><BR><CENTER><FONT COLOR=RED SIZE=4><B>' .
        _('The security settings on your account do not permit you to access this function') . '</B></FONT>';
        include('includes/footer.inc');
        exit;
    }

    /* This invoice is hard coded for A4 Landscape invoices or credit notes
     * so can't use PDFStarter.inc*/
    $Page_Width=842;
    $Page_Height=595;
    $Top_Margin=30;
    $Bottom_Margin=30;
    $Left_Margin=65;
    $Right_Margin=15;

    $PageSize = array(0,0,$Page_Width,$Page_Height);
    // 00044 - deprecated syntax
    //$pdf = & new Cpdf($PageSize);
    $pdf = new Cpdf($PageSize);
    $pdf->selectFont(Fonts::find('Helvetica'));
    $pdf->addinfo('Author','webERP ' . $Version);
    $pdf->addinfo('Creator','webERP http://www.weberp.org - R&OS PHP-PDF http://www.ros.co.nz');


    if ($InvOrCredit=='Invoice'){
        $pdf->addinfo('Title',_('Sales Invoice'));
        $pdf->addinfo('Subject',_('Invoices from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST['ToTransNo']);
    } else {
        $pdf->addinfo('Title',_('Sales Credit Note'));
        $pdf->addinfo('Subject',_('Credit Notes from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST['ToTransNo']);
    }

    $line_height=16;

    /*We have a range of invoices to print so get an array of all the company information */
    $CompanyRecord = ReadInCompanyRecord ($db);
    if ($CompanyRecord==0){
        /*CompanyRecord will be 0 if the company information could not be retrieved */
        exit;
    }


    while ($FromTransNo <= $_POST['ToTransNo']){

    /*retrieve the invoice details from the database to print
    notice that salesorder record must be present to print the invoice purging of sales orders will
    nobble the invoice reprints */

        if ($InvOrCredit=='Invoice') {
            $sql = 'SELECT DebtorTrans.TranDate,
                    DebtorTrans.OvAmount,
                    DebtorTrans.OvDiscount,
                    DebtorTrans.OvFreight,
                    DebtorTrans.OvGST,
                    DebtorTrans.Rate,
                    DebtorTrans.InvText,
                    DebtorTrans.Consignment,
                    DebtorsMaster.Name,
                    DebtorsMaster.CompanyName,
                    DebtorsMaster.Addr1,
                    DebtorsMaster.Addr2,
                    DebtorsMaster.MailStop,
                    DebtorsMaster.City,
                    DebtorsMaster.State,
                    DebtorsMaster.Zip,
                    DebtorsMaster.Country,
                    DebtorsMaster.CurrCode,
                    DebtorsMaster.InvAddrBranch,
                    PaymentTerms.Terms,
                    SalesOrders.DeliverTo,
                    SalesOrders.CompanyName DelCompanyName,
                    SalesOrders.Addr1 DelAddr1,
                    SalesOrders.Addr2 DelAddr2,
                    SalesOrders.MailStop DelMailStop,
                    SalesOrders.City DelCity,
                    SalesOrders.State DelState,
                    SalesOrders.Zip DelZip,
                    SalesOrders.Country DelCountry,
                    SalesOrders.ExtraLanguage,
                    SalesOrders.CustomerRef,
                    SalesOrders.OrderNo,
                    SalesOrders.OrdDate,
                    SalesOrders.CustomerRef,
                    Locations.LocationName,
                    Shippers.ShipperName,
                    CustBranch.BrName,
                    CustBranch.BrAddr1,
                    CustBranch.BrAddr2,
                    CustBranch.BrMailStop,
                    CustBranch.BrCity,
                    CustBranch.BrState,
                    CustBranch.BrZip,
                    CustBranch.BrCountry,
                    CustBranch.BrPostAddr1,
                    CustBranch.BrPostAddr2,
                    CustBranch.BrPostMailStop,
                    CustBranch.BrPostCity,
                    CustBranch.BrPostState,
                    CustBranch.BrPostZip,
                    CustBranch.BrPostCountry,
                    Salesman.SalesmanName,
                    DebtorTrans.DebtorNo,
                    DebtorTrans.BranchCode
                FROM DebtorTrans,
                    DebtorsMaster,
                    CustBranch,
                    SalesOrders,
                    Shippers,
                    Salesman,
                    Locations,
                    PaymentTerms
                WHERE DebtorTrans.Order_ = SalesOrders.OrderNo
                AND DebtorTrans.Type=10
                AND DebtorTrans.TransNo=' . $FromTransNo . '
                AND DebtorTrans.ShipVia=Shippers.Shipper_ID
                AND DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo
                AND DebtorsMaster.PaymentTerms=PaymentTerms.TermsIndicator
                AND DebtorTrans.DebtorNo=CustBranch.DebtorNo
                AND DebtorTrans.BranchCode=CustBranch.BranchCode
                AND CustBranch.Salesman=Salesman.SalesmanCode
                AND SalesOrders.FromStkLoc=Locations.LocCode';

            if ($_POST['PrintEDI']=='No'){
                $sql = $sql . ' AND DebtorsMaster.EDIInvoices=0';
            }
        } else {
            $sql = 'SELECT DebtorTrans.TranDate,
                    DebtorTrans.OvAmount,
                    DebtorTrans.OvDiscount,
                    DebtorTrans.OvFreight,
                    DebtorTrans.OvGST,
                    DebtorTrans.Rate,
                    DebtorTrans.InvText,
                    DebtorsMaster.InvAddrBranch,
                    DebtorsMaster.Name,
                    DebtorsMaster.CompanyName,
                    DebtorsMaster.Addr1,
                    DebtorsMaster.Addr2,
                    DebtorsMaster.MailStop,
                    DebtorsMaster.City,
                    DebtorsMaster.State,
                    DebtorsMaster.Zip,
                    DebtorsMaster.Country,
                    DebtorsMaster.CurrCode,
                    CustBranch.BrName,
                    CustBranch.BrName,
                    CustBranch.BrAddr1,
                    CustBranch.BrAddr2,
                    CustBranch.BrMailStop,
                    CustBranch.BrCity,
                    CustBranch.BrState,
                    CustBranch.BrZip,
                    CustBranch.BrCountry,
                    CustBranch.BrPostAddr1,
                    CustBranch.BrPostAddr2,
                    CustBranch.BrPostMailStop,
                    CustBranch.BrPostCity,
                    CustBranch.BrPostState,
                    CustBranch.BrPostZip,
                    CustBranch.BrPostCountry,
                    Salesman.SalesmanName,
                    DebtorTrans.DebtorNo,
                    DebtorTrans.BranchCode,
                    PaymentTerms.Terms
                FROM DebtorTrans,
                    DebtorsMaster,
                    CustBranch,
                    Salesman,
                    PaymentTerms
                WHERE DebtorTrans.Type=11
                AND DebtorsMaster.PaymentTerms = PaymentTerms.TermsIndicator
                AND DebtorTrans.TransNo=' . $FromTransNo .'
                AND DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo
                AND DebtorTrans.DebtorNo=CustBranch.DebtorNo
                AND DebtorTrans.BranchCode=CustBranch.BranchCode
                AND CustBranch.Salesman=Salesman.SalesmanCode';

            if ($_POST['PrintEDI']=='No'){
                $sql = $sql . ' AND DebtorsMaster.EDIInvoices=0';
            }
        }
        $result=DB_query($sql,$db);

        if (DB_error_no($db)!=0) {
            $title = _('Transaction Print Error Report');
            include ('includes/header.inc');

            echo '<BR>' . _('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $InvoiceToPrint . ' ' . _('from the database') . '. ' . _('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged') . '. ' . _('To print a credit note only requires the customer, transaction, salesman and branch records be available');
            if ($debug==1){
                echo _('The SQL used to get this information that failed was') . "<BR>$sql";
            }
            break;
            include ('includes/footer.inc');
            exit;
        }
        if (DB_num_rows($result)==1) {
        $myrow = DB_fetch_array($result);

        /* Use the osCommerce order number, if there is one. */
        $custRef = explode('#', $myrow['CustomerRef']);
        $ThisOrderNumber = ($custRef[0] == 'OSC' ) ? $myrow['CustomerRef'] : $myrow['OrderNo'];

        $ExchRate = $myrow['Rate'];

        if ($InvOrCredit=='Invoice'){

             $sql = 'SELECT StockMoves.StockID,
                    StockMaster.Description,
                    StockMaster.Harmonization,
                    StockMaster.Origin,
                    -StockMoves.Qty AS Quantity,
                    StockMoves.DiscountPercent,
                    ((1 - StockMoves.DiscountPercent) * StockMoves.Price * ' . $ExchRate . '* -StockMoves.Qty) AS FxNet,
                    (StockMoves.Price * ' . $ExchRate . ') AS FxPrice,
                    StockMoves.Narrative,
                    StockMaster.Units
                FROM StockMoves,
                    StockMaster
                WHERE StockMoves.StockID = StockMaster.StockID
                AND StockMoves.Type=10
                AND StockMoves.TransNo=' . $FromTransNo . '
                AND StockMoves.Show_On_Inv_Crds=1';
        } else {
        /* only credit notes to be retrieved */
             $sql = 'SELECT StockMoves.StockID,
                     StockMaster.Description,
                    StockMaster.Harmonization,
                    StockMoves.Qty AS Quantity,
                    StockMoves.DiscountPercent,
                    ((1 - StockMoves.DiscountPercent) * StockMoves.Price * ' . $ExchRate . ' * StockMoves.Qty) AS FxNet,
                    (StockMoves.Price * ' . $ExchRate . ') AS FxPrice,
                    StockMoves.Narrative,
                    StockMaster.Units
                FROM StockMoves,
                    StockMaster
                WHERE StockMoves.StockID = StockMaster.StockID
                AND StockMoves.Type=11
                AND StockMoves.TransNo=' . $FromTransNo . '
                AND StockMoves.Show_On_Inv_Crds=1';
        }

        $result=DB_query($sql,$db);
        if (DB_error_no($db)!=0) {
            $title = _('Transaction Print Error Report');
            include ('includes/header.inc');
            echo '<BR>' . _('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo . ' ' . _('from the database');
            if ($debug==1){
                echo '<BR>' . _('The SQL used to get this information that failed was') . "<BR>$sql";
            }
            include('includes/footer.inc');
            exit;
        }

        if (DB_num_rows($result)>0){

            $FontSize = 10;
            $PageNumber = 1;

            // 00044 - our PDF library can't do UTF-8, so we need to strip out
            // foreign characters.
            foreach ($myrow as &$column) {
                $column = utf8ToAscii($column);
            }

            if ($FromTransNo > $FirstTrans) {
                /* only initiate a new page if its not the first */
                $pdf->newPage();
            }
            include('includes/PDFTransPageHeader.inc');

            while ($myrow2=DB_fetch_array($result)) {

                $DisplayPrice = number_format($myrow2['FxPrice'],2);
                $DisplayQty = number_format($myrow2['Quantity'],2);
                $DisplayNet = number_format($myrow2['FxNet'],2);

                if ($myrow2['DiscountPercent']==0){
                    $DisplayDiscount ='';
                } else {
                    $DisplayDiscount = number_format($myrow2['DiscountPercent']*100,2) . '%';
                }

                $LeftOvers = $pdf->addTextWrap($Left_Margin+3,$YPos,95,$FontSize,$myrow2['StockID']);
                $LeftOvers = $pdf->addTextWrap($Left_Margin+100,$YPos,245,$FontSize,$myrow2['Description']);
                $LeftOvers = $pdf->addTextWrap($Left_Margin+390,$YPos,40,$FontSize,$myrow2['Origin']);
                $LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,$myrow2['Harmonization'],'right');
                $LeftOvers = $pdf->addTextWrap($Left_Margin+500,$YPos,60,$FontSize,$DisplayPrice,'right');
                $LeftOvers = $pdf->addTextWrap($Left_Margin+560,$YPos,60,$FontSize,$DisplayQty,'right');
                $LeftOvers = $pdf->addTextWrap($Left_Margin+630,$YPos,30,$FontSize,$myrow2['Units'],'left');
                $LeftOvers = $pdf->addTextWrap($Left_Margin+660,$YPos,40,$FontSize,$DisplayDiscount,'right');
                $LeftOvers = $pdf->addTextWrap($Left_Margin+700,$YPos,50,$FontSize,$DisplayNet,'right');

                $YPos -= ($line_height);

                $Narrative = $myrow2['Narrative'];
                while (strlen($Narrative)>1){
                    if ($YPos-$line_height <= $Bottom_Margin){
                        /* head up a new invoice/credit note page */
                        /*draw the vertical column lines right to the bottom */
                        PrintLinesToBottom ();
                        include ('includes/PDFTransPageHeader.inc');
                    } //end if need a new page headed up
                    /*increment a line down for the next line item */
                    if (strlen($Narrative)>1){
                        $Narrative = $pdf->addTextWrap($Left_Margin+100,$YPos,245,$FontSize,$Narrative);
                    }
                    $YPos -= ($line_height);
                }

            } //end while there are line items to print out
        } /*end if there are stock movements to show on the invoice or credit note*/

        $YPos -= $line_height;

        /* check to see enough space left to print the 4 lines for the totals/footer */
        if (($YPos-$Bottom_Margin)<(4*$line_height)){

            PrintLinesToBottom ();
            include ('includes/PDFTransPageHeader.inc');

        }
        /*Print a column vertical line    with enough space for the footer*/
        /*draw the vertical column lines to 4 lines shy of the bottom
        to leave space for invoice footer info ie totals etc*/
        $pdf->line($Left_Margin+97, $TopOfColHeadings+12,$Left_Margin+97,$Bottom_Margin+    (8*$line_height));
        $pdf->line($Left_Margin+370, $TopOfColHeadings+12,$Left_Margin+370,$Bottom_Margin+(8*$line_height));
        $pdf->line($Left_Margin+425, $TopOfColHeadings+12,$Left_Margin+425,$Bottom_Margin+(8*$line_height));
        $pdf->line($Left_Margin+510, $TopOfColHeadings+12,$Left_Margin+510,$Bottom_Margin+(8*$line_height));
        $pdf->line($Left_Margin+570, $TopOfColHeadings+12,$Left_Margin+570,$Bottom_Margin+(8*$line_height));
        $pdf->line($Left_Margin+625, $TopOfColHeadings+12,$Left_Margin+625,$Bottom_Margin+(8*$line_height));
        $pdf->line($Left_Margin+660, $TopOfColHeadings+12,$Left_Margin+660,$Bottom_Margin+(8*$line_height));
        $pdf->line($Left_Margin+703, $TopOfColHeadings+12,$Left_Margin+703,$Bottom_Margin+(8*$line_height));

        /*Rule off at bottom of the vertical lines */
        $pdf->line($Left_Margin, $Bottom_Margin+(8*$line_height),$Page_Width-$Right_Margin,$Bottom_Margin+(8*$line_height));

        /*Now print out the footer and totals */

        if ($InvOrCredit=='Invoice') {
            $DisplaySubTot = number_format($myrow['OvAmount'],2);
            $DisplayFreight = number_format($myrow['OvFreight'],2);
            $DisplayTax = number_format($myrow['OvGST'],2);
            $GrossTotal = $myrow['OvFreight']+$myrow['OvGST']+$myrow['OvAmount'];
            $DisplayTotal = number_format($GrossTotal,2);
            $payments_array = GetReceipts_AttachedToSalesOrder( $myrow['OrderNo'], $db);
        } else {
            $DisplaySubTot = number_format(-$myrow['OvAmount'],2);
            $DisplayFreight = number_format(-$myrow['OvFreight'],2);
            $DisplayTax = number_format(-$myrow['OvGST'],2);
            $GrossTotal = -$myrow['OvFreight']-$myrow['OvGST']-$myrow['OvAmount'];
            $DisplayTotal = number_format(-$myrow['OvFreight']-$myrow['OvGST']-$myrow['OvAmount'],2);
        }
    /*Print out the invoice text entered */
        $YPos = $Bottom_Margin+(7*$line_height);
    /* Print out the payment terms */

//            $pdf->addTextWrap($Left_Margin+5,$YPos+3,280,$FontSize,_('Payment Terms') . ': ' . $myrow['Terms']);
        $LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos-12,280,$FontSize,$myrow['InvText']);
        if (strlen($LeftOvers)>0){
            $LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos-24,280,$FontSize,$LeftOvers);
            if (strlen($LeftOvers)>0){
                $LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos-36,280,$FontSize,$LeftOvers);
                /*If there is some of the InvText leftover after 3 lines 200 wide then it is not printed :( */
            }
        }
        $FontSize = 10;
        $TITLE_MARGIN = $Page_Width-$Right_Margin-220;
        $SUM_MARGIN     = $Left_Margin+640;

        $Y_PAYMENTS = $YPos+5;
        $pdf->addText($TITLE_MARGIN, $Y_PAYMENTS,$FontSize, _('Sub Total'));
        $LeftOvers = $pdf->addTextWrap($SUM_MARGIN,$Y_PAYMENTS,120,$FontSize,$DisplaySubTot, 'right');

        $Y_PAYMENTS -= $line_height;
        $pdf->addText($TITLE_MARGIN, $Y_PAYMENTS,$FontSize, _('Freight'));
        $LeftOvers = $pdf->addTextWrap($SUM_MARGIN,$Y_PAYMENTS,120,$FontSize,$DisplayFreight, 'right');

        if ($DisplayTax != 0) {
                                    $Y_PAYMENTS -= $line_height;
            $pdf->addText($TITLE_MARGIN, $Y_PAYMENTS,$FontSize, 'Tax');
            $LeftOvers = $pdf->addTextWrap($SUM_MARGIN,$Y_PAYMENTS,120, $FontSize,$DisplayTax, 'right');
        }

        /*rule off for total */
//        $pdf->line($TITLE_MARGIN-2, $YPos-(2*$line_height),$Page_Width-$Right_Margin,$YPos-(2*$line_height));

        /*vertical to seperate totals from comments and ROMALPA */
        $pdf->line($TITLE_MARGIN-2, $YPos+$line_height,$TITLE_MARGIN-2,$Bottom_Margin);

        if ($InvOrCredit=='Invoice'){
//            $pdf->addText($Page_Width-$Right_Margin-220, 35,$FontSize, _('TOTAL INVOICE'));
//            $allocSQL = "    SELECT -DT_From.OvAmount as PAID FROM CustAllocns
//                    LEFT JOIN DebtorTrans DT_To     ON CustAllocns.TransID_AllocTo    = DT_To.ID     AND DT_To.Type     =10
//                    LEFT JOIN DebtorTrans DT_From ON CustAllocns.TransID_AllocFrom    = DT_From.ID AND DT_From.Type =12
//                    WHERE DT_To.Type=10 AND DT_To.TransNo = '" . $FromTransNo ."'";
//            $allocRES = DB_fetch_array(DB_query($allocSQL,$db));
//                                            $payment_status = ($allocRES['PAID'] == "" ? "" : 'Paid: $' . number_format($allocRES['PAID'],2));

//            $LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos,245,$FontSize,$RomalpaClause);
//                                                $LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos-$line_height+5,245,$FontSize,$payment_status );
//            $pdf->addText($Left_Margin+60, $YPos-($line_height*0),$FontSize,ConvertSQLDate($myrow['TranDate']));$YPos-=$line_height;

            $payment_total = 0;
            $Y_PAYMENTS -= $line_height;
            $LeftOvers = $pdf->addTextWrap($TITLE_MARGIN,$Y_PAYMENTS,120,$FontSize,'GROSS INVOICE');
            $LeftOvers = $pdf->addTextWrap($SUM_MARGIN,$Y_PAYMENTS,    120,$FontSize,$DisplayTotal, 'right');
            if ($payments_array) {
                while ( $this_payment = DB_fetch_array($payments_array)) {
                    $Y_PAYMENTS -= $line_height;
                    $LeftOvers = $pdf->addTextWrap($TITLE_MARGIN,$Y_PAYMENTS,120,$FontSize,'Paid on ' . Date('Y/m/d',strtotime($this_payment['TranDate'])));
                    $LeftOvers = $pdf->addTextWrap($SUM_MARGIN,$Y_PAYMENTS,120,$FontSize,number_format( -$this_payment['OvAmount'],2), 'right');
                    $payment_total -=$this_payment['OvAmount'] ;
                }
                $Y_PAYMENTS -= $line_height;
                $LeftOvers = $pdf->addTextWrap($TITLE_MARGIN,$Y_PAYMENTS,120,$FontSize,'TOTAL PAYMENTS');
                $LeftOvers = $pdf->addTextWrap($SUM_MARGIN,$Y_PAYMENTS,120,$FontSize,number_format($payment_total,2), 'right');
                $Y_PAYMENTS -= $line_height;
                $LeftOvers = $pdf->addTextWrap($TITLE_MARGIN,$Y_PAYMENTS,120,$FontSize,'NET DUE (OVERPAID)');
                $LeftOvers = $pdf->addTextWrap($SUM_MARGIN,$Y_PAYMENTS,120,$FontSize,number_format($GrossTotal - $payment_total,2),'right');
            }
            $Y_PAYMENTS -= 4;
            $pdf->addText($TITLE_MARGIN, $Y_PAYMENTS-= $line_height, $FontSize+2, _('All amounts stated in') . ' - ' . $myrow['CurrCode']);

            if ($myrow['ExtraLanguage']==1) {
                $LeftOvers = 'This item is being returned.';
            } elseif ($myrow['ExtraLanguage']==2) {
                $LeftOvers = 'This is an item in transit being returned to its original owner after repair.';
            }
            $iii = 1;
            if ( ($myrow['ExtraLanguage']!=0) && $LeftOvers ) {
                $LeftOvers = $pdf->addText($Left_Margin+60, $YPos-($line_height*$iii),$FontSize,$LeftOvers);
                $iii++;
            }
        } else {
            $pdf->addText($Page_Width-$Right_Margin-220, $YPos-($line_height*3),$FontSize, _('TOTAL CREDIT'));
         }
//        $LeftOvers = $pdf->addTextWrap($Left_Margin+642,35,120, $FontSize,$DisplayTotal, 'right');
        } /* end of check to see that there was an invoice record to print */

        $FromTransNo++;
    } /* end loop to print invoices */

    $FromTransNo--;
    $pdfcode = $pdf->output();
    $len = strlen($pdfcode);

    if ($len <1020){
        include('includes/header.inc');
        echo '<P>' . _('There were no transactions to print in the range selected');
        include('includes/footer.inc');
        exit;
    }

    if (isset($_GET['Email'])){ //email the invoice to address supplied

        $mail = new htmlMimeMail();
        $filename = $reports_dir . '/' . trim($InvOrCredit) . trim($FromTransNo) . '.pdf';
        $fp = fopen($filename, 'wb');
        fwrite ($fp, $pdfcode);
        fclose ($fp);

        $attachment = $mail->getFile($filename);
        $theText = "
Hi.
I am attaching for your information $InvOrCredit # $FromTransNo.
This should include the UPS tracking number related
to our order number $ThisOrderNumber.

Jack

(This email automatically generated by the Gumstix Customer Support System)
";
        $mail->setText($theText);
        $mail->SetSubject($InvOrCredit . ' ' . $FromTransNo);
        $mail->addAttachment($attachment, $filename, 'application/pdf');
        $mail->setFrom($CompanyName . '<' . $CompanyRecord['Email'] . '>');
        $result = $mail->send(array($_GET['Email']));

        unlink($filename); //delete the temporary file

        $title = _('Emailing') . ' ' .$InvOrCredit . ' ' . _('Number') . ' ' . $FromTransNo;
        include('includes/header.inc');
        echo "<P>$InvOrCredit " . _('number') . ' ' . $FromTransNo . ' ' . _('has been emailed to') . ' ' . $_GET['Email'];
        include('includes/footer.inc');
        exit;

    } else {
        header('Content-type: application/pdf');
        header('Content-Length: ' . $len);
        header('Content-Disposition: inline; filename=Customer_trans.pdf');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        $pdf->Stream();

        if (! file_exists($reports_dir . '/CustomerInvoices') ) {
            mkdir($reports_dir . '/CustomerInvoices');
        }
        $filename = $reports_dir . '/CustomerInvoices/' . trim($InvOrCredit) . trim($FromTransNo);
        $fn = fopen($filename.'.pdf', 'wb');
        if ( $fn ) {
            fwrite ($fn, $pdfcode);
            fclose ($fn);
        }

        system("/usr/bin/pdftops $filename" . '.pdf');
        try {
            $fp = Printer::openStandard();
        }
        catch ( PrinterException $ex ) {
            prnMsg($ex->getMessage(), 'error');
        }
        if ($fp) {
            $pshandle = fopen($filename . '.ps','rb');
            $psdata=fread($pshandle, filesize($filename . '.ps'));
            fwrite($fp, $psdata);
            if ($_GET['Copies']==3) {
                fwrite($fp, $psdata);
                fwrite($fp, $psdata);
            }
            fclose($pshandle);
        }
        fclose($fp);
    }

} else { /*The option to print PDF was not hit */

    $title=_('Select Invoices/Credit Notes To Print');
    include('includes/header.inc');

    if (!isset($FromTransNo) OR $FromTransNo=='') {


    /*if FromTransNo is not set then show a form to allow input of either a single invoice number or a range of invoices to be printed. Also get the last invoice number created to show the user where the current range is up to */

        echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "' METHOD='POST'><CENTER><TABLE>";

        echo '<TR><TD>Print Invoices or Credit Notes</TD><TD><SELECT name=InvOrCredit>';
        if ($InvOrCredit=='Invoice' OR !isset($InvOrCredit)){

             echo "<OPTION SELECTED VALUE='Invoice'>" . _('Invoices');
             echo "<OPTION VALUE='Credit'>" . _('Credit Notes');

        } else {

             echo "<OPTION SELECTED VALUE='Credit'>" . _('Credit Notes');
             echo "<OPTION VALUE='Invoice'>" . _('Invoices');

        }

        echo '</SELECT></TD></TR>';

        echo '<TR><TD>' . _('Print EDI Transactions') . '</TD><TD><SELECT name=PrintEDI>';
        if ($InvOrCredit=='Invoice' OR !isset($InvOrCredit)){

             echo "<OPTION SELECTED VALUE='No'>" . _('Do not Print PDF EDI Transactions');
             echo "<OPTION VALUE='Yes'>" . _('Print PDF EDI Transactions Too');

        } else {

             echo "<OPTION VALUE='No'>" . _('Do not Print PDF EDI Transactions');
             echo "<OPTION SELECTED VALUE='Yes'>" . _('Print PDF EDI Transactions Too');

        }

        echo '</SELECT></TD></TR>';
        echo '<TR><TD>' . _('Start invoice/credit note number to print') . '</TD><TD><input Type=text max=6 size=7 name=FromTransNo></TD></TR>';
        echo '<TR><TD>' . _('End invoice/credit note number to print') . "</TD><TD><input Type=text max=6 size=7 name='ToTransNo'></TD></TR></TABLE></CENTER>";
        echo "<CENTER><INPUT TYPE=Submit Name='Print' Value='" . _('Print') . "'><P>";
        echo "<INPUT TYPE=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></CENTER>";

        $sql = 'SELECT TypeNo FROM SysTypes WHERE TypeID=10';

        $result = DB_query($sql,$db);
        $myrow = DB_fetch_row($result);

        echo '<P>' . _('The last invoice created was number') . ' ' . $myrow[0] . '<BR>' . _('If only a single invoice is required') . ', ' . _('enter the invoice number to print in the Start transaction number to print field and leave the End transaction number to print field blank') . '. ' . _('Only use the end invoice to print field if you wish to print a sequential range of invoices');

        $sql = 'SELECT TypeNo FROM SysTypes WHERE TypeID=11';

        $result = DB_query($sql,$db);
        $myrow = DB_fetch_row($result);

        echo '<P>' . _('The last credit note created was number') . ' ' . $myrow[0] . '<BR>' . _('A sequential range can be printed using the same method as for invoices above') . '. ' . _('A single credit note can be printed by only entering a start transaction number');

    } else {

    /*We have a range of invoices to print so get an array of all the company information */
        $CompanyRecord = ReadInCompanyRecord ($db);
        if ($CompanyRecord==0){
            /*CompanyRecord will be 0 if the company information could not be retrieved */
            exit;
        }

        while ($FromTransNo <= $_POST['ToTransNo']){

    /*retrieve the invoice details from the database to print
    notice that salesorder record must be present to print the invoice purging of sales orders will
    nobble the invoice reprints */

            if ($InvOrCredit=='Invoice') {

                 $sql = "SELECT
                         DebtorTrans.TranDate,
                    DebtorTrans.OvAmount,
                    DebtorTrans.OvDiscount,
                    DebtorTrans.OvFreight,
                    DebtorTrans.OvGST,
                    DebtorTrans.Rate,
                    DebtorTrans.InvText,
                    DebtorTrans.Consignment,
                                                                                DebtorsMaster.Name,
                    DebtorsMaster.CompanyName,
                    DebtorsMaster.Addr1,
                    DebtorsMaster.Addr2,
                    DebtorsMaster.MailStop,
                    DebtorsMaster.City,
                    DebtorsMaster.State,
                    DebtorsMaster.Zip,
                    DebtorsMaster.Country,
                    DebtorsMaster.CurrCode,
                    SalesOrders.DeliverTo,
                                                                                SalesOrders.CompanyName DelCompanyName,
                    SalesOrders.Addr1 DelAddr1,
                    SalesOrders.Addr2 DelAddr2,
                    SalesOrders.MailStop DelMailStop,
                    SalesOrders.City DelCity,
                    SalesOrders.State DelState,
                    SalesOrders.Zip DelZip,
                    SalesOrders.Country DelCountry,
                    SalesOrders.CustomerRef,
                    SalesOrders.OrderNo,
                    SalesOrders.OrdDate,
                    Shippers.ShipperName,
                    CustBranch.BrName,
                    CustBranch.BrName,
                    CustBranch.BrAddr1,
                    CustBranch.BrAddr2,
                    CustBranch.BrMailStop,
                    CustBranch.BrCity,
                    CustBranch.BrState,
                    CustBranch.BrZip,
                    CustBranch.BrCountry,
                    Salesman.SalesmanName,
                    DebtorTrans.DebtorNo
                FROM DebtorTrans,
                    DebtorsMaster,
                    CustBranch,
                    SalesOrders,
                    Shippers,
                    Salesman
                WHERE DebtorTrans.Order_ = SalesOrders.OrderNo
                AND DebtorTrans.Type=10
                AND DebtorTrans.TransNo=" . $FromTransNo . "
                AND DebtorTrans.ShipVia=Shippers.Shipper_ID
                AND DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo
                AND DebtorTrans.DebtorNo=CustBranch.DebtorNo
                AND DebtorTrans.BranchCode=CustBranch.BranchCode
                AND CustBranch.Salesman=Salesman.SalesmanCode";
            } else {

                 $sql = 'SELECT DebtorTrans.TranDate,
                         DebtorTrans.OvAmount,
                    DebtorTrans.OvDiscount,
                    DebtorTrans.OvFreight,
                    DebtorTrans.OvGST,
                    DebtorTrans.Rate,
                    DebtorTrans.InvText,
                    DebtorsMaster.Name,
                                                                                DebtorsMaster.CompanyName,
                    DebtorsMaster.Addr1,
                    DebtorsMaster.Addr2,
                    DebtorsMaster.MailStop,
                    DebtorsMaster.City,
                    DebtorsMaster.State,
                    DebtorsMaster.Zip,
                    DebtorsMaster.Country,
                    DebtorsMaster.CurrCode,
                    CustBranch.BrName,
                    CustBranch.BrAddr1,
                    CustBranch.BrAddr2,
                    CustBranch.BrMailStop,
                    CustBranch.BrCity,
                    CustBranch.BrState,
                    CustBranch.BrZip,
                    CustBranch.BrCountry,
                    Salesman.SalesmanName,
                    DebtorTrans.DebtorNo,
                    DebtorTrans.BranchCode
                FROM DebtorTrans,
                    DebtorsMaster,
                    CustBranch,
                    Salesman
                WHERE DebtorTrans.Type=11
                AND DebtorTrans.TransNo=' . $FromTransNo . '
                AND DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo
                AND DebtorTrans.DebtorNo=CustBranch.DebtorNo
                AND DebtorTrans.BranchCode=CustBranch.BranchCode
                AND CustBranch.Salesman=Salesman.SalesmanCode';

            }

            $result=DB_query($sql,$db);
            if (DB_num_rows($result)==0 OR DB_error_no($db)!=0) {
                echo '<P>' . _('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $InvoiceToPrint . ' ' . _('from the database') . '. ' . _('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged') . '. ' . _('To print a credit note only requires the customer, transaction, salesman and branch records be available');
                if ($debug==1){
                    echo _('The SQL used to get this information that failed was') . "<BR>$sql";
                }
                break;
                include('includes/footer.inc');
                exit;
            } elseif (DB_num_rows($result)==1){

                $myrow = DB_fetch_array($result);
    /* Then there's an invoice (or credit note) to print. So print out the invoice header and GST Number from the company record */
                if (count($SecurityGroups[$_SESSION['AccessLevel']])==1 AND in_array(1, $SecurityGroups[$_SESSION['AccessLevel']]) AND $myrow['DebtorNo'] != $_SESSION['CustomerID']){
                    echo '<P><FONT COLOR=RED SIZE=4>' . _('This transaction is addressed to another customer and cannot be displayed for privacy reasons') . '. ' . _('Please select only transactions relevant to your company');
                    exit;
                }

                $ExchRate = $myrow['Rate'];
                $PageNumber = 1;

                echo "<TABLE WIDTH=100%><TR><TD VALIGN=TOP WIDTH=10%><img src='logo.jpg'></TD><TD BGCOLOR='#BBBBBB'><B>";

                if ($InvOrCredit=='Invoice') {
                     echo '<FONT SIZE=4>' . _('INVOICE') . ' ';
                } else {
                     echo '<FONT COLOR=RED SIZE=4>' . _('CREDIT NOTE') . ' ';
                }
                echo '</B>' . _('Number') . ' ' . $FromTransNo . '</FONT></TD></TR></TABLE>';

    /*Now print out the logo and company name and address */
                echo "<TABLE WIDTH=100%><TR><TD VALIGN=TOP >";
//                echo $CompanyRecord['PostalAddress'] . '<BR>';
                echo $CompanyRecord['RegOffice1'] . '<BR>';
                echo $CompanyRecord['RegOffice2'] . '<BR>';
                echo _('Email') . ': ' . $CompanyRecord['Email'];

                echo '</TD><TD WIDTH=50% ALIGN=RIGHT>';

    /*Now the customer charged to details in a sub table within a cell of the main table*/

                echo "<TABLE WIDTH=100%><TR><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Charge To') . ":</B></TD></TR><TR><TD BGCOLOR='#EEEEEE'>";
                if ($myrow['CompanyName']!=""){
                    echo $myrow['CompanyName'] . '<BR>';
                }
                echo $myrow['Name'] . '<BR>' . $myrow['Addr1'] . '<BR>';
                if ($myrow['Addr2'].$myrow['MailStop'] != "") {
                    echo $myrow['Addr2'] . ' ' . $myrow['MailStop'] .    '<BR>';
                }
                echo $myrow['City'] . ', ' . $myrow['State'] . ' ' . $myrow['Zip'] . '<BR>' . $myrow['Country'];
                echo '</TD></TR></TABLE>';
                /*end of the small table showing charge to account details */
                echo _('Page') . ': ' . $PageNumber;
                echo '</TD></TR></TABLE>';
                /*end of the main table showing the company name and charge to details */

                if ($InvOrCredit=='Invoice') {
                    $allocSQL = "    SELECT -DT_From.OvAmount as PAID FROM CustAllocns
                            LEFT JOIN DebtorTrans DT_To     ON CustAllocns.TransID_AllocTo    = DT_To.ID     AND DT_To.Type     =10
                            LEFT JOIN DebtorTrans DT_From ON CustAllocns.TransID_AllocFrom    = DT_From.ID AND DT_From.Type =12
                            WHERE DT_To.Type=10 AND DT_To.TransNo =" . $FromTransNo ;
                    $allocRES = DB_fetch_array(DB_query($allocSQL,$db));
                    $payment_status = ($allocRES['PAID'] == "" ? "" : 'Paid: $' . number_format($allocRES['PAID'],2));
                     echo "<TABLE WIDTH=100%>
                                 <TR>
                                     <TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Charge Branch') . ":</B></TD>
                                <TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Delivered To') . ":</B></TD>
                            </TR>";
                     echo "<TR><TD BGCOLOR='#EEEEEE'>" . $myrow['BrName'] . '<BR>' . $myrow['BrAddr1'] . '<BR>';
                     if ($myrow['BrAddr2'].$myrow['BrMailStop'] != "") {
                    echo $myrow['BrAddr2'] . '    ' . $myrow['BrMailStop'] . '<BR>';
                     }
                     echo $myrow['BrCity'] . ', ' . $myrow['BrState'] . ' ' . $myrow['BrZip'] . '<BR>' . $myrow['BrCountry'] . '</TD>';
                                                                     echo "<TD BGCOLOR='#EEEEEE'>" . $myrow['DeliverTo'] . '<BR>' . $myrow['DelCompanyName'] . '<BR>';
                            echo "<TD BGCOLOR='#EEEEEE'>" . $myrow['DeliverTo'] . '<BR>' . $myrow['DelAddr1'] . '<BR>';
                                                                     if ($myrow['DelAddr2'].$myrow['DelMailStop'] != "") {
                         echo $myrow['DelAddr2'] . ' ' . $myrow['DelMailStop'] . '<BR>';
                     }
                     echo $myrow['DelCity'] . ', ' . $myrow['DelState'] . ' ' . $myrow['DelZip'] . '<BR>' . $myrow['DelCountry'] . '</TD>';
                     echo '</TR>
                     </TABLE><HR>';
                     $inq_link = 'http://wwwapps.ups.com/etracking/tracking.cgi?TypeOfInquiryNumber=T&UPS_HTML_License=FBA9FD95C46E8FC0&UPS_HTML_Version=3.0&IATA=us&Lang=en&submit=Track+Package&InquiryNumber1=';
                     echo "<TABLE WIDTH=100%>
                             <TR>
                            <TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Your Order Ref') . "</B></TD>
                            <TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Our Order No') . "</B></TD>
                                                                                                                <TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Order Date') . "</B></TD>
                            <TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Payment status') . "</B></TD>
                            <TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Invoice Date') . "</B></TD>
                            <TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Sales Person') . "</FONT></B></TD>
                            <TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Shipper') . "</B></TD>
                            <TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Tracking Number') . "</B></TD>
                        </TR>";
                         echo "<TR>
                            <TD BGCOLOR='#EEEEEE'>" . $myrow['CustomerRef'] . "</TD>
                            <TD BGCOLOR='#EEEEEE'>" .$myrow['OrderNo'] . "</TD>
                            <TD BGCOLOR='#EEEEEE'>" . ConvertSQLDate($myrow['OrdDate']) . "</TD>
                                                                                                                <TD BGCOLOR='#EEEEEE'>" . $payment_status . "</TD>
                            <TD BGCOLOR='#EEEEEE'>" . ConvertSQLDate($myrow['TranDate']) . "</TD>
                            <TD BGCOLOR='#EEEEEE'>" . $myrow['SalesmanName'] . "</TD>
                            <TD BGCOLOR='#EEEEEE'>" . $myrow['ShipperName'] . "</TD>
                            <TD BGCOLOR='#EEEEEE'><A target='_blank' HREF='" . $inq_link .$myrow['Consignment']."'>" .
                                 $myrow['Consignment'] .
                                     "</A></TD>
                        </TR>
                    </TABLE>";

                     $sql ="SELECT StockMoves.StockID,KGS,
                             StockMaster.Description,
                        -StockMoves.Qty AS Quantity,
                        StockMoves.DiscountPercent,
                        ((1 - StockMoves.DiscountPercent) * StockMoves.Price * " . $ExchRate . '* -StockMoves.Qty) AS FxNet,
                        (StockMoves.Price * ' . $ExchRate . ') AS FxPrice,
                        StockMoves.Narrative,
                        StockMaster.Units
                    FROM StockMoves,
                        StockMaster
                    WHERE StockMoves.StockID = StockMaster.StockID
                    AND StockMoves.Type=10
                    AND StockMoves.TransNo=' . $FromTransNo . '
                    AND StockMoves.Show_On_Inv_Crds=1';

                } else { /* then its a credit note */

                     echo "<TABLE WIDTH=50%><TR>
                             <TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Branch') . ":</B></TD>
                        </TR>";
                     echo "<TR>
                             <TD BGCOLOR='#EEEEEE'>" .$myrow['BrName'] . '<BR>' . $myrow['BrAddr1'] . '<BR>' . $myrow['BrAddr2'] . '<BR>' . $myrow['BrMailStop'] . '<BR>' . $myrow['BrCity'] . ', ' . $myrow['BrState'] . ' ' . $myrow['BrZip'] . '<BR>' . $myrow['BrCountry'] . '</TD>
                    </TR></TABLE>';
                     echo "<HR><TABLE WIDTH=100%><TR>
                             <TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Date') . "</B></TD>
                        <TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Sales Person') . "</FONT></B></TD>
                    </TR>";
                     echo "<TR>
                             <TD BGCOLOR='#EEEEEE'>" . ConvertSQLDate($myrow['TranDate']) . "</TD>
                        <TD BGCOLOR='#EEEEEE'>" . $myrow['SalesmanName'] . '</TD>
                    </TR></TABLE>';

                     $sql ='SELECT StockMoves.StockID,
                             StockMaster.Description,
                        StockMaster.Origin,
                        StockMoves.Qty AS Quantity,
                        StockMoves.DiscountPercent, ((1 - StockMoves.DiscountPercent) * StockMoves.Price * ' . $ExchRate . ' * StockMoves.Qty) AS FxNet,
                        (StockMoves.Price * ' . $ExchRate . ') AS FxPrice,
                        StockMaster.Units
                    FROM StockMoves,
                        StockMaster
                    WHERE StockMoves.StockID = StockMaster.StockID
                    AND StockMoves.Type=11
                    AND StockMoves.TransNo=' . $FromTransNo . '
                    AND StockMoves.Show_On_Inv_Crds=1';
                }

                echo '<HR>';
                echo '<CENTER><FONT SIZE=2>' . _('All amounts stated in') . ' ' . $myrow['CurrCode'] . '</FONT></CENTER>';

                $result=DB_query($sql,$db);
                if (DB_error_no($db)!=0) {
                    echo '<BR>' . _('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo . ' ' . _('from the database');
                    if ($debug==1){
                         echo '<BR>' . _('The SQL used to get this information that failed was') . "<BR>$sql";
                    }
                    exit;
                }

                if (DB_num_rows($result)>0){
                    echo "<TABLE WIDTH=100% CELLPADDING=5><TR><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Item Code') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Item Description') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Quantity') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Unit') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Price') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Discount') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Net') . '</B></TD></TR>';

                    $LineCounter =17;
                    $k=0;    //row colour counter

                    while ($myrow2=DB_fetch_array($result)){

                                if ($k==1){
                            $RowStarter = "<tr bgcolor='#BBBBBB'>";
                            $k=0;
                                } else {
                            $RowStarter = "<tr bgcolor='#EEEEEE'>";
                            $k=1;
                                }

                                echo $RowStarter;

                                $DisplayPrice = number_format($myrow2['FxPrice'],2);
                                $DisplayQty = number_format($myrow2['Quantity'],2);
                                $DisplayNet = number_format($myrow2['FxNet'],2);

                                if ($myrow2['DiscountPercent']==0){
                             $DisplayDiscount ='';
                                } else {
                             $DisplayDiscount = number_format($myrow2['DiscountPercent']*100,2) . '%';
                                }

                                printf ('<TD>%s</TD>
                                        <TD>%s</TD>
                            <TD ALIGN=RIGHT>%s</TD>
                            <TD ALIGN=RIGHT>%s</TD>
                            <TD ALIGN=RIGHT>%s</TD>
                            <TD ALIGN=RIGHT>%s</TD>
                            <TD ALIGN=RIGHT>%s</TD>
                            </TR>',
                            $myrow2['StockID'],
                            $myrow2['Description'],
                            $DisplayQty,
                            $myrow2['Units'],
                            $DisplayPrice,
                            $DisplayDiscount,
                            $DisplayNet);
/* SED Preparation */
                        $thisLine['StockID'] = $myrow2['StockID'];
                        $thisLine['Price']     = $DisplayPrice;
                                                                                                $thisLine['Weight'] = $myrow2['KGS'];
                                                                                                $thisLine['QtyDispatched'] = $DisplayQty;
                                                                                                $thisLine['ItemDescription'] = $myrow2['Description'];
                        $LineItems[] = $thisLine;

                                if (strlen($myrow2['Narrative'])>1){
                                        echo $RowStarter . '<TD></TD><TD COLSPAN=6>' . $myrow2['Narrative'] . '</TD></TR>';
                            $LineCounter++;
                                }

                                $LineCounter++;

                                if ($LineCounter == ($PageLength - 2)){

                        /* head up a new invoice/credit note page */

                             $PageNumber++;
                             echo "</TABLE><TABLE WIDTH=100%><TR><TD VALIGN=TOP><img src='logo.jpg'></TD><TD BGCOLOR='#BBBBBB'><CENTER><B>";

                             if ($InvOrCredit=='Invoice') {
                                    echo '<FONT SIZE=4>' . _('INVOICE') . ' ';
                             } else {
                                    echo '<FONT COLOR=RED SIZE=4>' . _('CREDIT NOTE') . ' ';
                             }
                             echo '</B>' . _('Number') . ' ' . $FromTransNo . '</FONT><BR><FONT SIZE=1>' . _('GST Number') . ' - ' . $CompanyRecord['GSTNo'] . '</TD></TR><TABLE>';

    /*Now print out company name and address */
                                echo "<TABLE WIDTH=100%><TR><TD><FONT SIZE=4 COLOR='#333333'><B>$CompanyName</B></FONT><BR>";
                                echo $CompanyRecord['PostalAddress'] . '<BR>';
                                echo $CompanyRecord['RegOffice1'] . '<BR>';
                                echo $CompanyRecord['RegOffice2'] . '<BR>';
                                echo _('Telephone') . ': ' . $CompanyRecord['Telephone'] . '<BR>';
                                echo _('Facsimile') . ': ' . $CompanyRecord['Fax'] . '<BR>';
                                echo _('Email') . ': ' . $CompanyRecord['Email'] . '<BR>';
                                echo '</TD><TD ALIGN=RIGHT>' . _('Page') . ": $PageNumber</TD></TR></TABLE>";
                                echo "<TABLE WIDTH=100% CELLPADDING=5><TR><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Item Code') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Item Description') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Quantity') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Unit') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Price') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Discount') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Net') . "</B></TD></TR>";

                                $LineCounter = 10;

                                } //end if need a new page headed up
                    } //end while there are line items to print out
                    echo '</TABLE>';
                } /*end if there are stock movements to show on the invoice or credit note*/

                /* check to see enough space left to print the totals/footer */
                $LinesRequiredForText = floor(strlen($myrow['InvText'])/140);

                if ($LineCounter >= ($PageLength - 8 - $LinesRequiredFortext)){

                    /* head up a new invoice/credit note page */

                    $PageNumber++;
                    echo "<TABLE WIDTH=100%><TR><TD VALIGN=TOP><img src='logo.jpg'></TD><TD BGCOLOR='#BBBBBB'><CENTER><B>";

                    if ($InvOrCredit=='Invoice') {
                                echo '<FONT SIZE=4>' . _('INVOICE') .' ';
                    } else {
                                echo '<FONT COLOR=RED SIZE=4>' . _('CREDIT NOTE') . ' ';
                    }
                    echo '</B>' . _('Number') . ' ' . $FromTransNo . '</FONT><BR><FONT SIZE=1>' . _('GST Number') . ' - ' . $CompanyRecord['GSTNo'] . '</TD></TR><TABLE>';

    /*Print out the logo and company name and address */
                    echo "<TABLE WIDTH=100%><TR><TD><FONT SIZE=4 COLOR='#333333'><B>$CompanyName</B></FONT><BR>";
                    echo $CompanyRecord['PostalAddress'] . '<BR>';
                    echo $CompanyRecord['RegOffice1'] . '<BR>';
                    echo $CompanyRecord['RegOffice2'] . '<BR>';
                    echo _('Telephone') . ': ' . $CompanyRecord['Telephone'] . '<BR>';
                    echo _('Facsimile') . ': ' . $CompanyRecord['Fax'] . '<BR>';
                    echo _('Email') . ': ' . $CompanyRecord['Email'] . '<BR>';
                    echo '</TD><TD ALIGN=RIGHT>' . _('Page') . ": $PageNumber</TD></TR></TABLE>";
                    echo "<TABLE WIDTH=100% CELLPADDING=5><TR><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Item Code') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Item Description') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Quantity') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Unit') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Price') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Discount') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Net') . '</B></TD></TR>';

                    $LineCounter = 10;
                }

    /*Space out the footer to the bottom of the page */

                echo '<BR><BR>' . $myrow['InvText'];

                $LineCounter=$LineCounter+2+$LinesRequiredForText;
                while ($LineCounter < ($PageLength -16)){
                    echo '<BR>';
                    $LineCounter++;
                }

    /*Now print out the footer and totals */

                if ($InvOrCredit=='Invoice') {

                     $DisplaySubTot = number_format($myrow['OvAmount'],2);
                     $DisplayFreight = number_format($myrow['OvFreight'],2);
                     $DisplayTax = number_format($myrow['OvGST'],2);
                     $DisplayTotal = number_format($myrow['OvFreight']+$myrow['OvGST']+$myrow['OvAmount'],2);
                     $payments_array = GetReceipts_AttachedToSalesOrder( $myrow['OrderNo'], $db);
                } else {
                     $DisplaySubTot = number_format(-$myrow['OvAmount'],2);
                     $DisplayFreight = number_format(-$myrow['OvFreight'],2);
                     $DisplayTax = number_format(-$myrow['OvGST'],2);
                     $DisplayTotal = number_format(-$myrow['OvFreight']-$myrow['OvGST']-$myrow['OvAmount'],2);
                }
    /*Print out the invoice text entered */
                echo '<TABLE WIDTH=100%><TR><TD ALIGN=RIGHT>' . _('Sub Total') . "</TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE' WIDTH=15%>$DisplaySubTot</TD></TR>";
                echo '<TR><TD ALIGN=RIGHT>' . _('Freight') . "</TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE'>$DisplayFreight</TD></TR>";
                if ($DisplayTax!=0) {
                    echo '<TR><TD ALIGN=RIGHT>' . _('Tax') . "</TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE'>$DisplayTax</TD></TR>";
                }
                if ($InvOrCredit=='Invoice'){
                         $payment_total = 0;
                         echo '<TR><TD Align=RIGHT><B>' . _('TOTAL INVOICE') . "</B></TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE'><U><B>$DisplayTotal</B></U></TD></TR>";
                         if ($payments_array) {
                        while ( $this_payment = DB_fetch_array($payments_array)) {
                             echo "<TR><TD Align=RIGHT>Paid on " . Date('Y/m/d',strtotime($this_payment['TranDate'])) . "</TD>";
                             echo "<TD ALIGN=RIGHT BGCOLOR='#EEEEEE'>" .number_format( -$this_payment['OvAmount'],2) . "</TD></TR><TR>";
                             $payment_total -=$this_payment['OvAmount'] ;
                     }
                            echo '<TR><TD Align=RIGHT><B>TOTAL PAYMENTS</B></TD>';
                            echo "<TD ALIGN=RIGHT BGCOLOR='#EEEEEE'><U><B>" . number_format($payment_total,2) . "</B></U></TD></TR>";
                            echo '<TR><TD Align=RIGHT><B>TOTAL DUE (OVERPAID)</B></TD>';
                            echo "<TD ALIGN=RIGHT BGCOLOR='#EEEEEE'>" . number_format($GrossTotal - $payment_total,2) . "</TD></TR>";
                         }
                } else {
                         echo '<TR><TD Align=RIGHT><FONT COLOR=RED><B>' . _('TOTAL CREDIT') . "</B></FONT></TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE'><FONT COLOR=RED><U><B>$DisplayTotal</B></U></FONT></TD></TR>";
                    $OfferRefund= 'Offer';
                }
                echo '</TABLE>';
                $AskForSED=true;
                if ( $AskForSED == true ) {
                    echo "<form target='blank' method='post' action='".$rootpath."/Document.php?'" . SID . ">";
                    Input_Hidden('FormID',"sed");
                    Input_Hidden('CustomerName',esc_quotes($myrow['Name']) );
                    Input_Hidden('CustomerAddress',esc_quotes($myrow['Addr1']));
                    Input_Hidden('CityStateZip',$myrow['City'] .", ". $myrow['State'] ." ". $myrow['Zip'] );
                    Input_Hidden('Country',$_SESSION['Items']->CountryISO);
                    Input_Hidden('Date',Date("Y/m/d"));
                    Input_Text("Tracking",'UPS_Number',$myrow['Consignment'],22);
                    Input_Submit('SED',_('SED'));
                    $jjj = 0;
                    foreach ($LineItems as $LnItm) {
                                    if ( ($lineTotal = ($LnItm['Price'] * $LnItm['QtyDispatched'] * (1- $LnItm['DiscountPercent']))) >=2500 ) {
                                                    Input_Hidden($jjj . '_Harmonization', GetHarmonization($LnItm['StockID'], $db) );
                                                    Input_Hidden($jjj . '_Weight',$LnItm['QtyDispatched'] *$LnItm['Weight']);
                                                    Input_Hidden($jjj . '_Quantity',$LnItm['QtyDispatched']);
                                                    Input_Hidden($jjj . '_Description', $LnItm['ItemDescription']);
                                                    Input_Hidden($jjj . '_StockID',$LnItm['StockID']);
                                                    if ($LnItm->Origin=="USA") {
                                                                    Input_Hidden($jjj . '_DFMCode','D' );
                                                    } else {
                                                                    Input_Hidden($jjj . '_DFMCode','F' );
                                                    }
                                                    Input_Hidden($jjj . '_Value', "$" . number_format($lineTotal,0) );
                                                    $jjj++;
                                    }
                    }
                    Input_Hidden('NumRows',$jjj);
                    echo '</CENTER></FORM>';
                } // End of SED Form Button

            } /* end of check to see that there was an invoice record to print */
            $FromTransNo++;
        } /* end loop to print invoices */
    } /*end of if FromTransNo exists */
    include('includes/footer.inc');

} /*end of else not PrintPDF */



function PrintLinesToBottom () {

    global $pdf;
    global $PageNumber;
    global $TopOfColHeadings;
    global $Left_Margin;
    global $Bottom_Margin;
    global $line_height;


/*draw the vertical column lines right to the bottom */
    $pdf->line($Left_Margin+97, $TopOfColHeadings+12,$Left_Margin+97,$Bottom_Margin);

    /*Print a column vertical line */
    $pdf->line($Left_Margin+300, $TopOfColHeadings+12,$Left_Margin+300,$Bottom_Margin);

    /*Print a column vertical line */
    $pdf->line($Left_Margin+370, $TopOfColHeadings+12,$Left_Margin+370,$Bottom_Margin);

    /*Print a column vertical line */
    $pdf->line($Left_Margin+450, $TopOfColHeadings+12,$Left_Margin+450,$Bottom_Margin);

    /*Print a column vertical line */
    $pdf->line($Left_Margin+550, $TopOfColHeadings+12,$Left_Margin+550,$Bottom_Margin);

    /*Print a column vertical line */
    $pdf->line($Left_Margin+587, $TopOfColHeadings+12,$Left_Margin+587,$Bottom_Margin);

    $pdf->line($Left_Margin+640, $TopOfColHeadings+12,$Left_Margin+640,$Bottom_Margin);

    $pdf->newPage();
    $PageNumber++;

}

?>
